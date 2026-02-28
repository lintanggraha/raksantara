<?php

namespace App\Mail;

use App\ScheduledReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ScheduledReportDelivered extends Mailable
{
    use Queueable, SerializesModels;

    public $scheduledReport;
    public $reportTitle;
    private $filename;
    private $binary;
    private $mime;

    public function __construct(
        ScheduledReport $scheduledReport,
        string $reportTitle,
        string $filename,
        string $binary,
        string $mime
    ) {
        $this->scheduledReport = $scheduledReport;
        $this->reportTitle = $reportTitle;
        $this->filename = $filename;
        $this->binary = $binary;
        $this->mime = $mime;
    }

    public function build()
    {
        return $this
            ->subject('[RMS] Scheduled Report - ' . $this->scheduledReport->name)
            ->view('emails.scheduled-report-delivered')
            ->attachData($this->binary, $this->filename, [
                'mime' => $this->mime,
            ]);
    }
}
