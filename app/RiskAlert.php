<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RiskAlert extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_RESOLVED = 'resolved';

    protected $fillable = [
        'risk_id',
        'risk_treatment_id',
        'alert_type',
        'severity',
        'status',
        'due_date',
        'message',
        'alerted_at',
        'escalated_at',
        'resolved_at',
        'meta',
    ];

    protected $casts = [
        'due_date' => 'date',
        'alerted_at' => 'datetime',
        'escalated_at' => 'datetime',
        'resolved_at' => 'datetime',
        'meta' => 'array',
    ];

    public function risk()
    {
        return $this->belongsTo(Risk::class);
    }

    public function treatment()
    {
        return $this->belongsTo(RiskTreatment::class, 'risk_treatment_id');
    }
}

