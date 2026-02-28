<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDashboardPreference extends Model
{
    protected $fillable = [
        'user_id',
        'widget_visibility',
        'widget_order',
        'trend_granularity',
    ];

    protected $casts = [
        'widget_visibility' => 'array',
        'widget_order' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
