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
        'status_calculated',
        'notes',
        'recorded_by',
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
