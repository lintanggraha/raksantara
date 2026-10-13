<?php

namespace App\Services;

use App\SultengChecklistItem;
use App\SultengChecklistEvidence;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class SultengEvidenceService
{
    /**
     * Get root local evidence storage directory
     */
    public static function getBaseStoragePath(): string
    {
        $primary = (string) config('regional_assessment.sulteng.evidence_path');
        $sourceRoot = (string) config('regional_assessment.sulteng.source_root');
        if ($primary !== '' && $sourceRoot !== '' && is_dir($sourceRoot)) {
            if (!file_exists($primary)) {
                @mkdir($primary, (int) config('regional_assessment.directory_mode', 0750), true);
            }
            if (is_dir($primary) && is_writable($primary)) {
                return $primary;
            }
        }

        // Fallback to local storage/app/sulteng_evidence if D: not available
        $fallback = storage_path('app/sulteng_evidence');
        if (!file_exists($fallback)) {
            @mkdir($fallback, (int) config('regional_assessment.directory_mode', 0750), true);
        }
        return $fallback;
    }

    /**
     * Get directory path for specific category
     */
    public static function getItemDirectory(SultengChecklistItem $item): string
    {
        $base = self::getBaseStoragePath();
        $safeCategory = $item->category_code . ' - ' . preg_replace('/[\\/\\\\:*?"<>|]/', '-', $item->category_name);
        $safeCategory = trim(preg_replace('/\s+/', ' ', $safeCategory), " -.");
        
        $targetDir = $base . DIRECTORY_SEPARATOR . $safeCategory;
        if (!file_exists($targetDir)) {
            @mkdir($targetDir, (int) config('regional_assessment.directory_mode', 0750), true);
        }

        return $targetDir;
    }

    /**
     * Store one or multiple uploaded files for a checklist item
     *
     * @param SultengChecklistItem $item
     * @param array<UploadedFile>|UploadedFile $files
     * @param int|null $userId
     * @param string|null $notes
     * @return array<SultengChecklistEvidence>
     */
    public function storeEvidences(SultengChecklistItem $item, $files, ?int $userId = null, ?string $notes = null): array
    {
        if (!is_array($files)) {
            $files = [$files];
        }

        $files = array_filter($files, fn($f) => $f instanceof UploadedFile);
        if (empty($files)) {
            return [];
        }

        $cleanTitle = $item->clean_title;
        $targetDir = self::getItemDirectory($item);
        $existingEvidences = $item->evidences()->orderBy('evidence_index', 'asc')->get();
        $existingCount = $existingEvidences->count();
        $incomingCount = count($files);
        $totalCount = $existingCount + $incomingCount;

        // If total count is > 1 and there was already 1 existing un-suffixed evidence, rename it to -1
        if ($totalCount > 1 && $existingCount === 1) {
            $first = $existingEvidences->first();
            $firstExt = pathinfo($first->file_name, PATHINFO_EXTENSION);
            $newFirstFilename = "{$cleanTitle}-1.{$firstExt}";
            $newFirstPath = $targetDir . DIRECTORY_SEPARATOR . $newFirstFilename;

            if (file_exists($first->file_path) && $first->file_name !== $newFirstFilename) {
                @rename($first->file_path, $newFirstPath);
            }

            $first->update([
                'file_name' => $newFirstFilename,
                'file_path' => $newFirstPath,
                'evidence_index' => 1,
            ]);
        }

        $blockedExtensions = [
            'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar',
            'exe', 'bat', 'cmd', 'sh', 'bash', 'vbs', 'ps1', 'msi', 'com', 'scr',
            'cgi', 'pl', 'py', 'jar', 'jsp', 'asp', 'aspx', 'htm', 'html', 'svg'
        ];

        // Sanitize clean title further against path traversal tokens
        $cleanTitle = trim(preg_replace('/\.+/', '.', $cleanTitle), " .");

        foreach ($files as $i => $file) {
            $ext = strtolower($file->getClientOriginalExtension());
            if (empty($ext) || in_array($ext, $blockedExtensions, true) || !preg_match('/^[a-z0-9]{1,10}$/', $ext)) {
                $ext = 'dat';
            }

            if ($totalCount === 1) {
                // Exactly 1 evidence in total: no suffix
                $targetFilename = "{$cleanTitle}.{$ext}";
                $evidenceIndex = 1;
            } else {
                // More than 1 evidence in total: add -1, -2, -3 suffix
                $evidenceIndex = $existingCount + $i + 1;
                $targetFilename = "{$cleanTitle}-{$evidenceIndex}.{$ext}";
            }

            $destinationPath = $targetDir . DIRECTORY_SEPARATOR . $targetFilename;

            // Move uploaded file
            $file->move($targetDir, $targetFilename);

            $evidence = $item->evidences()->create([
                'uploaded_by'       => $userId,
                'file_name'         => $targetFilename,
                'file_path'         => $destinationPath,
                'storage_location'  => str_starts_with($destinationPath, 'D:') ? 'local_sulteng' : 'app_storage',
                'original_filename' => $file->getClientOriginalName(),
                'file_size'         => file_exists($destinationPath) ? (int) filesize($destinationPath) : 0,
                'mime_type'         => file_exists($destinationPath) ? (mime_content_type($destinationPath) ?: null) : null,
                'evidence_index'    => $evidenceIndex,
                'notes'             => $notes,
            ]);

            $createdEvidences[] = $evidence;
        }

        // If item status was 'Belum Diterima', automatically update to 'Diterima' (binary status)
        if ($item->status === 'Belum Diterima') {
            $item->update(['status' => 'Diterima']);
        }

        return $createdEvidences;
    }

    /**
     * Delete an evidence and re-index remaining evidences
     */
    public function deleteEvidence(SultengChecklistEvidence $evidence): bool
    {
        $item = $evidence->item;

        // Delete physical file
        if (!empty($evidence->file_path) && file_exists($evidence->file_path)) {
            @unlink($evidence->file_path);
        }

        $evidence->delete();

        if ($item) {
            $this->reindexItemEvidences($item);
        }

        return true;
    }

    /**
     * Re-index and rename remaining evidences for an item
     */
    public function reindexItemEvidences(SultengChecklistItem $item): void
    {
        $cleanTitle = $item->clean_title;
        $targetDir = self::getItemDirectory($item);
        $remaining = $item->evidences()->orderBy('created_at', 'asc')->get();
        $count = $remaining->count();

        if ($count === 0) {
            return;
        }

        if ($count === 1) {
            // Revert single remaining file to un-suffixed
            $single = $remaining->first();
            $ext = pathinfo($single->file_name, PATHINFO_EXTENSION);
            $newName = "{$cleanTitle}.{$ext}";
            $newPath = $targetDir . DIRECTORY_SEPARATOR . $newName;

            if (file_exists($single->file_path) && $single->file_name !== $newName) {
                @rename($single->file_path, $newPath);
            }

            $single->update([
                'file_name' => $newName,
                'file_path' => $newPath,
                'evidence_index' => 1,
            ]);
            return;
        }

        // Multiple files: enforce -1, -2, -3 consecutively
        foreach ($remaining as $idx => $ev) {
            $seq = $idx + 1;
            $ext = pathinfo($ev->file_name, PATHINFO_EXTENSION);
            $newName = "{$cleanTitle}-{$seq}.{$ext}";
            $newPath = $targetDir . DIRECTORY_SEPARATOR . $newName;

            if (file_exists($ev->file_path) && $ev->file_name !== $newName) {
                @rename($ev->file_path, $newPath);
            }

            $ev->update([
                'file_name' => $newName,
                'file_path' => $newPath,
                'evidence_index' => $seq,
            ]);
        }
    }
}
