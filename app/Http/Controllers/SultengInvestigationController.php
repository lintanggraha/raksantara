<?php

namespace App\Http\Controllers;

use App\SultengChecklistItem;
use App\SultengChecklistEvidence;
use App\Services\SultengEvidenceService;
use App\Support\EvidenceFileResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SultengInvestigationController extends Controller
{
    protected SultengEvidenceService $evidenceService;

    public function __construct(SultengEvidenceService $evidenceService)
    {
        $this->evidenceService = $evidenceService;
    }

    /**
     * Dashboard monitoring view
     */
    public function dashboard(Request $request)
    {
        $totalItems = SultengChecklistItem::count();
        $receivedCount = SultengChecklistItem::where('status', 'Diterima')->count();
        $pendingCount = SultengChecklistItem::where('status', 'Belum Diterima')->count();
        $inProgressCount = 0; // Completely transitioned to binary status (Diterima / Belum Diterima)

        $progressPercent = $totalItems > 0 ? round(($receivedCount / $totalItems) * 100, 1) : 0;
        $totalEvidences = SultengChecklistEvidence::count();
        $totalEvidenceBytes = SultengChecklistEvidence::sum('file_size');

        // Format total size
        $formattedTotalSize = $this->formatBytes($totalEvidenceBytes);

        // Category breakdown
        $categories = SultengChecklistItem::select('category_code', 'category_name')
            ->distinct()
            ->orderBy('category_code')
            ->get()
            ->map(function ($cat) {
                $items = SultengChecklistItem::where('category_code', $cat->category_code)->get();
                $total = $items->count();
                $diterima = $items->where('status', 'Diterima')->count();
                $belum = $items->where('status', 'Belum Diterima')->count();
                $proses = 0;
                $itemIds = $items->pluck('id');
                $evidencesCount = SultengChecklistEvidence::whereIn('checklist_item_id', $itemIds)->count();
                $pct = $total > 0 ? round(($diterima / $total) * 100) : 0;

                return [
                    'code' => $cat->category_code,
                    'name' => $cat->category_name,
                    'total' => $total,
                    'diterima' => $diterima,
                    'proses' => $proses,
                    'belum' => $belum,
                    'evidences_count' => $evidencesCount,
                    'percent' => $pct,
                ];
            });

        // PIC breakdown
        $picStats = SultengChecklistItem::select('pic_division', DB::raw('count(*) as total'))
            ->groupBy('pic_division')
            ->orderByDesc('total')
            ->get()
            ->map(function ($row) {
                $pic = $row->pic_division ?: 'Belum Ditentukan';
                $items = SultengChecklistItem::where('pic_division', $row->pic_division)->get();
                $diterima = $items->where('status', 'Diterima')->count();
                $belum = $items->where('status', 'Belum Diterima')->count();
                $proses = 0;
                $pct = $items->count() > 0 ? round(($diterima / $items->count()) * 100) : 0;

                return [
                    'pic' => $pic,
                    'total' => $items->count(),
                    'diterima' => $diterima,
                    'proses' => $proses,
                    'belum' => $belum,
                    'percent' => $pct,
                ];
            });

        // Prepare chart payloads for executive graphics (Binary: Diterima Hijau vs Belum Diterima Merah)
        $statusChart = [
            'labels' => ['Diterima (Hijau)', 'Belum Diterima (Merah)'],
            'data' => [$receivedCount, $pendingCount],
            'colors' => ['#2fb344', '#d63939'],
        ];

        $categoryChart = [
            'labels' => $categories->pluck('name')->map(function ($name) {
                return str_replace(['Server ', ' *)', ' (CBS)'], '', $name);
            })->values(),
            'codes' => $categories->pluck('code')->values(),
            'diterima' => $categories->pluck('diterima')->values(),
            'belum' => $categories->pluck('belum')->values(),
            'percent' => $categories->pluck('percent')->values(),
        ];

        $picChart = [
            'labels' => $picStats->pluck('pic')->values(),
            'total' => $picStats->pluck('total')->values(),
            'diterima' => $picStats->pluck('diterima')->values(),
            'belum' => $picStats->pluck('belum')->values(),
            'percent' => $picStats->pluck('percent')->values(),
        ];

        // Critical pending items for management follow-up (9 items)
        $pendingCriticalItems = SultengChecklistItem::where('status', 'Belum Diterima')
            ->orderBy('category_code')
            ->orderBy('item_number')
            ->get();

        // Recent uploaded evidence files
        $recentEvidences = SultengChecklistEvidence::with(['item', 'uploader'])
            ->latest()
            ->take(8)
            ->get();

        // Local evidence folder path
        $localEvidencePath = SultengEvidenceService::getBaseStoragePath();

        // Drilldown collections by status with evidences
        $inProgressItems = collect([]);

        $pendingItems = SultengChecklistItem::with('evidences')
            ->where('status', 'Belum Diterima')
            ->orderBy('code')
            ->get();

        $receivedItems = SultengChecklistItem::with('evidences')
            ->where('status', 'Diterima')
            ->orderBy('code')
            ->get();

        return view('sulteng.dashboard', compact(
            'totalItems',
            'receivedCount',
            'inProgressCount',
            'pendingCount',
            'progressPercent',
            'totalEvidences',
            'formattedTotalSize',
            'categories',
            'picStats',
            'recentEvidences',
            'localEvidencePath',
            'statusChart',
            'categoryChart',
            'picChart',
            'pendingCriticalItems',
            'inProgressItems',
            'pendingItems',
            'receivedItems'
        ));
    }

    /**
     * Sumut Assessment blank page
     */
    public function sumut()
    {
        return view('sulteng.sumut_blank');
    }

    /**
     * Dedicated Checklist & Upload Evidence page
     */
    public function items(Request $request)
    {
        $query = SultengChecklistItem::with(['evidences.uploader'])
            ->orderBy('sort_order', 'asc')
            ->orderBy('code', 'asc');

        // Filtering
        if ($request->filled('category')) {
            $query->where('category_code', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('pic')) {
            $query->where('pic_division', $request->pic);
        }

        if ($request->filled('has_evidence')) {
            if ($request->has_evidence === 'yes') {
                $query->has('evidences');
            } elseif ($request->has_evidence === 'no') {
                $query->doesntHave('evidences');
            }
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('code', 'like', "%{$s}%")
                    ->orWhere('title', 'like', "%{$s}%")
                    ->orWhere('category_name', 'like', "%{$s}%")
                    ->orWhere('pic_division', 'like', "%{$s}%")
                    ->orWhere('period', 'like', "%{$s}%");
            });
        }

        $items = $query->get();

        // Group by category code
        $groupedItems = $items->groupBy('category_code');

        // Filter dropdown options
        $allCategories = SultengChecklistItem::select('category_code', 'category_name')
            ->distinct()
            ->orderBy('category_code')
            ->get();

        $allPics = SultengChecklistItem::select('pic_division')
            ->whereNotNull('pic_division')
            ->where('pic_division', '!=', '')
            ->distinct()
            ->pluck('pic_division');

        $localEvidencePath = SultengEvidenceService::getBaseStoragePath();

        // Overall stats for quick header info
        $totalCount = SultengChecklistItem::count();
        $doneCount = SultengChecklistItem::where('status', 'Diterima')->count();
        $progressPct = $totalCount > 0 ? round(($doneCount / $totalCount) * 100, 1) : 0;

        return view('sulteng.items', compact(
            'groupedItems',
            'allCategories',
            'allPics',
            'localEvidencePath',
            'totalCount',
            'doneCount',
            'progressPct'
        ));
    }

    /**
     * Upload evidence for a point
     */
    public function uploadEvidence(Request $request, SultengChecklistItem $item)
    {
        $request->validate([
            'files' => 'required',
            'files.*' => [
                'file',
                'max:102400', // up to 100MB per file
                'mimes:pdf,doc,docx,xls,xlsx,csv,txt,log,json,xml,png,jpg,jpeg,gif,webp,zip,rar,7z,tar,gz,msg,eml,pcap,pcapng,evtx,dat',
            ],
            'notes' => 'nullable|string|max:1000',
        ], [
            'files.*.mimes' => 'Format file evidence tidak diizinkan. Gunakan dokumen (PDF, Word, Excel), log/teks (.log, .txt, .json), gambar, atau arsip (.zip, .rar).',
        ]);

        $uploadedFiles = $request->file('files');
        if (!is_array($uploadedFiles)) {
            $uploadedFiles = [$uploadedFiles];
        }

        $created = $this->evidenceService->storeEvidences(
            $item,
            $uploadedFiles,
            auth()->id(),
            $request->input('notes')
        );

        $fileNames = array_map(fn($e) => $e->file_name, $created);
        $fileNamesStr = implode(', ', $fileNames);

        return redirect()->back()->with(
            'success',
            "Berhasil upload " . count($created) . " evidence untuk [{$item->code}]: {$fileNamesStr}. Tersimpan di folder lokal."
        );
    }

    /**
     * Update item status or notes
     */
    public function updateItem(Request $request, SultengChecklistItem $item)
    {
        $validated = $request->validate([
            'status' => 'required|in:Belum Diterima,Dalam Proses,Diterima',
            'pic_division' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $item->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Item {$item->code} berhasil diperbarui.",
                'item' => $item,
            ]);
        }

        return redirect()->back()->with('success', "Status [{$item->code}] berhasil diubah menjadi: {$item->status}.");
    }

    /**
     * Validate that evidence file path is within authorized storage roots (anti-path-traversal)
     */
    protected function validateEvidencePath(SultengChecklistEvidence $evidence): string
    {
        if (empty($evidence->file_path)) {
            abort(404, 'File evidence tidak ditemukan.');
        }

        $realPath = realpath($evidence->file_path);
        if (!$realPath || !file_exists($realPath)) {
            abort(404, "File evidence '{$evidence->file_name}' tidak ditemukan di penyimpanan server.");
        }

        $allowedRoots = array_filter([
            realpath(SultengEvidenceService::getBaseStoragePath()),
            realpath(storage_path('app/sulteng_evidence')),
            realpath((string) config('regional_assessment.sulteng.source_root')),
            app()->environment('testing') ? realpath(sys_get_temp_dir()) : null,
        ]);

        $isPathAllowed = false;
        foreach ($allowedRoots as $root) {
            if ($root && (str_starts_with($realPath, $root . DIRECTORY_SEPARATOR) || $realPath === $root)) {
                $isPathAllowed = true;
                break;
            }
        }

        if (!$isPathAllowed) {
            abort(403, 'Akses ke lokasi file evidence ini ditolak demi keamanan.');
        }

        return $realPath;
    }

    /**
     * Download evidence file
     */
    public function downloadEvidence(SultengChecklistEvidence $evidence)
    {
        $realPath = $this->validateEvidencePath($evidence);

        return EvidenceFileResponse::download($realPath, (string) $evidence->file_name);
    }

    /**
     * Preview evidence file in browser (PDF, images, text)
     */
    public function previewEvidence(SultengChecklistEvidence $evidence)
    {
        $realPath = $this->validateEvidencePath($evidence);

        return EvidenceFileResponse::preview($realPath, (string) $evidence->file_name);
    }

    /**
     * Delete evidence
     */
    public function destroyEvidence(SultengChecklistEvidence $evidence)
    {
        $user = auth()->user();
        if (!$user) {
            abort(401);
        }

        // IDOR protection: Superadmins can delete any; regular users can only delete evidence they uploaded
        if (!$user->isSuperadmin() && $evidence->uploaded_by && $evidence->uploaded_by !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus file evidence yang diunggah oleh pengguna lain.');
        }

        $fileName = $evidence->file_name;
        $code = $evidence->item ? $evidence->item->code : '';

        $this->evidenceService->deleteEvidence($evidence);

        return redirect()->back()->with('success', "Evidence '{$fileName}' untuk point [{$code}] berhasil dihapus.");
    }

    /**
     * Export updated checklist to Excel format
     */
    public function exportExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Monitoring Checklist');

        // Title row
        $sheet->setCellValue('A1', 'Checklist Permintaan Data & Log Investigasi Bank Sulteng');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A2', 'Status Pemenuhan dan File Evidence per ' . now()->translatedFormat('d F Y H:i'));
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);

        // Header table
        $headers = ['No', 'Kategori', 'Data / Log', 'Periode', 'PIC Divisi', 'Status', 'Evidence Files'];
        $colIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($colIndex . '4', $header);
            $sheet->getStyle($colIndex . '4')->getFont()->setBold(true);
            $sheet->getStyle($colIndex . '4')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE2E8F0');
            $colIndex++;
        }

        $items = SultengChecklistItem::with('evidences')
            ->orderBy('sort_order', 'asc')
            ->get();

        $rowIdx = 5;
        foreach ($items as $item) {
            $evidenceList = $item->evidences->pluck('file_name')->implode(', ');
            if (empty($evidenceList)) {
                $evidenceList = '-';
            }

            $sheet->setCellValue('A' . $rowIdx, $item->code);
            $sheet->setCellValue('B' . $rowIdx, $item->category_name);
            $sheet->setCellValue('C' . $rowIdx, $item->title);
            $sheet->setCellValue('D' . $rowIdx, $item->period);
            $sheet->setCellValue('E' . $rowIdx, $item->pic_division);
            $sheet->setCellValue('F' . $rowIdx, $item->status);
            $sheet->setCellValue('G' . $rowIdx, $evidenceList);

            // Styling status cell
            $statusColor = match ($item->status) {
                'Diterima' => 'FFDCFCE7',
                'Dalam Proses' => 'FFFEF9C3',
                default => 'FFFEE2E2',
            };
            $sheet->getStyle('F' . $rowIdx)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB($statusColor);

            $rowIdx++;
        }

        // Auto width
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Checklist_Investigasi_Sulteng_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Re-sync from original Excel file in D:\Playground\SULTENG\Checklist_Permintaan_Data_Log.xlsx
     */
    public function syncFromExcel()
    {
        if (!auth()->user() || !auth()->user()->isSuperadmin()) {
            abort(403, 'Hanya superadmin yang berhak melakukan sinkronisasi data dari file Excel.');
        }

        $excelPath = (string) config('regional_assessment.sulteng.sync_excel_path');
        if (!file_exists($excelPath)) {
            return redirect()->back()->with('error', "File {$excelPath} tidak ditemukan di disk lokal.");
        }

        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => 'SultengChecklistSeeder',
            '--force' => true,
        ]);

        return redirect()->back()->with('success', 'Berhasil sinkronisasi ulang data checklist dari file Excel sumber!');
    }

    /**
     * Helper to format bytes
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = (int) floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}

