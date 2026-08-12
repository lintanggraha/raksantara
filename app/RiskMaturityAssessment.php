<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskMaturityAssessment extends Model
{
    use HasFactory;

    public const STATUS_OPTIONS = ['open', 'in_progress', 'achieved'];

    protected $fillable = [
        'assessment_period',
        'domain',
        'current_score',
        'target_score',
        'evidence',
        'improvement_plan',
        'owner_name',
        'target_date',
        'status',
        'assessed_by',
        'updated_by',
    ];

    protected $casts = [
        'target_date' => 'date',
    ];

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }
}
