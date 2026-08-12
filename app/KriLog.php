<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KriLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'kri_id',
        'recorded_value',
        'source_recorded_at',
        'source_reference',
        'status_calculated',
        'data_quality_status',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'source_recorded_at' => 'datetime',
    ];

    public function kri()
    {
        return $this->belongsTo(Kri::class);
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
