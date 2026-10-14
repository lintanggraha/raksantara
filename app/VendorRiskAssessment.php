<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorRiskAssessment extends Model
{
    use HasFactory;

    public const TYPES = ['Due Diligence', 'Periodic Review', 'Incident Review', 'Exit Review'];
    public const DECISIONS = ['Approved', 'Conditional', 'Rejected'];
    public const STATUS_OPTIONS = ['open', 'remediating', 'closed'];

    protected $fillable = [
        'vendor_id',
        'assessment_type',
        'assessed_at',
        'assessed_by',
        'inherent_rating',
        'residual_rating',
        'decision',
        'valid_until',
        'findings',
        'risk_treatment',
        'status',
    ];

    protected $casts = [
        'assessed_at' => 'date',
        'valid_until' => 'date',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }
}
