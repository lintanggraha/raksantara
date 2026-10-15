<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardFilters;
use App\Services\Dashboard\DashboardMetricsService;
use App\Support\RiskAccess;
use App\Support\RiskAnalyticsCacheVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * JSON data for each section of the main dashboard. Sections load
 * independently so one slow or failing section never blocks the page.
 */
class DashboardSectionController extends Controller
{
    private const CACHE_TTL_SECONDS = 120;

    private const SECTIONS = [
        'summary' => 'summary',
        'portfolio' => 'portfolio',
        'attention' => 'attention',
        'heatmap' => 'heatmap',
        'profile' => 'profile',
        'top-risks' => 'topRisks',
        'kris' => 'kriWatchlist',
        'losses' => 'losses',
        'actions' => 'actions',
        'assurance' => 'assurance',
        'trend' => 'trend',
    ];

    public function show(Request $request, string $section): JsonResponse
    {
        abort_unless(array_key_exists($section, self::SECTIONS), 404);
        $request->validate([
            'owner' => 'nullable|string|max:255',
            'category' => 'nullable|integer|min:1',
            'period' => 'nullable|in:month,quarter',
        ]);

        $user = $request->user();
        $filters = DashboardFilters::fromRequest($request);
        $method = self::SECTIONS[$section];
        $key = implode(':', [
            'dashboard', 'v3', RiskAnalyticsCacheVersion::current(), RiskAccess::scopeCacheKey($user), $filters->cacheKey(), $section,
        ]);

        $data = Cache::remember($key, self::CACHE_TTL_SECONDS, function () use ($user, $filters, $method) {
            return (new DashboardMetricsService($user, $filters))->{$method}();
        });

        return response()->json([
            'section' => $section,
            'generated_at' => now()->toIso8601String(),
            'data' => $data,
        ]);
    }

    public function heatmapCell(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kind' => 'required|in:inherent,residual',
            'p' => 'required|integer|between:1,5',
            'i' => 'required|integer|between:1,5',
            'owner' => 'nullable|string|max:255',
            'category' => 'nullable|integer|min:1',
        ]);

        $service = new DashboardMetricsService($request->user(), DashboardFilters::fromRequest($request));

        return response()->json([
            'data' => $service->heatmapCell($validated['kind'], (int) $validated['p'], (int) $validated['i']),
        ]);
    }
}
