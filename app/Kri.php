<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kri extends Model
{
    use HasFactory;

    protected $fillable = [
        'risk_id',
        'metric_name',
        'description',
        'data_source',
        'source_identifier',
        'source_reference',
        'expected_refresh_minutes',
        'source_updated_at',
        'data_quality_status',
        'data_quality_notes',
        'last_quality_checked_at',
        'last_sync_error',
        'threshold_type',
        'threshold_warn',
        'threshold_critical',
        'current_value',
        'status',
        'last_updated_by',
    ];

    protected $casts = [
        'source_updated_at' => 'datetime',
        'last_quality_checked_at' => 'datetime',
    ];

    public function risk()
    {
        return $this->belongsTo(Risk::class);
    }

    public function logs()
    {
        return $this->hasMany(KriLog::class)->orderBy('created_at', 'desc');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    /**
     * Automatically calculates and returns the color status 
     * based on a given value and the KRI's thresholds.
     */
    public function calculateStatusForValue($value): string
    {
        $value = (float) $value;
        $warn = is_null($this->threshold_warn) ? null : (float) $this->threshold_warn;
        $critical = is_null($this->threshold_critical) ? null : (float) $this->threshold_critical;

        if ($warn === null && $critical === null) {
            return 'Green';
        }

        if ($this->threshold_type === 'lower_is_better') {
            if ($critical !== null && $value >= $critical) return 'Red';
            if ($warn !== null && $value >= $warn) return 'Amber';
            return 'Green';
        } else {
            // higher_is_better
            if ($critical !== null && $value <= $critical) return 'Red';
            if ($warn !== null && $value <= $warn) return 'Amber';
            return 'Green';
        }
    }
}
