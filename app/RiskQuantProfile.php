<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RiskQuantProfile extends Model
{
    protected $fillable = [
        'radar_group_id',
        'potential_loss_inherent',
        'residual_loss_factor',
        'financial_impact_best_case',
        'financial_impact_most_likely',
        'financial_impact_worst_case',
        'monte_carlo_var',
        'risk_appetite_score',
        'risk_appetite_loss',
    ];

    protected $casts = [
        'potential_loss_inherent' => 'integer',
        'residual_loss_factor' => 'float',
        'financial_impact_best_case' => 'integer',
        'financial_impact_most_likely' => 'integer',
        'financial_impact_worst_case' => 'integer',
        'monte_carlo_var' => 'float',
        'risk_appetite_score' => 'integer',
        'risk_appetite_loss' => 'integer',
    ];

    public function radarGroup()
    {
        return $this->belongsTo(RadarGroup::class);
    }
}
