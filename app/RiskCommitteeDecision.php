<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskCommitteeDecision extends Model
{
    use HasFactory;

    public const STATUS_OPTIONS = ['open', 'in_progress', 'closed'];

    protected $fillable = [
        'meeting_date',
        'meeting_reference',
        'risk_id',
        'title',
        'decision',
        'owner_name',
        'due_at',
        'status',
        'closure_notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'meeting_date' => 'date',
        'due_at' => 'date',
    ];

    public function risk()
    {
        return $this->belongsTo(Risk::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
