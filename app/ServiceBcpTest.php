<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceBcpTest extends Model
{
    protected $fillable = [
        'business_service_id',
        'tested_at',
        'test_type',
        'result',
        'actual_recovery_minutes',
        'findings',
        'follow_up',
        'recorded_by',
    ];

    protected $casts = [
        'tested_at' => 'date',
    ];

    public function service()
    {
        return $this->belongsTo(BusinessService::class, 'business_service_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
