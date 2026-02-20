<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RiskResidualAnalysis extends Model
{
    protected $table = 'risk_residual_analyses';
    
    protected $fillable = [
        'risk_id',
        'probability_score',
        'probability_description',
        'impact_score',
        'impact_description',
        'impact_scale_id',
        'impact_amount',
        'probability_percent',
        'potential_loss',
        'residual_risk_score',
        'risk_adjustment',
        'risk_level',
        'risk_level_adjustment',
        'radar_group_id',
        'radar_description',
        'radar_code',
        'adjustment',
    ];

    protected $casts = [
        'impact_amount' => 'integer',
        'probability_percent' => 'float',
        'potential_loss' => 'integer',
    ];

    public function risk()
    {
        return $this->belongsTo(Risk::class);
    }

    public function impactScale()
    {
        return $this->belongsTo(ImpactScale::class);
    }

    public function radarGroup()
    {
        return $this->belongsTo(RadarGroup::class);
    }

    public function calculateRiskScore()
    {
        if (!$this->probability_score || !$this->impact_score) {
            return null;
        }

        $score = RiskScoreMatrix::getScore($this->probability_score, $this->impact_score);
        $this->residual_risk_score = $score;
        $this->risk_level = RiskScoreMatrix::getRiskLevel($score);
        return $score;
    }
}
