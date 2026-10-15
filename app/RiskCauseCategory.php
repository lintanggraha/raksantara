<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskCauseCategory extends Model
{
    use \App\Support\Auditable;

    protected string $auditEvent = 'parameter.cause_category';

    use HasFactory;

    protected $fillable = [
        'name',
    ];
}
