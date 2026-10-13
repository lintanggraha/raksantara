<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SultengChecklistEvidence extends Model
{
    protected $table = 'sulteng_checklist_evidences';

    protected $fillable = [
        'checklist_item_id',
        'uploaded_by',
        'file_name',
        'file_path',
        'storage_location',
        'original_filename',
        'file_size',
        'mime_type',
        'evidence_index',
        'notes',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(SultengChecklistItem::class, 'checklist_item_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) $this->file_size;
        if ($bytes <= 0) {
            return '0 B';
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = (int) floor(log($bytes, 1024));
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }

    public function getFileExtensionAttribute(): string
    {
        return strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
    }

    public function getIconClassAttribute(): string
    {
        $ext = $this->file_extension;
        return match ($ext) {
            'pdf' => 'far fa-file-pdf text-danger',
            'doc', 'docx' => 'far fa-file-word text-primary',
            'xls', 'xlsx', 'csv' => 'far fa-file-excel text-success',
            'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg' => 'far fa-file-image text-warning',
            'zip', 'rar', '7z', 'tar', 'gz' => 'far fa-file-archive text-secondary',
            'log', 'txt' => 'far fa-file-alt text-info',
            default => 'far fa-file text-muted',
        };
    }

    public function fileExistsOnDisk(): bool
    {
        return !empty($this->file_path) && file_exists($this->file_path);
    }
}
