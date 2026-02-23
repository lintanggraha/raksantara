<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RiskQuantProfile extends Model
{
    protected $fillable = [
        'radar_group_id',
        'potential_loss_inherent',
        'residual_loss_factor',
        'risk_appetite_score',
        'risk_appetite_loss',
    ];

    protected $casts = [
        'potential_loss_inherent' => 'integer',
        'residual_loss_factor' => 'float',
        'risk_appetite_score' => 'integer',
        'risk_appetite_loss' => 'integer',
    ];

    public function radarGroup()
    {
        return $this->belongsTo(RadarGroup::class);
    }
}
