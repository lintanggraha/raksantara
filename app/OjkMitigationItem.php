<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OjkMitigationItem extends Model
{
    protected $fillable = [
        'item_number',
        'action_plan',
        'pic',
        'solution',
        'target',
        'target_date',
        'mitigation_status',
        'sort_order',
    ];

    protected $casts = [
        'target_date' => 'date',
    ];
}
