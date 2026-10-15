<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProbabilityScale extends Model
{
    use \App\Support\Auditable;

    protected string $auditEvent = 'parameter.probability_scale';

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
