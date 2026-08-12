<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataRetentionSchedule extends Model
{
    use HasFactory;

    public const STATUSES = ['active', 'under_review', 'retired'];

    protected $fillable = [
        'record_type',
        'description',
        'retention_months',
        'owner_name',
        'status',
        'next_review_at',
        'legal_basis',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'next_review_at' => 'date',
    ];

    public function purgeRequests()
    {
        return $this->hasMany(DataPurgeRequest::class);
    }
}
