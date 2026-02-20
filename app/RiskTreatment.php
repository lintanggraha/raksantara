<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskTreatment extends Model
{
    use HasFactory;
    
    protected $fillable = [
    'risk_id',
    'option',
    'description',
    'pic',
    'deadline',
    'effectiveness',
    'effectiveness_score'
    ];

    public function risk()
    {
        return $this->belongsTo(Risk::class);
    }

}
