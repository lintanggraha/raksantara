<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RiskScoreMatrix extends Model
{
    protected $table = 'risk_score_matrix';
    
    protected $fillable = [
        'probability_score',
        'impact_score',
        'risk_score',
    ];

    public static function getScore($probability, $impact)
    {
        return self::where('probability_score', $probability)
                   ->where('impact_score', $impact)
                   ->first()?->risk_score;
    }

    public static function getRiskLevel($score)
    {
        if (is_null($score)) {
            return null;
        }

        $score = (int) $score;

        if ($score < 6) return 'Low';
        if ($score < 12) return 'Low to Moderate';
        if ($score < 16) return 'Moderate';
        if ($score < 20) return 'Moderate to High';
        return 'High';
    }
}
