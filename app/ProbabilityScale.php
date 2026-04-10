<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProbabilityScale extends Model
{
    protected $fillable = [
        'score',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
