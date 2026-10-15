<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RiskOwnerParameter extends Model
{
    use \App\Support\Auditable;

    protected string $auditEvent = 'parameter.risk_owner';

    protected $fillable = [
        'name',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
