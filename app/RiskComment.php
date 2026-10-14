<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RiskComment extends Model
{
    protected $fillable = [
        'risk_id',
        'user_id',
        'content',
        'edited_at',
    ];

    protected $casts = [
        'edited_at' => 'datetime',
    ];

    public function risk()
    {
        return $this->belongsTo(Risk::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mentions()
    {
        return $this->belongsToMany(
            User::class,
            'risk_comment_mentions',
            'risk_comment_id',
            'mentioned_user_id'
        )->withTimestamps();
    }

    public function attachments()
    {
        return $this->morphMany(EvidenceAttachment::class, 'attachable');
    }
}
