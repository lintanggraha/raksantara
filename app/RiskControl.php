<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskControl extends Model
{
    use HasFactory;

    protected $fillable = [
        'risk_id',
        'control_code',
        'name',
        'description',
        'owner_name',
        'review_frequency',
        'status',
        'design_effectiveness',
        'last_tested_at',
        'next_test_at',
        'created_by',
    ];

    protected $casts = [
        'last_tested_at' => 'date',
        'next_test_at' => 'date',
    ];

    public function risk()
    {
        return $this->belongsTo(Risk::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tests()
    {
        return $this->hasMany(RiskControlTest::class)->latest('tested_at');
    }
}
