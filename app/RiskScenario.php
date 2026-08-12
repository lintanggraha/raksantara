<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskScenario extends Model
{
    use HasFactory;

    public const TYPES = ['Operational', 'Cyber', 'Liquidity', 'Credit', 'Third Party', 'Regulatory', 'Strategic'];
    public const STATUS_OPTIONS = ['draft', 'approved', 'retired'];

    protected $fillable = [
        'scenario_code',
        'title',
        'scenario_type',
        'status',
        'time_horizon',
        'likelihood',
        'impact',
        'trigger_event',
        'narrative',
        'response_strategy',
        'owner_name',
        'created_by',
        'updated_by',
    ];

    public function impacts()
    {
        return $this->hasMany(RiskScenarioImpact::class)->with('risk');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
