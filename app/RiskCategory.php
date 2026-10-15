<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskCategory extends Model
{
    use \App\Support\Auditable;

    protected string $auditEvent = 'parameter.risk_category';

    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function risks()
    {
        return $this->hasMany(Risk::class);
    }
}
