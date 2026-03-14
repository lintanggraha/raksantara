<?php

namespace App\Http\Middleware;

use App\Services\ThreatIntelligenceService;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AutoRefreshThreatIntel
{
    private const LAST_RUN_KEY = 'threat-intel:auto-refresh:last-run-at';
    private const RUNNING_KEY = 'threat-intel:auto-refresh:running';
    private const RUNNING_TTL_MINUTES = 5;

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if (!$this->shouldAutoRefresh($request)) {
            return;
        }

        if (!$this->isDue()) {
            return;
        }

        if (!Cache::add(self::RUNNING_KEY, 1, now()->addMinutes(self::RUNNING_TTL_MINUTES))) {
            return;
        }

        try {
            /** @var ThreatIntelligenceService $service */
            $service = app(ThreatIntelligenceService::class);
            $force = (bool) config('services.threat_intel.auto_refresh_force', true);
            $service->refreshWorkingSet($force);

            Cache::put(self::LAST_RUN_KEY, now()->toIso8601String(), now()->addDays(30));
        } catch (\Throwable $e) {
            Log::warning('Threat intel auto refresh failed.', [
                'error' => $e->getMessage(),
            ]);
        } finally {
            Cache::forget(self::RUNNING_KEY);
        }
    }

    private function shouldAutoRefresh(Request $request): bool
    {
        if (!(bool) config('services.threat_intel.auto_refresh_enabled', true)) {
            return false;
        }

        if (strtoupper((string) $request->method()) !== 'GET') {
            return false;
        }

        if ($request->expectsJson() || $request->isXmlHttpRequest()) {
            return false;
        }

        if (is_null($request->user())) {
            return false;
        }

        if ($request->is('health') || $request->is('health/*')) {
            return false;
        }

        return true;
    }

    private function isDue(): bool
    {
        $intervalMinutes = max(5, (int) config('services.threat_intel.auto_refresh_interval_minutes', 30));
        $lastRunRaw = Cache::get(self::LAST_RUN_KEY);

        if (!is_string($lastRunRaw) || trim($lastRunRaw) === '') {
            return true;
        }

        try {
            return Carbon::parse($lastRunRaw)->lte(now()->subMinutes($intervalMinutes));
        } catch (\Throwable $e) {
            return true;
        }
    }
}

