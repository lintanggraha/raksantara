<?php

namespace App\Http\Controllers;

use App\OjkMitigationItem;
use App\Services\OjkMitigationSpreadsheetImportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OjkMitigationController extends Controller
{
    /** @var array<int, string> */
    private const ALLOWED_STATUSES = [
        'Not Started',
        'On Progress',
        'Done',
        'Blocked',
    ];

    private OjkMitigationSpreadsheetImportService $importService;

    public function __construct(OjkMitigationSpreadsheetImportService $importService)
    {
        $this->importService = $importService;
    }

    public function index()
    {
        $items = OjkMitigationItem::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $timeline = $this->buildTimelinePayload($items);
        $statusOptions = self::ALLOWED_STATUSES;

        return view('ojk.mitigation', compact('items', 'timeline', 'statusOptions'));
    }

    public function importSpreadsheet(Request $request)
    {
        $this->ensureEditable();

        $request->validate([
            'file' => 'required|file|max:20480|mimes:xls,xlsx',
        ]);

        $uploadedFile = $request->file('file');
        $storedPath = $uploadedFile->store('imports/ojk-mitigation', 'local');
        $absolutePath = storage_path('app/' . $storedPath);

        try {
            $result = $this->importService->parse($absolutePath);
            $rows = (array) ($result['rows'] ?? []);

            $previousCount = OjkMitigationItem::query()->count();
            // The import replaces the whole list: one summary entry, not one per row.
            OjkMitigationItem::withoutAuditing(fn () => DB::transaction(function () use ($rows) {
                OjkMitigationItem::query()->delete();

                foreach ($rows as $row) {
                    if (!is_array($row)) {
                        continue;
                    }

                    $parsedTargetDate = $this->parseTargetDate($row['target_date'] ?? null, $row['target'] ?? null);
                    $normalizedStatus = $this->resolveImportedStatus($row['mitigation_status'] ?? null, $parsedTargetDate);

                    OjkMitigationItem::query()->create([
                        'item_number' => (string) ($row['item_number'] ?? ''),
                        'action_plan' => (string) ($row['action_plan'] ?? '-'),
                        'pic' => $this->nullIfEmpty($row['pic'] ?? null),
                        'solution' => $this->nullIfEmpty($row['solution'] ?? null),
                        'target' => $this->nullIfEmpty($row['target'] ?? null),
                        'target_date' => $parsedTargetDate,
                        'mitigation_status' => $normalizedStatus,
                        'sort_order' => (int) ($row['sort_order'] ?? 0),
                    ]);
                }
            }));

            \App\Support\AuditLogger::logModelEvent('ojk_mitigation.imported', new OjkMitigationItem(), null, [
                'file_name' => $uploadedFile->getClientOriginalName(),
                'rows_loaded' => count($rows),
                'rows_replaced' => $previousCount,
            ]);

            return redirect()
                ->route('ojk-mitigation.index')
                ->with('success', sprintf('Import mitigasi OJK berhasil. %d baris dimuat.', count($rows)));
        } catch (\Throwable $e) {
            $incidentId = (string) Str::uuid();
            \Log::error('OJK mitigation import failed', [
                'incident_id' => $incidentId,
                'user_id' => auth()->id(),
                'exception_class' => get_class($e),
                'exception_file' => basename($e->getFile()),
                'exception_line' => $e->getLine(),
            ]);

            return redirect()
                ->route('ojk-mitigation.index')
                ->with('error', 'Import mitigasi OJK gagal. Referensi insiden: ' . $incidentId . '.');
        } finally {
            if (!empty($storedPath) && Storage::disk('local')->exists($storedPath)) {
                Storage::disk('local')->delete($storedPath);
            }
        }
    }

    public function edit(OjkMitigationItem $item)
    {
        $this->ensureEditable();

        $statusOptions = self::ALLOWED_STATUSES;

        return view('ojk.mitigation-edit', compact('item', 'statusOptions'));
    }

    public function update(Request $request, OjkMitigationItem $item)
    {
        $this->ensureEditable();

        $validated = $this->validateItemPayload($request);

        $item->item_number = (string) ($validated['item_number'] ?? '');
        $item->action_plan = (string) $validated['action_plan'];
        $item->pic = $this->nullIfEmpty($validated['pic'] ?? null);
        $item->solution = $this->nullIfEmpty($validated['solution'] ?? null);
        $item->target = $this->nullIfEmpty($validated['target'] ?? null);
        $item->target_date = $this->parseTargetDate(null, $validated['target'] ?? null);
        $item->mitigation_status = $this->normalizeStatus($validated['mitigation_status'] ?? null);

        if (array_key_exists('sort_order', $validated) && !is_null($validated['sort_order'])) {
            $item->sort_order = (int) $validated['sort_order'];
        }

        $item->save();

        return redirect()
            ->route('ojk-mitigation.index')
            ->with('success', 'Point mitigasi berhasil diperbarui.');
    }

    public function destroy(OjkMitigationItem $item)
    {
        $this->ensureEditable();

        $item->delete();

        return redirect()
            ->route('ojk-mitigation.index')
            ->with('success', 'Point mitigasi berhasil dihapus.');
    }

    private function validateItemPayload(Request $request): array
    {
        return $request->validate([
            'item_number' => 'nullable|string|max:40',
            'action_plan' => 'required|string|max:10000',
            'pic' => 'nullable|string|max:255',
            'solution' => 'nullable|string|max:10000',
            'target' => 'nullable|string|max:10000',
            'mitigation_status' => 'required|in:' . implode(',', self::ALLOWED_STATUSES),
            'sort_order' => 'nullable|integer|min:0|max:100000',
        ]);
    }

    private function normalizeStatus(?string $status): string
    {
        $status = trim((string) $status);
        if (in_array($status, self::ALLOWED_STATUSES, true)) {
            return $status;
        }

        return 'On Progress';
    }

    private function resolveImportedStatus(?string $status, ?string $targetDate): string
    {
        $normalized = $this->normalizeStatus($status);

        // Sheet OJK terbaru menandai item tanpa tanggal sebagai pekerjaan yang sudah selesai.
        if (is_null($targetDate) && $normalized === 'On Progress') {
            return 'Done';
        }

        return $normalized;
    }

    /**
     * @param  mixed  $rawDate
     */
    private function parseTargetDate($rawDate, ?string $target): ?string
    {
        if ($rawDate instanceof Carbon) {
            return $rawDate->toDateString();
        }

        if (is_string($rawDate) && trim($rawDate) !== '') {
            try {
                return Carbon::parse($rawDate)->toDateString();
            } catch (\Throwable $e) {
                // fall through to text parser
            }
        }

        if (is_null($target) || trim($target) === '') {
            return null;
        }

        $text = $this->translateIndonesianMonths($target);
        $text = str_replace(["\r", "\n"], ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim((string) $text);

        $dates = [];

        preg_match_all('/\b(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})\b/', $text, $textMatches, PREG_SET_ORDER);
        foreach ($textMatches as $match) {
            try {
                $dates[] = Carbon::createFromFormat('j F Y', $match[1] . ' ' . $match[2] . ' ' . $match[3])->toDateString();
            } catch (\Throwable $e) {
                // continue
            }
        }

        preg_match_all('/\b(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})\b/', $text, $numericMatches, PREG_SET_ORDER);
        foreach ($numericMatches as $match) {
            try {
                $dates[] = Carbon::createFromFormat('j-n-Y', $match[1] . '-' . $match[2] . '-' . $match[3])->toDateString();
            } catch (\Throwable $e) {
                // continue
            }
        }

        if (empty($dates)) {
            return null;
        }

        sort($dates);

        return $dates[count($dates) - 1] ?? null;
    }

    private function translateIndonesianMonths(string $text): string
    {
        $map = [
            'januari' => 'January',
            'februari' => 'February',
            'maret' => 'March',
            'april' => 'April',
            'mei' => 'May',
            'juni' => 'June',
            'juli' => 'July',
            'agustus' => 'August',
            'september' => 'September',
            'oktober' => 'October',
            'november' => 'November',
            'desember' => 'December',
        ];

        $result = $text;
        foreach ($map as $id => $en) {
            $result = preg_replace('/\b' . $id . '\b/i', $en, $result) ?: $result;
        }

        return $result;
    }

    private function nullIfEmpty($value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        return $text;
    }

    private function buildTimelinePayload($items): array
    {
        $statusColors = [
            'Not Started' => '#64748b',
            'On Progress' => '#2563eb',
            'Done' => '#16a34a',
            'Blocked' => '#dc2626',
        ];

        $today = Carbon::today()->startOfDay();
        $todayKey = $today->format('Y-m-d');
        $todayLabel = $today->format('d M Y');

        $dateLabelByKey = [];
        $pointLabels = [];
        $linePointsByStatus = [];
        $latestTargetDate = null;
        $latestTargetLabel = null;
        $nearestActiveTargetDate = null;
        $nearestActiveTargetLabel = null;

        foreach ($items as $item) {
            $pointLabel = $this->pointLabel($item);
            if (is_null($item->target_date)) {
                continue;
            }
            $pointLabels[] = $pointLabel;

            $status = $this->normalizeStatus((string) $item->mitigation_status);
            $targetDate = $item->target_date->copy()->startOfDay();
            $dateKey = $targetDate->format('Y-m-d');
            $targetLabel = $targetDate->format('d M Y');
            $dateLabelByKey[$dateKey] = $targetLabel;

            if (is_null($latestTargetDate) || $targetDate->greaterThan($latestTargetDate)) {
                $latestTargetDate = $targetDate->copy();
                $latestTargetLabel = $targetLabel;
            }

            if ($status !== 'Done' && $targetDate->greaterThanOrEqualTo($today)) {
                if (is_null($nearestActiveTargetDate) || $targetDate->lessThan($nearestActiveTargetDate)) {
                    $nearestActiveTargetDate = $targetDate->copy();
                    $nearestActiveTargetLabel = $targetLabel;
                }
            }

            $startDate = $today->copy();
            $endDate = $targetDate->copy();

            if ($status === 'Done') {
                // Done tetap divisualkan sebagai bar pendek agar terlihat seperti milestone completed.
                $startDate = $targetDate->copy()->subDay();
                $endDate = $targetDate->copy();
            } elseif ($targetDate->lessThan($today)) {
                // Overdue digambar dari target lampau sampai tanggal berjalan.
                $startDate = $targetDate->copy();
                $endDate = $today->copy();
            }

            if ($startDate->equalTo($endDate)) {
                $endDate = $endDate->copy()->addDay();
            }

            $startKey = $startDate->format('Y-m-d');
            $endKey = $endDate->format('Y-m-d');
            $startLabel = $startDate->format('d M Y');
            $endLabel = $endDate->format('d M Y');
            $dateLabelByKey[$startKey] = $startLabel;
            $dateLabelByKey[$endKey] = $endLabel;

            $remainingDays = (int) $today->diffInDays($targetDate, false);
            $pointMeta = [
                'item_number' => (string) $item->item_number,
                'pic' => (string) ($item->pic ?? '-'),
                'target' => (string) ($item->target ?? '-'),
                'target_label' => $targetLabel,
                'start_label' => $startLabel,
                'end_label' => $endLabel,
                'action_plan' => (string) Str::limit($item->action_plan, 120),
                'remaining_days' => $remainingDays,
            ];

            $linePointsByStatus[$status][] = array_merge($pointMeta, [
                'x' => $startLabel,
                'y' => $pointLabel,
                'segment' => 'start',
            ]);
            $linePointsByStatus[$status][] = array_merge($pointMeta, [
                'x' => $endLabel,
                'y' => $pointLabel,
                'segment' => 'end',
            ]);
            $linePointsByStatus[$status][] = null;
        }

        if (!empty($dateLabelByKey)) {
            $dateLabelByKey[$todayKey] = $todayLabel;
        }

        ksort($dateLabelByKey);
        $dateLabels = array_values($dateLabelByKey);
        $pointLabels = array_values(array_unique($pointLabels));

        $remainingDays = null;
        $remainingLabel = 'Remaining: -';
        if ($nearestActiveTargetDate instanceof Carbon) {
            $remainingDays = (int) $today->diffInDays($nearestActiveTargetDate, false);
            if ($remainingDays > 0) {
                $remainingLabel = 'Remaining: ' . $remainingDays . ' hari ke target aktif terdekat (' . $nearestActiveTargetLabel . ')';
            } elseif ($remainingDays === 0) {
                $remainingLabel = 'Remaining: target aktif jatuh hari ini (' . $nearestActiveTargetLabel . ')';
            } else {
                $remainingLabel = 'Remaining: overdue ' . abs($remainingDays) . ' hari dari target aktif terdekat (' . $nearestActiveTargetLabel . ')';
            }
        } elseif ($latestTargetDate instanceof Carbon) {
            $remainingDays = (int) $today->diffInDays($latestTargetDate, false);
            if ($remainingDays > 0) {
                $remainingLabel = 'Remaining: ' . $remainingDays . ' hari ke deadline terakhir (' . $latestTargetLabel . ')';
            } elseif ($remainingDays === 0) {
                $remainingLabel = 'Remaining: deadline terakhir jatuh hari ini (' . $latestTargetLabel . ')';
            } else {
                $remainingLabel = 'Remaining: seluruh deadline sudah lewat ' . abs($remainingDays) . ' hari';
            }
        }

        $datasets = [];
        foreach (self::ALLOWED_STATUSES as $status) {
            $datasets[] = [
                'label' => $status,
                'data' => $linePointsByStatus[$status] ?? [],
                'backgroundColor' => $statusColors[$status],
                'borderColor' => $statusColors[$status],
                'borderWidth' => 3,
                'pointRadius' => 0,
                'pointHoverRadius' => 6,
                'showLine' => true,
                'spanGaps' => false,
                'tension' => 0,
                'fill' => false,
            ];
        }

        return [
            'date_labels' => $dateLabels,
            'point_labels' => $pointLabels,
            'datasets' => $datasets,
            'today_label' => !empty($dateLabels) ? $todayLabel : null,
            'remaining_days' => $remainingDays,
            'remaining_label' => $remainingLabel,
            'total_items' => (int) $items->count(),
            'dated_items' => (int) $items->whereNotNull('target_date')->count(),
            'undated_items' => (int) $items->whereNull('target_date')->count(),
            'status_counts' => [
                'Not Started' => (int) $items->where('mitigation_status', 'Not Started')->count(),
                'On Progress' => (int) $items->where('mitigation_status', 'On Progress')->count(),
                'Done' => (int) $items->where('mitigation_status', 'Done')->count(),
                'Blocked' => (int) $items->where('mitigation_status', 'Blocked')->count(),
            ],
        ];
    }

    private function pointLabel(OjkMitigationItem $item): string
    {
        $number = trim((string) $item->item_number);
        if ($number === '') {
            $number = (string) $item->id;
        }

        return 'Point ' . $number;
    }

    private function ensureEditable(): void
    {
        abort_if(
            optional(auth()->user())->isSuperadmin(),
            403,
            'Superadmin is read-only for mitigation dashboard.'
        );
    }
}
