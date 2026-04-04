<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bia extends Model
{
    use HasFactory;

    protected $fillable = [
        'risk_id',
        'rto',
        'rpo',
        'mao',
        'impact_financial',
        'impact_operational',
        'impact_regulatory',
        'impact_reputational',
        'criticality_tier',
        'justification_notes',
    ];

    public function risk()
    {
        return $this->belongsTo(Risk::class);
    }

    public function bcp()
    {
        return $this->hasOne(Bcp::class, 'bia_id');
    }
}
