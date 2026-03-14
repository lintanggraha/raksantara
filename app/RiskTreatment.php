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
    'status',
    'deadline',
    'effectiveness',
    'effectiveness_score'
    ];

    public function risk()
    {
        return $this->belongsTo(Risk::class);
    }

    public function alerts()
    {
        return $this->hasMany(RiskAlert::class);
    }

    public function attachments()
    {
        return $this->morphMany(EvidenceAttachment::class, 'attachable');
    }

}
