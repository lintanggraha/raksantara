<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskControlFrameworkMapping extends Model
{
    use HasFactory;

    public const STATUS_OPTIONS = ['mapped', 'partial', 'gap', 'not_applicable'];

    protected $fillable = [
        'risk_control_id',
        'framework_code',
        'control_reference',
        'requirement_title',
        'mapping_status',
        'evidence_notes',
        'last_reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'last_reviewed_at' => 'date',
    ];

    public function control()
    {
        return $this->belongsTo(RiskControl::class, 'risk_control_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
