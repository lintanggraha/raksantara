<?php

namespace App\Console\Commands;

use App\Services\ThreatIntelligenceService;
use Illuminate\Console\Command;

class RefreshThreatIntelCommand extends Command
{
    protected $signature = 'threat-intel:refresh
        {--force : Bypass cache and fetch fresh feed from source}
        {--days=30 : NVD publication window (7,30,90)}
        {--limit=12 : Dashboard item limit (10,12,20,50)}';

    protected $description = 'Refresh and warm threat intelligence dashboard data';

    public function handle(ThreatIntelligenceService $threatIntelligenceService): int
    {
        $days = (int) $this->option('days');
        $limit = (int) $this->option('limit');
        $force = (bool) $this->option('force');

        $dashboard = $threatIntelligenceService->buildDashboard([
            'days' => $days,
            'limit' => $limit,
        ], $force);

        $displayed = (int) data_get($dashboard, 'summary.displayed_cves', 0);
        $critical = (int) data_get($dashboard, 'summary.critical_cves', 0);
        $nvdStatus = (string) data_get($dashboard, 'sources.nvd.status', 'unknown');
        $kevStatus = (string) data_get($dashboard, 'sources.kev.status', 'unknown');

        $this->info(sprintf(
            'Threat intel refreshed. displayed=%d critical=%d nvd=%s kev=%s',
            $displayed,
            $critical,
            $nvdStatus,
            $kevStatus
        ));

        if ($nvdStatus !== 'ok' || $kevStatus !== 'ok') {
            $this->warn('One or more sources are degraded. Check threat-intelligence source notes in UI.');
        }

        return self::SUCCESS;
    }
}

