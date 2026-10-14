<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduledReportRun extends Model
{
    protected $fillable = [
        'scheduled_report_id',
        'trigger_type',
        'status',
        'scope',
        'format',
        'recipient_count',
        'output_filename',
        'error_message',
        'ran_at',
    ];

    protected $casts = [
        'recipient_count' => 'integer',
        'ran_at' => 'datetime',
    ];

    public function scheduledReport()
    {
        return $this->belongsTo(ScheduledReport::class, 'scheduled_report_id');
    }
}
