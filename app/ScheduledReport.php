<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduledReport extends Model
{
    use \App\Support\Auditable;

    protected string $auditEvent = 'scheduled_report';
    protected array $auditIgnore = ['last_run_at', 'next_run_at'];

    protected $fillable = [
        'user_id',
        'name',
        'scope',
        'format',
        'frequency',
        'send_time',
        'day_of_week',
        'day_of_month',
        'timezone',
        'recipients',
        'filters',
        'is_active',
        'last_run_at',
        'next_run_at',
    ];

    protected $casts = [
        'recipients' => 'array',
        'filters' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function runs()
    {
        return $this->hasMany(ScheduledReportRun::class, 'scheduled_report_id');
    }
}
