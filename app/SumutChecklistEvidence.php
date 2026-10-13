<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SumutChecklistEvidence extends Model
{
    protected $table = 'sumut_checklist_evidences';

    protected $fillable = [
        'checklist_item_id',
        'uploaded_by',
        'folder_number',
        'file_name',
        'original_filename',
        'file_path',
        'storage_location',
        'file_size',
        'mime_type',
        'file_extension',
        'notes',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(SumutChecklistItem::class, 'checklist_item_id');
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

    public function getIconClassAttribute(): string
    {
        $ext = strtolower($this->file_extension ?: pathinfo($this->file_name, PATHINFO_EXTENSION));
        return match ($ext) {
            'pdf' => 'far fa-file-pdf text-danger',
            'doc', 'docx' => 'far fa-file-word text-primary',
            'xls', 'xlsx', 'csv' => 'far fa-file-excel text-success',
            'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg' => 'far fa-file-image text-warning',
            'zip', 'rar', '7z', 'tar', 'gz' => 'far fa-file-archive text-secondary',
            'mp4', 'mkv', 'avi' => 'far fa-file-video text-info',
            'log', 'txt' => 'far fa-file-alt text-info',
            default => 'far fa-file text-muted',
        };
    }

    public function fileExistsOnDisk(): bool
    {
        return !empty($this->file_path) && file_exists($this->file_path);
    }
}
