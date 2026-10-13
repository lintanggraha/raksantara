<?php

namespace App\Http\Controllers;

use App\Services\AuditDashboardDataService;
use App\Services\AuditDashboardSpreadsheetImportService;
use App\Services\ReportDataService;
use App\Support\PdfReportLayout;
use App\Support\PdfSectionPaginator;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Fill;

class AuditDashboardController extends Controller
{
    private AuditDashboardDataService $dashboardDataService;
    private AuditDashboardSpreadsheetImportService $spreadsheetImportService;
    private ReportDataService $reportDataService;

    public function __construct(
        AuditDashboardDataService $dashboardDataService,
        AuditDashboardSpreadsheetImportService $spreadsheetImportService,
        ReportDataService $reportDataService
    )
    {
        $this->dashboardDataService = $dashboardDataService;
        $this->spreadsheetImportService = $spreadsheetImportService;
        $this->reportDataService = $reportDataService;
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100',
            'bank' => 'nullable|string|max:64',
        ]);

        $hasImported = (bool) $request->session()->get('audit_dashboard.has_imported', false);
        $importRows = $hasImported ? $request->session()->get('audit_dashboard.import_rows') : null;
        $useImportedRows = $hasImported && is_array($importRows) && !empty($importRows);

        $auditDashboard = $useImportedRows
            ? $this->dashboardDataService->buildFromImportedRows($importRows, $validated)
            : $this->dashboardDataService->build($validated);

        return view('audit.placeholder', compact('auditDashboard'));
    }

    public function importSpreadsheet(Request $request)
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');
        abort_if($user->isSuperadmin(), 403, 'Superadmin is read-only for risk dashboard.');
        abort_unless($user->isComplianceDivision(), 403, 'Import audit hanya tersedia untuk divisi Compliance.');

        $request->validate([
            'file' => 'required|file|max:20480|mimes:xls,xlsx',
        ]);

        $uploadedFile = $request->file('file');
        $storedPath = $uploadedFile->store('imports/audit-dashboard', 'local');
        $absolutePath = storage_path('app/' . $storedPath);

        try {
            $result = $this->spreadsheetImportService->parse($absolutePath);
            $importRows = (array) ($result['rows'] ?? []);
            $importMeta = is_array($result['meta'] ?? null) ? $result['meta'] : [];

            if (empty($importRows)) {
                return redirect()
                    ->route('audit.placeholder')
                    ->with('error', 'Tidak ada data valid yang bisa diimport dari file tersebut.');
            }

            $request->session()->put('audit_dashboard.import_rows', $importRows);
            $request->session()->put('audit_dashboard.has_imported', true);
            $request->session()->forget('audit_dashboard.import_preview_rows');
            $request->session()->forget('audit_dashboard.import_meta');
            $request->session()->forget('audit_dashboard.import_tab_analysis');

            return redirect()
                ->route('audit.placeholder')
                ->with('success', sprintf(
                    'Import audit berhasil. %d baris dimuat dari sheet %s dan langsung diterapkan ke dashboard.',
                    (int) ($importMeta['row_count'] ?? count($importRows)),
                    (string) ($importMeta['sheet_name'] ?? '-')
                ));
        } catch (\Throwable $e) {
            $incidentId = (string) \Illuminate\Support\Str::uuid();
            \Log::error('Audit dashboard import failed', [
                'incident_id' => $incidentId,
                'user_id' => auth()->id(),
                'exception_class' => get_class($e),
                'exception_file' => basename($e->getFile()),
                'exception_line' => $e->getLine(),
            ]);

            return redirect()
                ->route('audit.placeholder')
                ->with('error', 'Import audit gagal. Referensi insiden: ' . $incidentId . '.');
        } finally {
            if (!empty($storedPath) && Storage::disk('local')->exists($storedPath)) {
                Storage::disk('local')->delete($storedPath);
            }
        }
    }

    /**
     * Export Audit Dashboard sebagai PDF/XLSX/PPTX
     */
    public function export(Request $request, string $format)
    {
        abort_if(
            optional(auth()->user())->isSuperadmin(),
            403,
            'Export is disabled for superadmin.'
        );

        $format = strtolower($format);
        if (!in_array($format, ['xls', 'xlsx', 'pdf', 'pptx'], true)) {
            abort(404, 'Unknown report format.');
        }

        $validated = $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100',
            'bank' => 'nullable|string|max:64',
        ]);

        $hasImported = (bool) $request->session()->get('audit_dashboard.has_imported', false);
        $importRows = $hasImported ? $request->session()->get('audit_dashboard.import_rows') : null;
        $useImportedRows = $hasImported && is_array($importRows) && !empty($importRows);

        $auditDashboard = $useImportedRows
            ? $this->dashboardDataService->buildFromImportedRows($importRows, $validated)
            : $this->dashboardDataService->build($validated);

        $payload = $this->buildAuditReportPayload($auditDashboard, $validated);

        try {
            switch ($format) {
                case 'xls':
                case 'xlsx':
                    return $this->downloadAuditExcel($payload, $format);
                case 'pdf':
                    return $this->downloadAuditPdf($payload);
                case 'pptx':
                    return $this->downloadAuditPowerPoint($payload);
                default:
                    abort(404, 'Unknown report format.');
            }
        } catch (\Throwable $e) {
            $incidentId = (string) \Illuminate\Support\Str::uuid();
            \Log::error('Audit export failed', [
                'incident_id' => $incidentId,
                'user_id' => auth()->id(),
                'format' => $format,
                'exception_class' => get_class($e),
                'exception_file' => basename($e->getFile()),
                'exception_line' => $e->getLine(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Ekspor audit gagal. Referensi insiden: ' . $incidentId . '.');
        }
    }

    private function buildAuditReportPayload(array $auditDashboard, array $filters): array
    {
        $sections = [];

        // Tab 1: Executive Summary
        $sections[] = [
            'title' => 'Executive Summary',
            'type' => 'audit_executive_summary',
            'headings' => ['Metric', 'Value'],
            'rows' => [
                ['Total Findings', (string) ($auditDashboard['summary']['total_findings'] ?? '0')],
                ['Closed Findings', (string) ($auditDashboard['summary']['closed_findings'] ?? '0')],
                ['Open Findings', (string) ($auditDashboard['summary']['open_findings'] ?? '0')],
                ['Overdue Findings', (string) ($auditDashboard['summary']['overdue_findings'] ?? '0')],
                ['Closure Rate', ($auditDashboard['summary']['closure_rate'] ?? '0') . '%'],
            ],
            'insight' => 'Ringkasan status temuan audit secara keseluruhan.',
        ];

        // Tab 2: Rekap Detail Temuan
        if (!empty($auditDashboard['rekap_detail'])) {
            $rows = collect($auditDashboard['rekap_detail'])->map(function ($item) {
                return [
                    (string) ($item['region'] ?? '-'),
                    (string) ($item['bank'] ?? '-'),
                    (string) ($item['year'] ?? '-'),
                    (string) ($item['auditee'] ?? '-'),
                    (string) ($item['total'] ?? '0'),
                    (string) ($item['closed'] ?? '0'),
                    (string) ($item['on_progress'] ?? '0'),
                    (string) ($item['domain_bank'] ?? '0'),
                ];
            })->toArray();

            $sections[] = [
                'title' => 'Rekap Detail Temuan',
                'type' => 'audit_rekap_table',
                'headings' => ['Region', 'Bank', 'Year', 'Auditee', 'Total', 'Closed', 'On Progress', 'Domain Bank'],
                'rows' => $rows,
                'insight' => 'Detail temuan audit berdasarkan region, bank, dan auditee.',
            ];
        }

        // Tab 3: MAPS Temuan (Geographic Distribution)
        if (!empty($auditDashboard['maps']['points'])) {
            $mapRows = collect($auditDashboard['maps']['points'])->map(function ($item) {
                return [
                    (string) ($item['bank'] ?? '-'),
                    (string) ($item['region'] ?? '-'),
                    (string) ($item['latitude'] ?? '0'),
                    (string) ($item['longitude'] ?? '0'),
                    (string) ($item['total_findings'] ?? '0'),
                ];
            })->toArray();

            $sections[] = [
                'title' => 'MAPS Temuan - Geographic Distribution',
                'type' => 'audit_maps_table',
                'headings' => ['Bank', 'Region', 'Latitude', 'Longitude', 'Total Findings'],
                'rows' => $mapRows,
                'insight' => 'Distribusi geografis temuan audit berdasarkan lokasi bank.',
            ];
        }

        // Tab 4: Progress Temuan (by Bank, Region, Product)
        if (!empty($auditDashboard['progress']['by_bank']['rows'])) {
            $progressByBank = collect($auditDashboard['progress']['by_bank']['rows'])->map(function ($item) {
                return [
                    (string) ($item['group'] ?? '-'),
                    (string) ($item['total'] ?? '0'),
                    (string) ($item['counts']['Closed'] ?? '0'),
                    (string) ($item['counts']['On Progress'] ?? '0'),
                    (string) ($item['counts']['Domain Bank'] ?? '0'),
                    (string) ($item['percentages']['Closed'] ?? '0') . '%',
                ];
            })->toArray();

            $sections[] = [
                'title' => 'Progress Temuan - by Bank',
                'type' => 'audit_progress_bank',
                'headings' => ['Bank', 'Total', 'Closed', 'On Progress', 'Domain Bank', 'Closure %'],
                'rows' => $progressByBank,
                'insight' => 'Progress penutupan temuan audit per bank.',
            ];
        }

        if (!empty($auditDashboard['progress']['by_region']['rows'])) {
            $progressByRegion = collect($auditDashboard['progress']['by_region']['rows'])->map(function ($item) {
                return [
                    (string) ($item['group'] ?? '-'),
                    (string) ($item['total'] ?? '0'),
                    (string) ($item['counts']['Closed'] ?? '0'),
                    (string) ($item['counts']['On Progress'] ?? '0'),
                    (string) ($item['counts']['Domain Bank'] ?? '0'),
                ];
            })->toArray();

            $sections[] = [
                'title' => 'Progress Temuan - by Region',
                'type' => 'audit_progress_region',
                'headings' => ['Region', 'Total', 'Closed', 'On Progress', 'Domain Bank'],
                'rows' => $progressByRegion,
                'insight' => 'Progress penutupan temuan audit per region.',
            ];
        }

        // Tab 5: Trend Temuan
        if (!empty($auditDashboard['trend']['labels'])) {
            $trendRows = [];
            $labels = $auditDashboard['trend']['labels'] ?? [];
            $createdValues = $auditDashboard['trend']['created_values'] ?? [];
            $closedValues = $auditDashboard['trend']['closed_values'] ?? [];
            
            for ($i = 0; $i < count($labels); $i++) {
                $trendRows[] = [
                    (string) ($labels[$i] ?? '-'),
                    (string) ($createdValues[$i] ?? '0'),
                    (string) ($closedValues[$i] ?? '0'),
                ];
            }

            $sections[] = [
                'title' => 'Trend Temuan - Creation vs Closure',
                'type' => 'audit_trend_table',
                'headings' => ['Period', 'Created', 'Closed'],
                'rows' => $trendRows,
                'insight' => 'Tren penciptaan dan penutupan temuan audit dari waktu ke waktu.',
            ];
        }

        // Tab 6: Detail Outstanding
        if (!empty($auditDashboard['detail_outstanding']['rows'])) {
            $outstandingRows = collect($auditDashboard['detail_outstanding']['rows'])->map(function ($item) {
                return [
                    (string) ($item['bank'] ?? '-'),
                    (string) ($item['finding'] ?? '-'),
                    (string) ($item['recommendation'] ?? '-'),
                    (string) ($item['due_date'] ?? '-'),
                    (string) ($item['days_remaining'] ?? '0'),
                ];
            })->toArray();

            $sections[] = [
                'title' => 'Detail Outstanding Findings',
                'type' => 'audit_outstanding_table',
                'headings' => ['Bank', 'Finding', 'Recommendation', 'Due Date', 'Days Remaining'],
                'rows' => $outstandingRows,
                'insight' => 'Daftar temuan yang masih outstanding dan belum ditutup.',
            ];
        }

        // Tab 7: Duedate Maturity
        if (!empty($auditDashboard['duedate_maturity']['by_bank'])) {
            $maturityByBank = collect($auditDashboard['duedate_maturity']['by_bank'])->map(function ($item) {
                return [
                    (string) ($item['group'] ?? '-'),
                    (string) ($item['total'] ?? '0'),
                    (string) ($item['counts']['On Time'] ?? '0'),
                    (string) ($item['counts']['Overdue'] ?? '0'),
                    (string) ($item['percentages']['On Time'] ?? '0') . '%',
                    (string) ($item['percentages']['Overdue'] ?? '0') . '%',
                ];
            })->toArray();

            $sections[] = [
                'title' => 'Duedate Maturity - by Bank',
                'type' => 'audit_maturity_table',
                'headings' => ['Bank', 'Total', 'On Time', 'Overdue', 'On Time %', 'Overdue %'],
                'rows' => $maturityByBank,
                'insight' => 'Analisis kepatuhan terhadap deadline penutupan temuan.',
            ];
        }

        $yearFilter = $filters['year'] ?? 'All Years';
        $bankFilter = $filters['bank'] ?? 'All Banks';

        return [
            'title' => 'Audit Monitoring Dashboard Report',
            'file' => 'audit-monitoring-report-' . (is_numeric($yearFilter) ? $yearFilter : 'all-years'),
            'sections' => $sections,
            'filters' => [
                'year' => $yearFilter,
                'bank' => $bankFilter,
            ],
        ];
    }

    private function downloadAuditExcel(array $payload, string $format = 'xlsx')
    {
        $format = strtolower($format);
        $extension = $format === 'xls' ? 'xls' : 'xlsx';
        $writerType = $extension === 'xls' ? \Maatwebsite\Excel\Excel::XLS : \Maatwebsite\Excel\Excel::XLSX;

        $sections = $payload['sections'] ?? [];
        array_unshift($sections, [
            'title' => 'Report Info',
            'headings' => ['Key', 'Value'],
            'rows' => [
                ['Title', (string) ($payload['title'] ?? 'Audit Report')],
                ['Generated At', now()->format('Y-m-d H:i:s')],
                ['Year Filter', (string) ($payload['filters']['year'] ?? 'All')],
                ['Bank Filter', (string) ($payload['filters']['bank'] ?? 'All')],
            ],
        ]);

        $filename = $this->makeFilename($payload['file'] ?? 'audit-report', $extension);

        return \Excel::download(
            new \App\Exports\MultiSheetReportExport($sections),
            $filename,
            $writerType
        );
    }

    private function downloadAuditPdf(array $payload)
    {
        try {
            $sections = PdfSectionPaginator::paginate((array) ($payload['sections'] ?? []));
            $pdfLayout = PdfReportLayout::resolve($sections);

            $html = view('reports.pdf', [
                'title' => $payload['title'] ?? 'Audit Monitoring Report',
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

            $output = $dompdf->output();
            
            if (empty($output)) {
                throw new \RuntimeException('PDF generation failed: empty output');
            }

            $filename = $this->makeFilename($payload['file'] ?? 'audit-report', 'pdf');

            return response($output, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Audit PDF export failed', [
                'exception_class' => get_class($e),
                'exception_file' => basename($e->getFile()),
                'exception_line' => $e->getLine(),
            ]);
            
            throw new \RuntimeException('Failed to generate PDF.', 0, $e);
        }
    }

    private function downloadAuditPowerPoint(array $payload)
    {
        try {
            $presentation = new PhpPresentation();
            $presentation->removeSlideByIndex(0);

            $generatedAt = now()->format('Y-m-d H:i:s');

            // Cover slide
            $slide = $presentation->createSlide();
            $background = $slide->createRichTextShape()
                ->setHeight(720)
                ->setWidth(960)
                ->setOffsetX(0)
                ->setOffsetY(0);
            $background->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('FFF5F8FC');

            $header = $slide->createRichTextShape()
                ->setHeight(12)
                ->setWidth(960)
                ->setOffsetX(0)
                ->setOffsetY(0);
            $header->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('FF0F4C81');

            $titleShape = $slide->createRichTextShape()
                ->setHeight(280)
                ->setWidth(900)
                ->setOffsetX(30)
                ->setOffsetY(130);
            $titleRun = $titleShape->createTextRun($payload['title'] ?? 'Audit Report');
            $titleRun->getFont()
                ->setBold(true)
                ->setSize(34)
                ->setColor(new Color('FF0F172A'));
            $titleShape->createBreak();
            $titleShape->createBreak();
            $metaRun = $titleShape->createTextRun('Generated: ' . $generatedAt);
            $metaRun->getFont()
                ->setSize(17)
                ->setColor(new Color('FF334155'));

            // Content slides
            foreach ($payload['sections'] ?? [] as $section) {
                $this->appendAuditSectionSlide($presentation, $section);
            }

            $temporaryPath = tempnam(sys_get_temp_dir(), 'rms-audit-pptx-');
            if ($temporaryPath === false) {
                throw new \RuntimeException('Unable to create temporary report file.');
            }

            $writer = IOFactory::createWriter($presentation, 'PowerPoint2007');
            $writer->save($temporaryPath);

            $filename = $this->makeFilename($payload['file'] ?? 'audit-report', 'pptx');

            return response()->download($temporaryPath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            ])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            \Log::error('Audit PPTX export failed', [
                'exception_class' => get_class($e),
                'exception_file' => basename($e->getFile()),
                'exception_line' => $e->getLine(),
            ]);
            
            throw new \RuntimeException('Failed to generate PPTX.', 0, $e);
        }
    }

    private function appendAuditSectionSlide($presentation, array $section): void
    {
        $title = (string) ($section['title'] ?? 'Section');
        $headings = is_array($section['headings'] ?? null) ? array_values($section['headings']) : [];
        $rows = is_array($section['rows'] ?? null) ? $section['rows'] : [];

        if (empty($rows)) {
            $slide = $presentation->createSlide();
            $header = $slide->createRichTextShape()
                ->setHeight(54)
                ->setWidth(920)
                ->setOffsetX(20)
                ->setOffsetY(24);
            $header->getFill()
                ->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('FF0F4C81');

            $headerText = $slide->createRichTextShape()
                ->setHeight(54)
                ->setWidth(900)
                ->setOffsetX(30)
                ->setOffsetY(33);
            $headerRun = $headerText->createTextRun($title);
            $headerRun->getFont()
                ->setBold(true)
                ->setSize(21)
                ->setColor(new \PhpOffice\PhpPresentation\Style\Color(\PhpOffice\PhpPresentation\Style\Color::COLOR_WHITE));

            $emptyShape = $slide->createRichTextShape()
                ->setHeight(420)
                ->setWidth(900)
                ->setOffsetX(30)
                ->setOffsetY(140);
            $emptyRun = $emptyShape->createTextRun('No data available for this section.');
            $emptyRun->getFont()
                ->setSize(21)
                ->setBold(true)
                ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF64748B'));

            return;
        }

        $columnCount = max(1, count($headings));
        $chunkSize = $columnCount >= 8 ? 6 : 10;
        $chunks = array_chunk($rows, $chunkSize);
        $totalChunks = count($chunks);

        foreach ($chunks as $index => $chunk) {
            $slide = $presentation->createSlide();

            // Header
            $header = $slide->createRichTextShape()
                ->setHeight(54)
                ->setWidth(920)
                ->setOffsetX(20)
                ->setOffsetY(24);
            $header->getFill()
                ->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('FF0F4C81');

            $headerText = $slide->createRichTextShape()
                ->setHeight(54)
                ->setWidth(900)
                ->setOffsetX(30)
                ->setOffsetY(33);
            $partText = $totalChunks > 1 ? ' (Part ' . ($index + 1) . '/' . $totalChunks . ')' : '';
            $headerRun = $headerText->createTextRun($title . $partText);
            $headerRun->getFont()
                ->setBold(true)
                ->setSize(21)
                ->setColor(new \PhpOffice\PhpPresentation\Style\Color(\PhpOffice\PhpPresentation\Style\Color::COLOR_WHITE));

            // Table
            $table = $slide->createTableShape($columnCount)
                ->setHeight(400)
                ->setWidth(920)
                ->setOffsetX(20)
                ->setOffsetY(105);

            $columnWidth = max(80, (int) floor(920 / $columnCount));
            $headerFontSize = $columnCount >= 6 ? 9 : 10;
            $bodyFontSize = $columnCount >= 6 ? 8 : 9;

            $headerRow = $table->createRow()->setHeight(28);
            for ($col = 0; $col < $columnCount; $col++) {
                $headerCell = $headerRow->nextCell();
                $headerCell->setWidth($columnWidth);
                $headerCell->getFill()
                    ->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FF0F4C81');
                $headerCell->getActiveParagraph()->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpPresentation\Style\Alignment::HORIZONTAL_CENTER);
                $headerRun = $headerCell->createTextRun((string) ($headings[$col] ?? ('Column ' . ($col + 1))));
                $headerRun->getFont()
                    ->setBold(true)
                    ->setSize($headerFontSize)
                    ->setColor(new \PhpOffice\PhpPresentation\Style\Color(\PhpOffice\PhpPresentation\Style\Color::COLOR_WHITE));
            }

            foreach ($chunk as $rowIndex => $row) {
                $rowData = is_array($row) ? array_values($row) : [$row];
                $bodyRow = $table->createRow()->setHeight(24);
                for ($col = 0; $col < $columnCount; $col++) {
                    $cell = $bodyRow->nextCell();
                    $cell->setWidth($columnWidth);
                    $cell->getFill()
                        ->setFillType(\PhpOffice\PhpPresentation\Style\Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB($rowIndex % 2 === 0 ? 'FFFFFFFF' : 'FFF8FBFF');
                    $cell->getActiveParagraph()->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpPresentation\Style\Alignment::HORIZONTAL_CENTER);
                    $rawValue = isset($rowData[$col]) ? $rowData[$col] : '';
                    $text = preg_replace('/\s+/', ' ', trim((string) $rawValue));
                    $run = $cell->createTextRun(\Illuminate\Support\Str::limit((string) $text, 80, '...'));
                    $run->getFont()
                        ->setSize($bodyFontSize)
                        ->setColor(new \PhpOffice\PhpPresentation\Style\Color('FF1F2937'));
                }
            }
        }
    }

    private function makeFilename(string $base, string $extension): string
    {
        $safeBase = \Illuminate\Support\Str::slug($base, '-');
        if ($safeBase === '') {
            $safeBase = 'report';
        }

        return $safeBase . '-' . now()->format('Ymd-His') . '.' . $extension;
    }
}
