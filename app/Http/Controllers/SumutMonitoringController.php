<?php

namespace App\Http\Controllers;

use App\SumutChecklistItem;
use App\SumutChecklistEvidence;
use App\Services\SumutEvidenceService;
use App\Support\EvidenceFileResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SumutMonitoringController extends Controller
{
    protected SumutEvidenceService $evidenceService;

    public function __construct(SumutEvidenceService $evidenceService)
    {
        $this->evidenceService = $evidenceService;
    }

    /**
     * Executive Dashboard for Sumut 27 Point Monitoring
     */
    public function dashboard(Request $request)
    {
        $metrics = $this->evidenceService->getDashboardMetrics();

        // Get recent/all items for the quick status list
        $items = SumutChecklistItem::with('evidences')
            ->orderBy('point_number', 'asc')
            ->get();

        // 6 Points requiring follow-up (< 100%)
        $incompleteItems = $items->where('progress_percent', '<', 100)->values();

        // Status Chart Data (Binary: Ada vs Tidak Ada)
        $statusChart = [
            'labels' => ['Ada (Sesuai & Ada Bukti)', 'Tidak Ada (Kosong/Belum Ada)'],
            'data' => [$metrics['ada_count'], $metrics['tidak_ada_count']],
            'colors' => ['#2fb344', '#d63939'],
        ];

        // Categories Chart Data
        $categoryChart = [
            'labels' => $metrics['categories']->pluck('name')->values(),
            'total' => $metrics['categories']->pluck('total')->values(),
            'ada' => $metrics['categories']->pluck('ada')->values(),
            'avg_progress' => $metrics['categories']->pluck('avg_progress')->values(),
            'evidences_count' => $metrics['categories']->pluck('evidences_count')->values(),
        ];

        // PIC Distribution Chart Data (sorted descending)
        $picChart = [
            'labels' => $metrics['pic_stats']->pluck('pic')->values(),
            'total' => $metrics['pic_stats']->pluck('total')->values(),
            'share_percent' => $metrics['pic_stats']->pluck('share_percent')->values(),
        ];

        return view('sumut.dashboard', compact(
            'metrics',
            'items',
            'incompleteItems',
            'statusChart',
            'categoryChart',
            'picChart'
        ));
    }

    /**
     * Checklist & Evidence Explorer Table (27 Points)
     */
    public function items(Request $request)
    {
        $query = SumutChecklistItem::with(['evidences' => function ($q) {
            $q->orderBy('file_name', 'asc');
        }]);

        // Filter: Category
        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        // Filter: Status ('Ada' or 'Tidak Ada')
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        // Filter: Progress Status ('on_progress' or 'done')
        if ($request->filled('progress_status')) {
            if ($request->query('progress_status') === 'on_progress') {
                $query->where('progress_percent', '<', 100);
            } elseif ($request->query('progress_status') === 'done') {
                $query->where('progress_percent', '>=', 100);
            }
        }

        // Filter: PIC
        if ($request->filled('pic')) {
            $query->where('pic', $request->query('pic'));
        }

        // Filter: Search
        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                if (is_numeric($search)) {
                    $q->where('point_number', (int)$search);
                }
                $q->orWhere('title', 'like', "%{$search}%")
                  ->orWhere('clean_title', 'like', "%{$search}%")
                  ->orWhere('pic', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('recommendation', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhereHas('evidences', function ($sub) use ($search) {
                      $sub->where('file_name', 'like', "%{$search}%");
                  });
            });
        }

        $items = $query->orderBy('point_number', 'asc')->paginate(15)->withQueryString();

        // Get filter dropdown options
        $categories = SumutChecklistItem::select('category')->distinct()->pluck('category');
        $pics = SumutChecklistItem::select('pic')->distinct()->whereNotNull('pic')->pluck('pic');
        $totalItems = SumutChecklistItem::count();
        $adaCount = SumutChecklistItem::where('status', 'Ada')->count();
        $tidakAdaCount = SumutChecklistItem::where('status', 'Tidak Ada')->count();

        return view('sumut.items', compact(
            'items',
            'categories',
            'pics',
            'totalItems',
            'adaCount',
            'tidakAdaCount'
        ));
    }

    /**
     * Update checklist item (notes, recommendation, status)
     */
    public function updateItem(Request $request, SumutChecklistItem $item)
    {
        $validated = $request->validate([
            'status' => 'required|in:Ada,Tidak Ada',
            'notes' => 'nullable|string|max:5000',
            'recommendation' => 'nullable|string|max:5000',
            'follow_up' => 'nullable|string|max:5000',
        ]);

        $item->update($validated);

        return redirect()->back()->with('success', "Point #{$item->folder_number} berhasil diperbarui.");
    }

    /**
     * Download physical evidence file
     */
    public function downloadEvidence(SumutChecklistEvidence $evidence)
    {
        $filePath = $evidence->file_path;

        if (!file_exists($filePath) || !is_file($filePath)) {
            abort(404, 'File evidence tidak ditemukan pada disk lokal.');
        }

        if (!SumutEvidenceService::isPathSafe($filePath)) {
            abort(403, 'Akses ke path file ini dibatasi oleh kebijakan keamanan.');
        }

        return EvidenceFileResponse::download($filePath, (string) $evidence->file_name);
    }

    /**
     * Preview evidence file in browser (inline)
     */
    public function previewEvidence(SumutChecklistEvidence $evidence)
    {
        $filePath = $evidence->file_path;

        if (!file_exists($filePath) || !is_file($filePath)) {
            abort(404, 'File evidence tidak ditemukan pada disk lokal.');
        }

        if (!SumutEvidenceService::isPathSafe($filePath)) {
            abort(403, 'Akses ke path file ini dibatasi oleh kebijakan keamanan.');
        }

        return EvidenceFileResponse::preview($filePath, (string) $evidence->file_name);
    }

    /**
     * Export 27 Points Checklist & Evidence to Excel
     */
    public function exportExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sumut 27 Point');

        // Document Title
        $sheet->setCellValue('A1', 'MONITORING PENCEGAHAN & PENGUATAN (SUMUT 27 POINT)');
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E293B'));

        $sheet->setCellValue('A2', 'Status Pemenuhan, Arsip Bukti Fisik & Rekomendasi Tambahan per ' . now()->translatedFormat('d F Y H:i'));
        $sheet->mergeCells('A2:J2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF64748B'));

        // Table Headers
        $headers = [
            'No',
            'Kategori / Cluster',
            'Point Kegiatan Penguatan',
            'Status Bukti',
            'Jml Bukti',
            'Daftar File Bukti Fisik',
            'Progress Bank Sumut',
            'Target Selesai',
            'PIC',
            'Rekomendasi Bukti Lain (Analisis Ahli)',
        ];

        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '4', $h);
            $sheet->getStyle($col . '4')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
            $sheet->getStyle($col . '4')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF1E3A8A'); // Navy Blue Header
            $sheet->getStyle($col . '4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($col . '4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $col++;
        }
        $sheet->getRowDimension(4)->setRowHeight(28);

        $items = SumutChecklistItem::with('evidences')
            ->orderBy('point_number', 'asc')
            ->get();

        $rowIdx = 5;
        foreach ($items as $item) {
            $evidenceList = $item->evidences->pluck('file_name')->implode("\n");
            if (empty($evidenceList)) {
                $evidenceList = '-';
            }

            $sheet->setCellValue('A' . $rowIdx, sprintf("%02d", $item->point_number));
            $sheet->setCellValue('B' . $rowIdx, $item->category);
            $sheet->setCellValue('C' . $rowIdx, $item->title);
            $sheet->setCellValue('D' . $rowIdx, $item->status);
            $sheet->setCellValue('E' . $rowIdx, $item->evidences->count());
            $sheet->setCellValue('F' . $rowIdx, $evidenceList);
            $sheet->setCellValue('G' . $rowIdx, $item->progress_bs ?: $item->progress);
            $sheet->setCellValue('H' . $rowIdx, $item->target_date ?: '-');
            $sheet->setCellValue('I' . $rowIdx, $item->pic ?: '-');
            $sheet->setCellValue('J' . $rowIdx, $item->recommendation ?: '-');

            // Alignment & wrapping
            $sheet->getStyle('A' . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $rowIdx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $rowIdx)->getAlignment()->setWrapText(true);
            $sheet->getStyle('C' . $rowIdx)->getAlignment()->setWrapText(true);
            $sheet->getStyle('J' . $rowIdx)->getAlignment()->setWrapText(true);

            // Status badge color
            $statusColor = ($item->status === 'Ada') ? 'FFDCFCE7' : 'FFFEE2E2';
            $sheet->getStyle('D' . $rowIdx)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB($statusColor);
            $sheet->getStyle('D' . $rowIdx)->getFont()->setBold(true);

            $rowIdx++;
        }

        // Border styling
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFE2E8F0'],
                ],
            ],
        ];
        $sheet->getStyle('A4:J' . ($rowIdx - 1))->applyFromArray($borderStyle);

        // Auto widths with limits
        foreach (range('A', 'J') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }
        $sheet->getColumnDimension('C')->setAutoSize(false)->setWidth(45);
        $sheet->getColumnDimension('F')->setAutoSize(false)->setWidth(45);
        $sheet->getColumnDimension('J')->setAutoSize(false)->setWidth(50);

        $fileName = 'Monitoring_Sumut_27_Point_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Re-sync checklist and scan folders
     */
    public function syncFromExcel()
    {
        try {
            Artisan::call('db:seed', [
                '--class' => 'SumutChecklistSeeder',
                '--force' => true,
            ]);

            return redirect()->back()->with('success', 'Data Sumut 27 Point dan arsip evidence fisik berhasil disinkronisasi ulang.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal sinkronisasi data: ' . $e->getMessage());
        }
    }
}
