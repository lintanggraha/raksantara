<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RiskInherentAnalysis extends Model
{
    protected $table = 'risk_inherent_analyses';
    
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
        'inherent_risk_score',
        'risk_level',
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

    public function calculateRiskScore()
    {
        if (!$this->probability_score || !$this->impact_score) {
            return null;
        }

        $score = RiskScoreMatrix::getScore($this->probability_score, $this->impact_score);
        $this->inherent_risk_score = $score;
        $this->risk_level = RiskScoreMatrix::getRiskLevel($score);
        return $score;
    }
}
