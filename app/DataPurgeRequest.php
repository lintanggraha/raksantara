<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataPurgeRequest extends Model
{
    use HasFactory;

    public const STATUSES = ['requested', 'approved', 'rejected', 'executed', 'cancelled'];

    protected $fillable = [
        'data_retention_schedule_id',
        'scope_reference',
        'estimated_record_count',
        'requested_at',
        'proposed_purge_at',
        'legal_hold_checked',
        'justification',
        'status',
        'review_notes',
        'requested_by',
        'reviewed_by',
        'reviewed_at',
        'executed_by',
        'executed_at',
        'evidence_reference',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'proposed_purge_at' => 'datetime',
        'legal_hold_checked' => 'boolean',
        'reviewed_at' => 'datetime',
        'executed_at' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(DataRetentionSchedule::class, 'data_retention_schedule_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
