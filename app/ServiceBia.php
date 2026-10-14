<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceBia extends Model
{
    protected $table = 'service_bias';

    protected $fillable = [
        'business_service_id',
        'impact_matrix',
        'mtpd_minutes',
        'tier',
        'rto_minutes',
        'rpo_minutes',
        'minimum_resources',
        'notes',
        'next_review_at',
        'assessed_by',
        'assessed_at',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'impact_matrix' => 'array',
        'next_review_at' => 'date',
        'assessed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(BusinessService::class, 'business_service_id');
    }

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
