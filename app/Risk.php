<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Risk extends Model
{
    use HasFactory;
    protected $fillable = [
    'risk_id',
    'year',
    'risk_title',
    'risk_category_id',
    'risk_cause_category_id',
    'cause',
    'existing_control',
    'risk_owner',
    'created_by',
    'risk_description',
    'probability_notes',
    'probability_score',
    'impact_scale_id',
    'impact_notes',
    'impact_score',
    'risk_score',
    'status'
];

    public function category()
    {
        return $this->belongsTo(RiskCategory::class, 'risk_category_id');
    }

    public function causeCategory()
    {
        return $this->belongsTo(RiskCauseCategory::class, 'risk_cause_category_id');
    }

    public function getRiskLevelAttribute()
    {
        $score = $this->risk_score;

        if (is_null($score)) return null;

        if ($score <= 5) return 'Low';
        if ($score <= 11) return 'Low to Moderate';
        if ($score <= 15) return 'Moderate';
        if ($score <= 19) return 'Moderate to High';

        return 'High';
    }

    public function impactScale()
    {
        return $this->belongsTo(ImpactScale::class);
    }

    public static function generateRiskId()
    {
        $year = Carbon::now()->year;

        $last = self::where('year', $year)
            ->whereNotNull('risk_id')
            ->orderBy('risk_id', 'desc')
            ->first();

        if (!$last) {
            $nextNumber = 1;
        } else {
            // ambil 3 digit terakhir
            $lastNumber = (int) substr($last->risk_id, -3);
            $nextNumber = $lastNumber + 1;
        }

        return sprintf('RISK-%d-%03d', $year, $nextNumber);
    }
    
    public function treatments()
    {
        return $this->hasMany(\App\RiskTreatment::class);
    }

    public function inherentAnalysis()
    {
        return $this->hasOne(RiskInherentAnalysis::class);
    }

    public function residualAnalysis()
    {
        return $this->hasOne(RiskResidualAnalysis::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

}
