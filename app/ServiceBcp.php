<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceBcp extends Model
{
    protected $fillable = [
        'business_service_id',
        'activation_criteria',
        'strategy_summary',
        'recovery_procedures',
        'primary_workaround',
        'contacts',
        'testing_frequency',
        'updated_by',
    ];

    public function service()
    {
        return $this->belongsTo(BusinessService::class, 'business_service_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
