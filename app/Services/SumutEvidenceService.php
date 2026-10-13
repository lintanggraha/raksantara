<?php

namespace App\Services;

use App\SumutChecklistItem;
use App\SumutChecklistEvidence;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SumutEvidenceService
{
    /**
     * Get root local evidence storage directory
     */
    public static function getBaseStoragePath(): string
    {
        $primary = (string) config('regional_assessment.sumut.evidence_path');
        if ($primary !== '' && is_dir($primary)) {
            return $primary;
        }

        // Fallback to local storage/app/sumut_evidence if D: not available
        $fallback = storage_path('app/sumut_evidence');
        if (!file_exists($fallback)) {
            @mkdir($fallback, (int) config('regional_assessment.directory_mode', 0750), true);
        }
        return $fallback;
    }

    /**
     * Get directory path for specific checklist item
     */
    public static function getItemDirectory(SumutChecklistItem $item): string
    {
        $base = self::getBaseStoragePath();
        $folderName = sprintf("%02d", $item->point_number);
        $targetDir = $base . DIRECTORY_SEPARATOR . $folderName;

        if (!file_exists($targetDir)) {
            @mkdir($targetDir, (int) config('regional_assessment.directory_mode', 0750), true);
        }

        return $targetDir;
    }

    /**
     * Check if path is safely inside base directory (prevent path traversal)
     */
    public static function isPathSafe(string $filePath): bool
    {
        $realBase = realpath(self::getBaseStoragePath());
        $realFile = realpath($filePath);

        if (!$realFile) {
            // If file doesn't exist yet, check its directory
            $dir = realpath(dirname($filePath));
            if (!$dir || !$realBase) {
                return false;
            }
            return self::isWithin($dir, $realBase);
        }

        if (!$realBase) {
            return false;
        }

        return self::isWithin($realFile, $realBase);
    }

    /**
     * Compare against the base plus a trailing separator so a sibling folder
     * sharing the same prefix (e.g. "Status_OJK_02102026_x") is rejected.
     */
    private static function isWithin(string $path, string $base): bool
    {
        $path = strtolower($path);
        $base = strtolower(rtrim($base, '\\/'));

        return $path === $base || str_starts_with($path, $base . DIRECTORY_SEPARATOR);
    }

    /**
     * Get summary metrics for Executive Dashboard
     */
    public function getDashboardMetrics(): array
    {
        $totalItems = SumutChecklistItem::count();
        $adaCount = SumutChecklistItem::where('status', 'Ada')->count();
        $tidakAdaCount = SumutChecklistItem::where('status', 'Tidak Ada')->count();

        $totalEvidences = SumutChecklistEvidence::count();
        $totalEvidenceBytes = SumutChecklistEvidence::sum('file_size');

        $avgProgress = $totalItems > 0
            ? round(SumutChecklistItem::avg('progress_percent') ?? 0, 1)
            : 0;

        // Categories breakdown
        $categories = SumutChecklistItem::select('category')
            ->distinct()
            ->get()
            ->map(function ($cat) {
                $items = SumutChecklistItem::where('category', $cat->category)->get();
                $total = $items->count();
                $ada = $items->where('status', 'Ada')->count();
                $tidakAda = $items->where('status', 'Tidak Ada')->count();
                $itemIds = $items->pluck('id');
                $evCount = SumutChecklistEvidence::whereIn('checklist_item_id', $itemIds)->count();
                $pct = $total > 0 ? round(($ada / $total) * 100) : 0;
                $avgProg = round($items->avg('progress_percent') ?? 0);

                return [
                    'name' => $cat->category,
                    'total' => $total,
                    'ada' => $ada,
                    'tidak_ada' => $tidakAda,
                    'evidences_count' => $evCount,
                    'percent' => $pct,
                    'avg_progress' => $avgProg,
                ];
            });

        // PIC breakdown sorted by total descending
        $picStats = SumutChecklistItem::select('pic')
            ->groupBy('pic')
            ->get()
            ->map(function ($row) use ($totalItems) {
                $pic = trim($row->pic ?: 'Belum Ditentukan');
                $items = SumutChecklistItem::where('pic', $row->pic)->get();
                $count = $items->count();
                $ada = $items->where('status', 'Ada')->count();
                $tidakAda = $items->where('status', 'Tidak Ada')->count();
                $pctAda = $count > 0 ? round(($ada / $count) * 100) : 0;
                $sharePct = $totalItems > 0 ? round(($count / $totalItems) * 100, 1) : 0;
                $avgProg = round($items->avg('progress_percent') ?? 0);

                return [
                    'pic' => $pic,
                    'total' => $count,
                    'ada' => $ada,
                    'tidak_ada' => $tidakAda,
                    'percent' => $pctAda,
                    'share_percent' => $sharePct,
                    'avg_progress' => $avgProg,
                ];
            })
            ->sortByDesc('total')
            ->values();

        return [
            'total_items' => $totalItems,
            'ada_count' => $adaCount,
            'tidak_ada_count' => $tidakAdaCount,
            'total_evidences' => $totalEvidences,
            'total_size_formatted' => $this->formatBytes($totalEvidenceBytes),
            'avg_progress' => $avgProgress,
            'categories' => $categories,
            'pic_stats' => $picStats,
        ];
    }

    /**
     * Format bytes to human readable format
     */
    public function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = (int) floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }
}
