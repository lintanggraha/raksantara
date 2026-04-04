<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bcp extends Model
{
    use HasFactory;

    protected $fillable = [
        'bia_id',
        'strategy_summary',
        'recovery_procedures',
        'primary_workaround',
        'testing_frequency',
        'last_tested_at',
        'last_test_result',
    ];

    protected $casts = [
        'last_tested_at' => 'date',
    ];

    public function bia()
    {
        return $this->belongsTo(Bia::class, 'bia_id');
    }
}
