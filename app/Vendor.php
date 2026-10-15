<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use \App\Support\Auditable;

    protected string $auditEvent = 'vendor';

    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'contact_name',
        'contact_email',
        'status',
        'onboarding_status',
        'criticality_level',
        'risk_rating',
        'service_owner',
        'data_classification',
        'contract_end_date',
        'next_review_at',
        'last_assessed_at',
    ];

    protected $casts = [
        'contract_end_date' => 'date',
        'next_review_at' => 'date',
        'last_assessed_at' => 'datetime',
    ];

    public function assessments()
    {
        return $this->hasMany(VendorRiskAssessment::class)->latest('assessed_at');
    }

    public function risks()
    {
        return $this->hasMany(Risk::class);
    }
}
