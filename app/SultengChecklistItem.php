<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SultengChecklistItem extends Model
{
    protected $table = 'sulteng_checklist_items';

    protected $fillable = [
        'category_code',
        'category_name',
        'item_number',
        'code',
        'title',
        'clean_title',
        'period',
        'pic_division',
        'status',
        'notes',
        'sort_order',
    ];

    /**
     * Evidences attached to this checklist item
     */
    public function evidences(): HasMany
    {
        return $this->hasMany(SultengChecklistEvidence::class, 'checklist_item_id')->orderBy('evidence_index', 'asc');
    }

    /**
     * Get clean single-line title safe for display and file naming
     */
    public function getCleanTitleAttribute($value): string
    {
        if (!empty($value)) {
            return $value;
        }

        return static::sanitizeTitleForFilename($this->title);
    }

    /**
     * Convert item title to a clean string suitable for Windows filenames
     */
    public static function sanitizeTitleForFilename(string $title): string
    {
        // If title has multiple lines (e.g. Technical Reference with bullets),
        // we can take the primary topic line or combine with dashes
        $lines = preg_split('/[\r\n]+/', trim($title));
        $firstLine = trim($lines[0]);
        
        // Remove trailing colon
        $firstLine = rtrim($firstLine, ':');
        
        // Replace invalid Windows filename characters: \ / : * ? " < > |
        $sanitized = preg_replace('/[\\/\\\\:*?"<>|]/', '-', $firstLine);
        
        // Clean whitespace and consecutive hyphens
        $sanitized = preg_replace('/\s+/', ' ', $sanitized);
        $sanitized = preg_replace('/-+/', '-', $sanitized);
        $sanitized = trim($sanitized, " -.");

        // Fallback if empty
        if (empty($sanitized)) {
            $sanitized = 'Evidence';
        }

        // Limit length to avoid Windows MAX_PATH issues
        if (mb_strlen($sanitized) > 120) {
            $sanitized = mb_substr($sanitized, 0, 120);
        }

        return $sanitized;
    }

    /**
     * Badge CSS class for status
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'Diterima' => 'bg-green-lt text-green border-green',
            'Dalam Proses' => 'bg-yellow-lt text-yellow border-yellow',
            default => 'bg-red-lt text-red border-red',
        };
    }

    /**
     * Status icon
     */
    public function getStatusIconAttribute(): string
    {
        return match ($this->status) {
            'Diterima' => 'fas fa-check-circle',
            'Dalam Proses' => 'fas fa-clock',
            default => 'fas fa-times-circle',
        };
    }
}
