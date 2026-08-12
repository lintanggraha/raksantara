<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskControlTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'risk_control_id',
        'tested_by',
        'tested_at',
        'result',
        'findings',
        'remediation_plan',
        'remediation_due_at',
        'remediation_status',
    ];

    protected $casts = [
        'tested_at' => 'date',
        'remediation_due_at' => 'date',
    ];

    public function control()
    {
        return $this->belongsTo(RiskControl::class, 'risk_control_id');
    }

    public function tester()
    {
        return $this->belongsTo(User::class, 'tested_by');
    }
}
