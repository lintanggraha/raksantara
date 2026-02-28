<?php

namespace App\Console\Commands;

use App\ScheduledReport;
use App\Services\ReportDataService;
use App\Services\ScheduledReportDispatchService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DispatchScheduledReportsCommand extends Command
{
    protected $signature = 'reports:dispatch-scheduled {--id= : Dispatch specific schedule ID}';

    protected $description = 'Generate and deliver due scheduled reports via email';

    public function handle(
        ReportDataService $reportDataService,
        ScheduledReportDispatchService $dispatchService
    ): int {
        $query = ScheduledReport::query()
            ->with('user')
            ->where('is_active', true)
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<=', now());

        if (!empty($this->option('id'))) {
            $query->where('id', (int) $this->option('id'));
        }

        $schedules = $query->get();
        if ($schedules->isEmpty()) {
            $this->info('No due schedules.');

            return 0;
        }

        $processed = 0;
        $failed = 0;

        foreach ($schedules as $schedule) {
            $result = $dispatchService->dispatchSchedule($schedule, $reportDataService, 'auto');
            if ($result['success'] ?? false) {
                $processed++;
            } else {
                $failed++;

                Log::error('Scheduled report dispatch failed', [
                    'scheduled_report_id' => (int) $schedule->id,
                    'error' => (string) ($result['error'] ?? 'Unknown error'),
                ]);
            }
        }

        $this->info('Scheduled reports processed: ' . $processed . ', failed: ' . $failed . '.');

        return $failed > 0 ? 1 : 0;
    }
}
