<?php

namespace App\Http\Controllers;

use App\Exports\MultiSheetReportExport;
use App\Services\ReportDataService;
use App\Support\AuditLogger;
use App\Support\PdfReportLayout;
use App\Support\PdfSectionPaginator;
use App\Support\RiskOwnerAccess;
use App\User;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Shape\Chart\Series;
use PhpOffice\PhpPresentation\Shape\Chart\Type\Bar;
use PhpOffice\PhpPresentation\Shape\Chart\Type\Line;
use PhpOffice\PhpPresentation\Shape\Chart\Type\Pie;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Fill;

class ReportController extends Controller
{
    private const EXPORT_AUDIT_DEDUP_SECONDS = 12;

    /** @var ReportDataService */
    private $reportDataService;

    public function __construct(ReportDataService $reportDataService)
    {
        $this->reportDataService = $reportDataService;
    }

    public function export(Request $request, string $scope, string $format)
    {
        abort_if(
            optional(auth()->user())->isSuperadmin(),
            403,
            'Export is disabled for superadmin.'
        );

        $validated = $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100',
            'risk_category_id' => 'nullable|integer|exists:risk_categories,id',
            'risk_cause_category_id' => 'nullable|integer|exists:risk_cause_categories,id',
            'risk_owner' => 'nullable|string|max:120',
        ]);

        $scope = strtolower($scope);
        $format = strtolower($format);

        if (!in_array($scope, ['monitoring', 'board', 'risks', 'heatmap', 'recap', 'overall'], true)) {
            abort(404, 'Unknown report scope.');
        }

        if (!in_array($format, ['xls', 'xlsx', 'pdf', 'pptx'], true)) {
            abort(404, 'Unknown report format.');
        }

        $year = array_key_exists('year', $validated) ? (int) $validated['year'] : null;
        $actor = auth()->user();
        $riskOwner = $this->normalizeRiskOwnerFilter($validated['risk_owner'] ?? null);
        $allowedRiskOwners = $this->allowedReportRiskOwners($riskOwner);

        if (!is_null($riskOwner) && !in_array($riskOwner, $allowedRiskOwners, true)) {
            throw ValidationException::withMessages([
                'risk_owner' => 'Selected risk owner is not allowed for your account.',
            ]);
        }

        $filters = [
            'risk_category_id' => $validated['risk_category_id'] ?? null,
            'risk_cause_category_id' => $validated['risk_cause_category_id'] ?? null,
            'risk_owner' => $riskOwner,
        ];
        $payload = $this->reportDataService->buildPayload(
            $scope,
            $year,
            $filters,
            $actor instanceof User ? $actor : null
        );

        if ($actor instanceof User && $this->shouldAuditExport($request, $actor, $scope, $format, $year, $filters)) {
            AuditLogger::logModelEvent('report.export.generated', $actor, null, [
                'scope' => $scope,
                'format' => $format,
                'year' => $year,
                'risk_category_id' => $filters['risk_category_id'],
                'risk_cause_category_id' => $filters['risk_cause_category_id'],
                'risk_owner' => $filters['risk_owner'],
            ]);
        }

        try {
            switch ($format) {
                case 'xls':
                case 'xlsx':
                    return $this->downloadExcel($payload, $format);
                case 'pdf':
                    return $this->downloadPdf($payload);
                case 'pptx':
                    return $this->downloadPowerPoint($payload);
                default:
                    abort(404, 'Unknown report format.');
            }
        } catch (\Throwable $e) {
            if ($actor instanceof User) {
                AuditLogger::logModelEvent('report.export.failed', $actor, null, [
                    'scope' => $scope,
                    'format' => $format,
                    'year' => $year,
                    'risk_owner' => $filters['risk_owner'],
                    'error' => Str::limit($e->getMessage(), 200),
                ]);
            }

            throw $e;
        }
    }

    private function normalizeRiskOwnerFilter(?string $riskOwner): ?string
    {
        $riskOwner = trim((string) $riskOwner);
        if ($riskOwner === '') {
            return null;
        }

        return RiskOwnerAccess::canonicalOption($riskOwner) ?: $riskOwner;
    }

    private function allowedReportRiskOwners(?string $includeCurrent = null): array
    {
        $actor = auth()->user();
        if (!$actor instanceof User) {
            return [];
        }

        return $actor->allowedRiskOwners($includeCurrent);
    }

    private function shouldAuditExport(
        Request $request,
        User $actor,
        string $scope,
        string $format,
        ?int $year,
        array $filters
    ): bool {
        if ($request->isMethod('head')) {
            return false;
        }

        $signature = implode('|', [
            (string) $actor->id,
            $scope,
            $format,
            (string) ($year ?? '-'),
            (string) ($filters['risk_category_id'] ?? '-'),
            (string) ($filters['risk_cause_category_id'] ?? '-'),
            (string) ($filters['risk_owner'] ?? '-'),
            (string) $request->ip(),
            (string) $request->userAgent(),
        ]);

        $key = 'audit:report-export:generated:' . sha1($signature);

        return Cache::add($key, 1, now()->addSeconds(self::EXPORT_AUDIT_DEDUP_SECONDS));
    }

    private function downloadExcel(array $payload, string $format = 'xlsx')
    {
        // Normalize to xlsx for modern compatibility; allow legacy 'xls' request
        $format = strtolower($format);
        $extension = $format === 'xls' ? 'xls' : 'xlsx';
        $writerType = $extension === 'xls' ? ExcelWriter::XLS : ExcelWriter::XLSX;

        $sections = $payload['sections'] ?? [];

        // Prepend a metadata sheet for better presentation
        $metaRows = [
            ['Title', (string) ($payload['title'] ?? 'Report')],
            ['Generated At', now()->format('Y-m-d H:i:s')],
            ['Section Count', (string) count($sections)],
        ];

        array_unshift($sections, [
            'title' => 'Report Info',
            'headings' => ['Key', 'Value'],
            'rows' => $metaRows,
        ]);

        $filename = $this->makeFilename($payload['file'] ?? 'report', $extension);

        return Excel::download(
            new MultiSheetReportExport($sections),
            $filename,
            $writerType
        );
    }

    private function downloadPdf(array $payload)
    {
        $sections = PdfSectionPaginator::paginate((array) ($payload['sections'] ?? []));
        $pdfLayout = PdfReportLayout::resolve($sections);

        $html = view('reports.pdf', [
            'title' => $payload['title'] ?? 'Report',
            'sections' => $sections,
            'generatedAt' => now()->format('Y-m-d H:i:s'),
            'pdfLayout' => $pdfLayout,
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        $options->set('dpi', ($pdfLayout['density'] ?? 'normal') === 'ultra' ? 125 : 115);
        $options->set('isFontSubsettingEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper(
            (string) ($pdfLayout['paper'] ?? 'A4'),
            (string) ($pdfLayout['orientation'] ?? 'landscape')
        );
        $dompdf->render();

        $filename = $this->makeFilename($payload['file'] ?? 'report', 'pdf');

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function downloadPowerPoint(array $payload)
    {
        $presentation = new PhpPresentation();
        $presentation->removeSlideByIndex(0);

        $generatedAt = now()->format('Y-m-d H:i:s');

        $this->appendCoverSlide(
            $presentation,
            (string) ($payload['title'] ?? 'Report'),
            count($payload['sections'] ?? []),
            $generatedAt
        );

        foreach ($payload['sections'] ?? [] as $section) {
            $this->appendSectionSlides($presentation, $section);
        }

        // Add footer to each slide with generated timestamp and slide numbers
        $slides = $presentation->getAllSlides();
        $totalSlides = count($slides);
        foreach ($slides as $idx => $slide) {
            $footer = $slide->createRichTextShape()
                ->setHeight(20)
                ->setWidth(960)
                ->setOffsetX(10)
                ->setOffsetY(540);
            $text = 'Generated: ' . $generatedAt . '  |  Slide ' . ($idx + 1) . '/' . $totalSlides;
            $footerRun = $footer->createTextRun($text);
            $footerRun->getFont()
                ->setSize(10)
                ->setItalic(true)
                ->setColor(new Color('FF64748B'));
        }

        $temporaryPath = tempnam(sys_get_temp_dir(), 'rms-report-');
        if ($temporaryPath === false) {
            abort(500, 'Unable to create temporary report file.');
        }

        $writer = IOFactory::createWriter($presentation, 'PowerPoint2007');
        $writer->save($temporaryPath);

        $filename = $this->makeFilename($payload['file'] ?? 'report', 'pptx');

        return response()->download(
            $temporaryPath,
            $filename,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            ]
        )->deleteFileAfterSend(true);
    }

    private function appendCoverSlide(PhpPresentation $presentation, string $title, int $sectionCount, string $generatedAt): void
    {
        $slide = $presentation->createSlide();
        $this->applySlideTheme($slide, 'FF0F172A');

        $heroShape = $slide->createRichTextShape()
            ->setHeight(290)
            ->setWidth(900)
            ->setOffsetX(30)
            ->setOffsetY(86);
        $heroFill = $heroShape->getFill();
        if ($heroFill) {
            $heroFill->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('FFF1F5F9');
        }

        $heroKicker = $heroShape->createTextRun('RISK MANAGEMENT REPORT');
        $heroKicker->getFont()
            ->setSize(15)
            ->setBold(true)
            ->setColor(new Color('FF3B82F6'));

        $heroShape->createBreak();
        $titleRun = $heroShape->createTextRun($title);
        $titleRun->getFont()
            ->setBold(true)
            ->setSize(34)
            ->setColor(new Color('FF0F172A'));

        $heroShape->createBreak();
        $heroShape->createBreak();
        $generatedRun = $heroShape->createTextRun('Generated: ' . $generatedAt);
        $generatedRun->getFont()
            ->setSize(17)
            ->setColor(new Color('FF334155'));

        $heroShape->createBreak();
        $sectionRun = $heroShape->createTextRun('Sections: ' . $sectionCount);
        $sectionRun->getFont()
            ->setSize(17)
            ->setColor(new Color('FF334155'));
    }

    private function appendSectionSlides(PhpPresentation $presentation, array $section): void
    {
        $type = (string) ($section['type'] ?? '');

        if ($type === 'board_status_chart') {
            $this->appendBoardStatusChartSlide($presentation, $section);

            return;
        }

        if ($type === 'board_category_chart') {
            $this->appendBoardCategoryChartSlide($presentation, $section);

            return;
        }

        if ($type === 'board_trend_chart') {
            $this->appendBoardTrendChartSlide($presentation, $section);

            return;
        }

        if ($type === 'board_narrative') {
            $this->appendBoardNarrativeSlides($presentation, $section);

            return;
        }

        $this->appendSectionTableSlides($presentation, $section);
    }

    private function appendSectionTableSlides(PhpPresentation $presentation, array $section): void
    {
        $title = (string) ($section['title'] ?? 'Section');
        $headings = is_array($section['headings'] ?? null) ? $section['headings'] : [];
        $rows = is_array($section['rows'] ?? null) ? $section['rows'] : [];
        $insight = trim((string) ($section['insight'] ?? ''));

        if (empty($rows)) {
            $slide = $presentation->createSlide();
            $this->applySlideTheme($slide);
            $this->buildSectionHeader($slide, $title, 1, 1);

            $bodyShape = $slide->createRichTextShape()
                ->setHeight(440)
                ->setWidth(920)
                ->setOffsetX(20)
                ->setOffsetY(100);
            $panelFill = $bodyShape->getFill();
            if ($panelFill) {
                $panelFill->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FFF8FAFC');
            }

            $emptyRun = $bodyShape->createTextRun('No data available.');
            $emptyRun->getFont()
                ->setSize(22)
                ->setBold(true)
                ->setColor(new Color('FF64748B'));

            if ($insight !== '') {
                $this->appendInsightShape($slide, $insight);
            }

            return;
        }

        $columnCount = max(1, count($headings));
        $chunkSize = $columnCount >= 10 ? 8 : ($columnCount >= 7 ? 10 : 12);
        $chunks = array_chunk($rows, $chunkSize);
        $totalChunks = count($chunks);

        foreach ($chunks as $index => $chunk) {
            $slide = $presentation->createSlide();
            $this->applySlideTheme($slide);
            $this->buildSectionHeader($slide, $title, $index + 1, $totalChunks);

            $table = $slide->createTableShape($columnCount)
                ->setHeight(400)
                ->setWidth(920)
                ->setOffsetX(20)
                ->setOffsetY(105);

            $columnWidth = max(65, (int) floor(920 / $columnCount));
            $headerFontSize = $columnCount >= 9 ? 8 : 9;
            $bodyFontSize = $columnCount >= 9 ? 7 : 8;

            $headerRow = $table->createRow()->setHeight(32);
            for ($col = 0; $col < $columnCount; $col++) {
                $headerCell = $headerRow->nextCell();
                $headerCell->setWidth($columnWidth);
                $headerFill = $headerCell->getFill();
                $headerFill->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FF0F172A');
                $headerAlignment = $headerCell->getActiveParagraph()->getAlignment();
                $headerAlignment->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $headerRun = $headerCell->createTextRun((string) ($headings[$col] ?? ('Column ' . ($col + 1))));
                $headerRun->getFont()
                    ->setBold(true)
                    ->setSize($headerFontSize)
                    ->setColor(new Color(Color::COLOR_WHITE));
            }

            foreach ($chunk as $rowIndex => $row) {
                $rowData = is_array($row) ? array_values($row) : [$row];
                $bodyRow = $table->createRow()->setHeight(30);
                for ($col = 0; $col < $columnCount; $col++) {
                    $cell = $bodyRow->nextCell();
                    $cell->setWidth($columnWidth);
                    $cellFill = $cell->getFill();
                    $cellFill->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB($rowIndex % 2 === 0 ? 'FFFFFFFF' : 'FFF8FAFC');
                    $alignment = $cell->getActiveParagraph()->getAlignment();
                    $alignment->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $rawValue = isset($rowData[$col]) ? $rowData[$col] : '';
                    $text = preg_replace('/\s+/', ' ', trim((string) $rawValue));
                    $run = $cell->createTextRun(Str::limit((string) $text, 100, '...'));
                    $run->getFont()
                        ->setSize($bodyFontSize)
                        ->setColor(new Color('FF1F2937'));
                }
            }

            if ($insight !== '' && $index === ($totalChunks - 1)) {
                $this->appendInsightShape($slide, $insight);
            }
        }
    }

    private function appendBoardStatusChartSlide(PhpPresentation $presentation, array $section): void
    {
        $title = (string) ($section['title'] ?? 'Board Status Distribution');
        $rows = is_array($section['rows'] ?? null) ? $section['rows'] : [];
        $insight = trim((string) ($section['insight'] ?? ''));

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = (string) ($row[0] ?? '-');
            $values[] = (int) ($row[1] ?? 0);
        }

        if (empty($labels)) {
            $fallback = $section;
            $fallback['type'] = '';
            $this->appendSectionTableSlides($presentation, $fallback);

            return;
        }

        $slide = $presentation->createSlide();
        $this->applySlideTheme($slide);
        $this->buildSectionHeader($slide, $title, 1, 1);

        $chart = $slide->createChartShape()
            ->setOffsetX(20)
            ->setOffsetY(108)
            ->setWidth(540)
            ->setHeight(340);

        $pieType = new Pie();
        $seriesValues = [];
        foreach ($labels as $index => $label) {
            $seriesValues[$label] = (string) ($values[$index] ?? 0);
        }

        $series = new Series('Status', $seriesValues);
        $series->setShowValue(true)
            ->setShowSeriesName(false)
            ->setShowCategoryName(true);

        $palette = ['FFDC2626', 'FFF59E0B', 'FF16A34A', 'FF2563EB', 'FF64748B'];
        foreach ($labels as $index => $label) {
            $pointFill = $series->getDataPointFill($index);
            $pointFill->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB($palette[$index % count($palette)]);
        }

        $pieType->addSeries($series);
        $chart->getPlotArea()->setType($pieType);
        $chart->getTitle()->setText('Risk Status Distribution');
        $chart->getLegend()->setPosition(\PhpOffice\PhpPresentation\Shape\Chart\Legend::POSITION_RIGHT);

        $this->appendBoardLegendTable($slide, $labels, $values, 585, 128);

        if ($insight !== '') {
            $this->appendInsightShape($slide, $insight, 585, 280, 355, 132);
        }
    }

    private function appendBoardCategoryChartSlide(PhpPresentation $presentation, array $section): void
    {
        $title = (string) ($section['title'] ?? 'Board Category Exposure');
        $rows = is_array($section['rows'] ?? null) ? $section['rows'] : [];
        $insight = trim((string) ($section['insight'] ?? ''));

        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = (string) ($row[0] ?? '-');
            $values[] = (int) ($row[1] ?? 0);
        }

        if (empty($labels)) {
            $fallback = $section;
            $fallback['type'] = '';
            $this->appendSectionTableSlides($presentation, $fallback);

            return;
        }

        $slide = $presentation->createSlide();
        $this->applySlideTheme($slide);
        $this->buildSectionHeader($slide, $title, 1, 1);

        $chart = $slide->createChartShape()
            ->setOffsetX(20)
            ->setOffsetY(108)
            ->setWidth(640)
            ->setHeight(340);

        $barType = new Bar();
        $seriesValues = [];
        foreach ($labels as $index => $label) {
            $seriesValues[$label] = (string) ($values[$index] ?? 0);
        }
        $series = new Series('Risk Count', $seriesValues);
        $series->setShowValue(true)->setShowCategoryName(false);
        $series->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2563EB');
        $barType->addSeries($series);

        $chart->getPlotArea()->setType($barType);
        $chart->getTitle()->setText('Top Category Exposure');
        $chart->getLegend()->setVisible(false);

        $this->appendBoardLegendTable($slide, $labels, $values, 690, 128, 'Category');

        if ($insight !== '') {
            $this->appendInsightShape($slide, $insight, 690, 280, 250, 132);
        }
    }

    private function appendBoardTrendChartSlide(PhpPresentation $presentation, array $section): void
    {
        $title = (string) ($section['title'] ?? 'Board Trend Chart');
        $chartData = is_array($section['chart'] ?? null) ? $section['chart'] : [];
        $insight = trim((string) ($section['insight'] ?? ''));

        $labels = is_array($chartData['labels'] ?? null) ? $chartData['labels'] : [];
        $inherentValues = is_array($chartData['inherent_values'] ?? null) ? $chartData['inherent_values'] : [];
        $residualValues = is_array($chartData['residual_values'] ?? null) ? $chartData['residual_values'] : [];

        $inherentSeriesValues = [];
        $residualSeriesValues = [];
        foreach ($labels as $index => $label) {
            $key = (string) $label;
            $inherentSeriesValues[$key] = (string) number_format((float) ($inherentValues[$index] ?? 0), 2, '.', '');
            $residualSeriesValues[$key] = (string) number_format((float) ($residualValues[$index] ?? 0), 2, '.', '');
        }

        if (empty($labels)) {
            $fallback = $section;
            $fallback['type'] = '';
            $this->appendSectionTableSlides($presentation, $fallback);

            return;
        }

        $slide = $presentation->createSlide();
        $this->applySlideTheme($slide);
        $this->buildSectionHeader($slide, $title, 1, 1);

        $chart = $slide->createChartShape()
            ->setOffsetX(20)
            ->setOffsetY(108)
            ->setWidth(640)
            ->setHeight(340);

        $lineType = new Line();
        $inherentSeries = new Series('Inherent', $inherentSeriesValues);
        $inherentSeries->setShowValue(true)->setShowCategoryName(false);
        $inherentSeries->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2563EB');

        $residualSeries = new Series('Residual', $residualSeriesValues);
        $residualSeries->setShowValue(true)->setShowCategoryName(false);
        $residualSeries->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF16A34A');

        $lineType->addSeries($inherentSeries);
        $lineType->addSeries($residualSeries);

        $chart->getPlotArea()->setType($lineType);
        $chart->getTitle()->setText('Average Risk Score Trend');
        $chart->getLegend()->setPosition(\PhpOffice\PhpPresentation\Shape\Chart\Legend::POSITION_TOP);

        $tableRows = is_array($section['rows'] ?? null) ? $section['rows'] : [];
        $miniRows = [];
        foreach ($tableRows as $row) {
            $miniRows[] = [
                (string) ($row[0] ?? '-'),
                (string) ($row[1] ?? '0.00'),
                (string) ($row[2] ?? '0.00'),
            ];
        }

        $table = $slide->createTableShape(3)
            ->setOffsetX(690)
            ->setOffsetY(128)
            ->setWidth(250)
            ->setHeight(140);

        $headerRow = $table->createRow()->setHeight(24);
        foreach (['Period', 'Inh', 'Res'] as $heading) {
            $cell = $headerRow->nextCell()->setWidth(80);
            $cell->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0F4C81');
            $cell->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $run = $cell->createTextRun($heading);
            $run->getFont()->setBold(true)->setSize(8)->setColor(new Color(Color::COLOR_WHITE));
        }

        foreach (array_slice($miniRows, 0, 5) as $rowIndex => $miniRow) {
            $tableRow = $table->createRow()->setHeight(22);
            foreach ($miniRow as $value) {
                $cell = $tableRow->nextCell()->setWidth(80);
                $cell->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rowIndex % 2 === 0 ? 'FFFFFFFF' : 'FFF8FBFF');
                $cell->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $cell->createTextRun(Str::limit((string) $value, 24, '...'))->getFont()->setSize(7);
            }
        }

        if ($insight !== '') {
            $this->appendInsightShape($slide, $insight, 690, 280, 250, 132);
        }
    }

    private function appendBoardNarrativeSlides(PhpPresentation $presentation, array $section): void
    {
        $title = (string) ($section['title'] ?? 'Board Narrative');
        $rows = is_array($section['rows'] ?? null) ? $section['rows'] : [];

        if (empty($rows)) {
            $this->appendSectionTableSlides($presentation, $section);

            return;
        }

        $chunkSize = 4;
        $chunks = array_chunk($rows, $chunkSize);
        $totalChunks = count($chunks);

        foreach ($chunks as $index => $chunk) {
            $slide = $presentation->createSlide();
            $this->applySlideTheme($slide);
            $this->buildSectionHeader($slide, $title, $index + 1, $totalChunks);

            $narrativeShape = $slide->createRichTextShape()
                ->setOffsetX(25)
                ->setOffsetY(100)
                ->setWidth(910)
                ->setHeight(410);

            $narrativeShape->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('FFFFFFFF');

            foreach ($chunk as $row) {
                $theme = (string) ($row[0] ?? '-');
                $recommendation = (string) ($row[1] ?? '-');

                $themeRun = $narrativeShape->createTextRun('- ' . $theme);
                $themeRun->getFont()
                    ->setBold(true)
                    ->setSize(14)
                    ->setColor(new Color('FF0F4C81'));
                $narrativeShape->createBreak();

                $recommendationRun = $narrativeShape->createTextRun('  ' . Str::limit($recommendation, 450, '...'));
                $recommendationRun->getFont()
                    ->setSize(11)
                    ->setColor(new Color('FF1F2937'));
                $narrativeShape->createBreak();
                $narrativeShape->createBreak();
            }
        }
    }

    private function appendBoardLegendTable($slide, array $labels, array $values, int $offsetX, int $offsetY, string $labelHeader = 'Status'): void
    {
        $table = $slide->createTableShape(2)
            ->setOffsetX($offsetX)
            ->setOffsetY($offsetY)
            ->setWidth(250)
            ->setHeight(140);

        $headerRow = $table->createRow()->setHeight(24);
        foreach ([$labelHeader, 'Count'] as $header) {
            $cell = $headerRow->nextCell()->setWidth(120);
            $cell->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0F4C81');
            $cell->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $run = $cell->createTextRun($header);
            $run->getFont()->setBold(true)->setSize(8)->setColor(new Color(Color::COLOR_WHITE));
        }

        foreach ($labels as $index => $label) {
            $row = $table->createRow()->setHeight(22);

            $labelCell = $row->nextCell()->setWidth(120);
            $labelCell->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($index % 2 === 0 ? 'FFFFFFFF' : 'FFF8FBFF');
            $labelCell->createTextRun(Str::limit((string) $label, 32, '...'))->getFont()->setSize(7);

            $valueCell = $row->nextCell()->setWidth(120);
            $valueCell->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($index % 2 === 0 ? 'FFFFFFFF' : 'FFF8FBFF');
            $valueCell->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $valueCell->createTextRun((string) ($values[$index] ?? 0))->getFont()->setSize(7)->setBold(true);
        }
    }

    private function appendInsightShape($slide, string $insight, int $offsetX = 20, int $offsetY = 510, int $width = 920, int $height = 45): void
    {
        $insightShape = $slide->createRichTextShape()
            ->setOffsetX($offsetX)
            ->setOffsetY($offsetY)
            ->setWidth($width)
            ->setHeight($height);
        $insightShape->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FFEAF2FF');

        $insightRun = $insightShape->createTextRun('Insight: ' . Str::limit($insight, 500, '...'));
        $insightRun->getFont()
            ->setSize(10)
            ->setColor(new Color('FF1E3A8A'))
            ->setItalic(true);
    }

    private function buildSectionHeader($slide, string $title, int $part, int $total): void
    {
        $header = $title;
        if ($total > 1) {
            $header .= ' (Part ' . $part . '/' . $total . ')';
        }

        $headerBand = $slide->createRichTextShape()
            ->setHeight(54)
            ->setWidth(920)
            ->setOffsetX(20)
            ->setOffsetY(20);
        $headerFill = $headerBand->getFill();
        if ($headerFill) {
            $headerFill->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('FF0F4C81');
        }

        $titleShape = $slide->createRichTextShape()
            ->setHeight(54)
            ->setWidth(900)
            ->setOffsetX(30)
            ->setOffsetY(29);
        $titleRun = $titleShape->createTextRun($header);
        $titleRun->getFont()
            ->setBold(true)
            ->setSize(21)
            ->setColor(new Color(Color::COLOR_WHITE));
    }

    private function applySlideTheme($slide, string $accentColor = 'FF0F172A'): void
    {
        $backgroundShape = $slide->createRichTextShape()
            ->setHeight(720)
            ->setWidth(960)
            ->setOffsetX(0)
            ->setOffsetY(0);
        $backgroundFill = $backgroundShape->getFill();
        if ($backgroundFill) {
            $backgroundFill->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('FFF8FAFC');
        }

        $accentBar = $slide->createRichTextShape()
            ->setHeight(8)
            ->setWidth(960)
            ->setOffsetX(0)
            ->setOffsetY(0);
        $accentFill = $accentBar->getFill();
        if ($accentFill) {
            $accentFill->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB($accentColor);
        }
    }

    private function makeFilename(string $base, string $extension): string
    {
        $safeBase = Str::slug($base, '-');
        if ($safeBase === '') {
            $safeBase = 'report';
        }

        return $safeBase . '-' . now()->format('Ymd-His') . '.' . $extension;
    }
}
