<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskRcsaWorkspace extends Model
{
    use HasFactory;

    protected $fillable = [
        'risk_id',
        'process_owner',
        'objective',
        'scope',
        'key_controls',
        'control_gaps',
        'likelihood_rationale',
        'impact_rationale',
        'review_frequency',
        'next_review_at',
        'sign_off_name',
        'sign_off_role',
        'signed_off_at',
        'notes',
        'updated_by',
    ];

    protected $casts = [
        'next_review_at' => 'date',
        'signed_off_at' => 'datetime',
    ];

    public function risk()
    {
        return $this->belongsTo(Risk::class);
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
