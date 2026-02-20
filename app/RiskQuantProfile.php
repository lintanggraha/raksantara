<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RiskQuantProfile extends Model
{
    protected $fillable = [
        'radar_group_id',
        'potential_loss_inherent',
        'residual_loss_factor',
    ];

    protected $casts = [
        'potential_loss_inherent' => 'integer',
        'residual_loss_factor' => 'float',
    ];

    public function radarGroup()
    {
        return $this->belongsTo(RadarGroup::class);
    }
}

