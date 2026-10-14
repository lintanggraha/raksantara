<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EvidenceAttachment extends Model
{
    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'uploaded_by',
        'original_name',
        'storage_path',
        'mime_type',
        'size',
        'notes',
    ];

    public function attachable()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

