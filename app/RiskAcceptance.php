<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskAcceptance extends Model
{
    use HasFactory;

    protected $fillable = [
        'risk_id',
        'requested_by',
        'reviewed_by',
        'status',
        'rationale',
        'compensating_controls',
        'accepted_until',
        'decision_notes',
        'decided_at',
    ];

    protected $casts = [
        'accepted_until' => 'date',
        'decided_at' => 'datetime',
    ];

    public function risk()
    {
        return $this->belongsTo(Risk::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isOpen(): bool
    {
        return $this->status === 'requested';
    }
}
