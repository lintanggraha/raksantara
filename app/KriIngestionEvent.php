<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KriIngestionEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'source',
        'event_id',
        'kri_id',
        'source_recorded_at',
        'payload_hash',
        'status',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'source_recorded_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function kri()
    {
        return $this->belongsTo(Kri::class);
    }
}
