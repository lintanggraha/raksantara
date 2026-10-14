<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RadarGroup extends Model
{
    protected $fillable = [
        'code',
        'description',
    ];

    public function residualAnalyses()
    {
        return $this->hasMany(RiskResidualAnalysis::class);
    }

    public function quantProfile()
    {
        return $this->hasOne(RiskQuantProfile::class);
    }
}
