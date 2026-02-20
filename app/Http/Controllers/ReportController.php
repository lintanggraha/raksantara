<?php

namespace App\Http\Controllers;

use App\Exports\MultiSheetReportExport;
use App\Services\ReportDataService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;

class ReportController extends Controller
{
    /** @var ReportDataService */
    private $reportDataService;

    public function __construct(ReportDataService $reportDataService)
    {
        $this->reportDataService = $reportDataService;
    }

    public function export(Request $request, string $scope, string $format)
    {
        $validated = $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100',
        ]);

        $scope = strtolower($scope);
        $format = strtolower($format);

        if (!in_array($scope, ['risks', 'heatmap', 'recap', 'overall'], true)) {
            abort(404, 'Unknown report scope.');
        }

        if (!in_array($format, ['xls', 'pdf', 'pptx'], true)) {
            abort(404, 'Unknown report format.');
        }

        $year = array_key_exists('year', $validated) ? (int) $validated['year'] : null;
        $payload = $this->reportDataService->buildPayload($scope, $year);

        switch ($format) {
            case 'xls':
                return $this->downloadExcel($payload);
            case 'pdf':
                return $this->downloadPdf($payload);
            case 'pptx':
                return $this->downloadPowerPoint($payload);
            default:
                abort(404, 'Unknown report format.');
        }
    }

    private function downloadExcel(array $payload)
    {
        $filename = $this->makeFilename($payload['file'] ?? 'report', 'xls');

        return Excel::download(
            new MultiSheetReportExport($payload['sections'] ?? []),
            $filename,
            ExcelWriter::XLS
        );
    }

    private function downloadPdf(array $payload)
    {
        $html = view('reports.pdf', [
            'title' => $payload['title'] ?? 'Report',
            'sections' => $payload['sections'] ?? [],
            'generatedAt' => now()->format('Y-m-d H:i:s'),
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
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

        $this->appendCoverSlide(
            $presentation,
            (string) ($payload['title'] ?? 'Report'),
            count($payload['sections'] ?? [])
        );

        foreach ($payload['sections'] ?? [] as $section) {
            $this->appendSectionSlides($presentation, $section);
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

    private function appendCoverSlide(PhpPresentation $presentation, string $title, int $sectionCount): void
    {
        $slide = $presentation->createSlide();

        $titleShape = $slide->createRichTextShape()
            ->setHeight(120)
            ->setWidth(900)
            ->setOffsetX(30)
            ->setOffsetY(120);
        $titleShape->createTextRun($title)->getFont()->setBold(true)->setSize(34);

        $subtitleShape = $slide->createRichTextShape()
            ->setHeight(80)
            ->setWidth(900)
            ->setOffsetX(30)
            ->setOffsetY(280);
        $subtitleShape->createTextRun('Generated: ' . now()->format('Y-m-d H:i:s'))->getFont()->setSize(18);
        $subtitleShape->createBreak();
        $subtitleShape->createTextRun('Sections: ' . $sectionCount)->getFont()->setSize(18);
    }

    private function appendSectionSlides(PhpPresentation $presentation, array $section): void
    {
        $title = (string) ($section['title'] ?? 'Section');
        $headings = $section['headings'] ?? [];
        $rows = $section['rows'] ?? [];

        if (empty($rows)) {
            $slide = $presentation->createSlide();
            $this->buildSectionHeader($slide, $title, 1, 1);

            $bodyShape = $slide->createRichTextShape()
                ->setHeight(520)
                ->setWidth(920)
                ->setOffsetX(20)
                ->setOffsetY(90);
            $bodyShape->createTextRun('No data available.')->getFont()->setSize(18);

            return;
        }

        $chunkSize = 14;
        $chunks = array_chunk($rows, $chunkSize);
        $totalChunks = count($chunks);

        foreach ($chunks as $index => $chunk) {
            $slide = $presentation->createSlide();
            $this->buildSectionHeader($slide, $title, $index + 1, $totalChunks);

            $tableShape = $slide->createRichTextShape()
                ->setHeight(520)
                ->setWidth(920)
                ->setOffsetX(20)
                ->setOffsetY(90);

            $tableShape->createTextRun($this->buildLine($headings))->getFont()->setBold(true)->setSize(11);
            $tableShape->createBreak();
            $tableShape->createTextRun(str_repeat('-', 160))->getFont()->setSize(10);

            foreach ($chunk as $row) {
                $tableShape->createBreak();
                $tableShape->createTextRun($this->buildLine($row))->getFont()->setSize(10);
            }
        }
    }

    private function buildSectionHeader($slide, string $title, int $part, int $total): void
    {
        $header = $title;
        if ($total > 1) {
            $header .= ' (Part ' . $part . '/' . $total . ')';
        }

        $titleShape = $slide->createRichTextShape()
            ->setHeight(60)
            ->setWidth(920)
            ->setOffsetX(20)
            ->setOffsetY(20);
        $titleShape->createTextRun($header)->getFont()->setBold(true)->setSize(24);
    }

    private function buildLine(array $cells): string
    {
        $columns = [];

        foreach ($cells as $value) {
            $text = preg_replace('/\s+/', ' ', trim((string) $value));
            $columns[] = Str::limit((string) $text, 38, '...');
        }

        return implode(' | ', $columns);
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
