<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RiskStatusChangeRequest extends Model
{
    protected $fillable = [
        'risk_id',
        'requested_by',
        'from_status',
        'to_status',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
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
}

