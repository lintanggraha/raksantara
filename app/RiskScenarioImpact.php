<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskScenarioImpact extends Model
{
    use HasFactory;

    protected $fillable = [
        'risk_scenario_id',
        'risk_id',
        'scenario_probability',
        'scenario_impact',
        'scenario_score',
        'assumptions',
        'mitigation_response',
    ];

    public function scenario()
    {
        return $this->belongsTo(RiskScenario::class, 'risk_scenario_id');
    }

    public function risk()
    {
        return $this->belongsTo(Risk::class);
    }
}
