<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskIncidentAction extends Model
{
    use HasFactory;

    public const ACTION_TYPES = ['Corrective', 'Preventive'];
    public const STATUS_OPTIONS = ['open', 'in_progress', 'completed', 'verified'];

    protected $fillable = [
        'risk_incident_id',
        'action_type',
        'title',
        'description',
        'owner_name',
        'due_at',
        'status',
        'completion_notes',
        'completed_by',
        'completed_at',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'due_at' => 'date',
        'completed_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function incident()
    {
        return $this->belongsTo(RiskIncident::class, 'risk_incident_id');
    }

    public function completer()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
