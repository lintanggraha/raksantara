<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskTreatment extends Model
{
    use \App\Support\Auditable;

    protected string $auditEvent = 'treatment';

    use HasFactory;
    
    protected $fillable = [
    'risk_id',
    'template_id',
    'option',
    'description',
    'pic',
    'status',
    'status_updated_at',
    'deadline',
    'next_action_at',
    'blocked_reason',
    'playbook_checklist',
    'effectiveness',
    'effectiveness_score'
    ];

    protected $casts = [
        'deadline' => 'date',
        'next_action_at' => 'date',
        'status_updated_at' => 'datetime',
    ];

    public function risk()
    {
        return $this->belongsTo(Risk::class);
    }

    public function template()
    {
        return $this->belongsTo(RiskTreatmentTemplate::class, 'template_id');
    }

    public function alerts()
    {
        return $this->hasMany(RiskAlert::class);
    }

    public function attachments()
    {
        return $this->morphMany(EvidenceAttachment::class, 'attachable');
    }

}
