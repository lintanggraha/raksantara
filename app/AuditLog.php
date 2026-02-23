<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'actor_id',
        'auditable_type',
        'auditable_id',
        'event',
        'before_state',
        'after_state',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'before_state' => 'array',
        'after_state' => 'array',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}

