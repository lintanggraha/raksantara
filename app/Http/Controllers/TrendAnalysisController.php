<?php

namespace App\Http\Controllers;

use App\Risk;
use App\Support\RiskAnalyticsCacheVersion;
use App\Support\RiskAccess;
use App\Support\RiskOwnerAccess;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TrendAnalysisController extends Controller
{
    private const TREND_ANALYSIS_CACHE_TTL_SECONDS = 300;

    /**
     * Tampilkan halaman Risk Trend Analysis
     */
    public function index(Request $request)
    {
        $request->validate([
            'granularity' => 'nullable|in:monthly,quarterly,yearly',
            'days' => 'nullable|integer|min:30|max:1460',
        ]);

        $granularity = $request->input('granularity', 'quarterly');
        $daysBack = (int) $request->input('days', 365);
        $payload = $this->getCachedTrendPayload($granularity, $daysBack);

        $trendData = $payload['trendData'];
        $statistics = $payload['statistics'];
        $categories = $payload['categories'];
        $statusTrends = $payload['statusTrends'];
        $ownerTrends = $payload['ownerTrends'];
        $heatmapTrends = $payload['heatmapTrends'];

        return view('trends.analysis', compact(
            'trendData',
            'statistics',
            'categories',
            'statusTrends',
            'ownerTrends',
            'heatmapTrends',
            'granularity',
            'daysBack'
        ));
    }

    /**
     * API endpoint untuk trend data (JSON)
     */
    public function data(Request $request)
    {
        $request->validate([
            'granularity' => 'nullable|in:monthly,quarterly,yearly',
            'days' => 'nullable|integer|min:30|max:1460',
        ]);

        $granularity = $request->input('granularity', 'quarterly');
        $daysBack = (int) $request->input('days', 365);
        $payload = $this->getCachedTrendPayload($granularity, $daysBack);

        return response()->json([
            'trend' => $payload['trendData'],
            'statistics' => $payload['statistics'],
            'categories' => $payload['categories'],
            'status' => $payload['statusTrends'],
            'owners' => $payload['ownerTrends'],
            'heatmap_trends' => $payload['heatmapTrends'],
        ]);
    }

    private function getCachedTrendPayload(string $granularity, int $daysBack): array
    {
        $actor = auth()->user();
        $scopeKey = RiskAccess::scopeCacheKey($actor instanceof User ? $actor : null);
        $cacheKey = implode(':', [
            'risk',
            'trend_analysis',
            'v2',
            'cv' . RiskAnalyticsCacheVersion::current(),
            'scope_' . $scopeKey,
            'g' . $granularity,
            'd' . $daysBack,
        ]);

        return Cache::remember($cacheKey, now()->addSeconds(self::TREND_ANALYSIS_CACHE_TTL_SECONDS), function () use ($granularity, $daysBack) {
            return $this->buildTrendPayload($granularity, $daysBack);
        });
    }

    private function buildTrendPayload(string $granularity, int $daysBack): array
    {
        $risks = $this->loadTrendRisks($daysBack);
        $trendData = $this->calculateTrendData($risks, $granularity);

        return [
            'trendData' => $trendData,
            'statistics' => $this->calculateTrendStatistics($trendData),
            'categories' => $this->calculateCategoryTrends($risks),
            'statusTrends' => $this->calculateStatusTrends($trendData),
            'ownerTrends' => $this->calculateOwnerTrends($risks),
            'heatmapTrends' => $this->calculateHeatmapTrends($risks, $granularity),
        ];
    }

    private function loadTrendRisks(int $daysBack): Collection
    {
        $startDate = now()->subDays($daysBack)->startOfDay();

        $query = Risk::query()
            ->select([
                'id',
                'risk_category_id',
                'risk_owner',
                'status',
                'created_at',
            ])
            ->with([
                'category:id,name',
                'inherentAnalysis:id,risk_id,inherent_risk_score',
                'residualAnalysis:id,risk_id,residual_risk_score,probability_score,impact_score',
            ])
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at');
        RiskAccess::applyVisibilityScope($query, auth()->user());

        return $query->get();
    }

    /**
     * Hitung trend data risk score seiring waktu
     */
    private function calculateTrendData(Collection $risks, string $granularity): array
    {
        if ($risks->isEmpty()) {
            return [];
        }

        $grouped = $this->groupByPeriod($risks, $granularity);

        return $this->aggregateTrendMetrics($grouped, $granularity);
    }

    /**
     * Kelompokkan risk berdasarkan periode (monthly, quarterly, yearly)
     */
    private function groupByPeriod(Collection $risks, string $granularity): array
    {
        $grouped = [];

        foreach ($risks as $risk) {
            $createdAt = $risk->created_at instanceof Carbon
                ? $risk->created_at->copy()->startOfDay()
                : Carbon::parse($risk->created_at)->startOfDay();
            $key = $this->getPeriodKey($createdAt, $granularity);

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'period_key' => $key,
                    'period_label' => $this->getPeriodLabel($createdAt, $granularity),
                    'start_date' => $this->getPeriodStart($createdAt, $granularity),
                    'end_date' => $this->getPeriodEnd($createdAt, $granularity),
                    'risks' => [],
                ];
            }

            $grouped[$key]['risks'][] = $risk;
        }

        // Sort by period
        uksort($grouped, function ($a, $b) {
            return strcmp($a, $b);
        });

        return $grouped;
    }

    /**
     * Dapatkan key periode berdasarkan granularity
     */
    private function getPeriodKey(Carbon $date, string $granularity): string
    {
        if ($granularity === 'monthly') {
            return $date->format('Y-m');
        }

        if ($granularity === 'yearly') {
            return $date->format('Y');
        }

        // quarterly
        $quarter = ceil($date->month / 3);
        return $date->format('Y') . '-Q' . $quarter;
    }

    /**
     * Dapatkan label periode
     */
    private function getPeriodLabel(Carbon $date, string $granularity): string
    {
        if ($granularity === 'monthly') {
            return $date->format('M Y');
        }

        if ($granularity === 'yearly') {
            return $date->format('Y');
        }

        // quarterly
        $quarter = ceil($date->month / 3);
        return 'Q' . $quarter . ' ' . $date->format('Y');
    }

    /**
     * Dapatkan tanggal awal periode
     */
    private function getPeriodStart(Carbon $date, string $granularity): Carbon
    {
        if ($granularity === 'monthly') {
            return $date->copy()->startOfMonth();
        }

        if ($granularity === 'yearly') {
            return $date->copy()->startOfYear();
        }

        // quarterly
        $quarter = ceil($date->month / 3);
        return $date->copy()->month(($quarter - 1) * 3 + 1)->startOfMonth();
    }

    /**
     * Dapatkan tanggal akhir periode
     */
    private function getPeriodEnd(Carbon $date, string $granularity): Carbon
    {
        if ($granularity === 'monthly') {
            return $date->copy()->endOfMonth();
        }

        if ($granularity === 'yearly') {
            return $date->copy()->endOfYear();
        }

        // quarterly
        $quarter = ceil($date->month / 3);
        return $date->copy()->month($quarter * 3)->endOfMonth();
    }

    /**
     * Agregasi metrik trend dari risk yang dikelompokkan
     */
    private function aggregateTrendMetrics(array $grouped, string $granularity): array
    {
        $result = [];

        foreach ($grouped as $periodData) {
            $risks = $periodData['risks'];
            $n = count($risks);

            if ($n === 0) {
                continue;
            }

            $inherentScores = [];
            $residualScores = [];
            $statusCounts = ['Open' => 0, 'Monitoring' => 0, 'Closed' => 0];

            foreach ($risks as $risk) {
                $statusCounts[$risk->status] = ($statusCounts[$risk->status] ?? 0) + 1;

                if ($risk->inherentAnalysis) {
                    if ($risk->inherentAnalysis->inherent_risk_score) {
                        $inherentScores[] = (float) $risk->inherentAnalysis->inherent_risk_score;
                    }
                }

                if ($risk->residualAnalysis) {
                    if ($risk->residualAnalysis->residual_risk_score) {
                        $residualScores[] = (float) $risk->residualAnalysis->residual_risk_score;
                    }
                }
            }

            $avgInherent = !empty($inherentScores) ? round(array_sum($inherentScores) / count($inherentScores), 2) : null;
            $avgResidual = !empty($residualScores) ? round(array_sum($residualScores) / count($residualScores), 2) : null;
            $maxInherent = !empty($inherentScores) ? max($inherentScores) : null;
            $maxResidual = !empty($residualScores) ? max($residualScores) : null;

            $result[] = [
                'period' => $periodData['period_label'],
                'period_key' => $periodData['period_key'],
                'start_date' => $periodData['start_date']->format('Y-m-d'),
                'end_date' => $periodData['end_date']->format('Y-m-d'),
                'risk_count' => $n,
                'inherent_avg' => $avgInherent,
                'inherent_max' => $maxInherent,
                'residual_avg' => $avgResidual,
                'residual_max' => $maxResidual,
                'status' => $statusCounts,
            ];
        }

        return $result;
    }

    /**
     * Hitung statistik trend
     */
    private function calculateTrendStatistics(array $trendData): array
    {
        if (empty($trendData)) {
            return [
                'avg_inherent' => 0,
                'avg_residual' => 0,
                'max_inherent' => 0,
                'max_residual' => 0,
                'total_risks' => 0,
                'improvement_percentage' => 0,
            ];
        }

        $inherentValues = $this->extractNumericTrendValues($trendData, 'inherent_avg');
        $residualValues = $this->extractNumericTrendValues($trendData, 'residual_avg');
        $inherentMaxValues = $this->extractNumericTrendValues($trendData, 'inherent_max');
        $residualMaxValues = $this->extractNumericTrendValues($trendData, 'residual_max');
        $riskCounts = array_column($trendData, 'risk_count');

        $avgInherent = !empty($inherentValues) ? round(array_sum($inherentValues) / count($inherentValues), 2) : 0;
        $avgResidual = !empty($residualValues) ? round(array_sum($residualValues) / count($residualValues), 2) : 0;
        $maxInherent = !empty($inherentMaxValues) ? max($inherentMaxValues) : 0;
        $maxResidual = !empty($residualMaxValues) ? max($residualMaxValues) : 0;
        $totalRisks = array_sum($riskCounts);

        // Hitung improvement (penurunan dari inherent ke residual)
        $improvement = 0;
        if ($avgInherent > 0) {
            $improvement = round((($avgInherent - $avgResidual) / $avgInherent) * 100, 2);
        }

        return [
            'avg_inherent' => $avgInherent,
            'avg_residual' => $avgResidual,
            'max_inherent' => $maxInherent,
            'max_residual' => $maxResidual,
            'total_risks' => $totalRisks,
            'improvement_percentage' => $improvement,
        ];
    }

    /**
     * @return array<int, float>
     */
    private function extractNumericTrendValues(array $trendData, string $column): array
    {
        $values = [];

        foreach ($trendData as $row) {
            if (!is_array($row) || !array_key_exists($column, $row)) {
                continue;
            }

            $value = $row[$column];
            if (!is_numeric($value)) {
                continue;
            }

            $values[] = (float) $value;
        }

        return $values;
    }

    /**
     * Hitung trend per kategori risk
     */
    private function calculateCategoryTrends(Collection $risks): array
    {
        if ($risks->isEmpty()) {
            return [];
        }

        $data = $risks
            ->groupBy('risk_category_id')
            ->map(function ($risks, $categoryId) {
                $category = optional($risks->first()->category)->name ?? 'Uncategorized';
                $n = $risks->count();
                $inherentScores = $risks->pluck('inherentAnalysis.inherent_risk_score')
                    ->filter()
                    ->map(fn($v) => (float) $v)
                    ->toArray();
                $residualScores = $risks->pluck('residualAnalysis.residual_risk_score')
                    ->filter()
                    ->map(fn($v) => (float) $v)
                    ->toArray();

                return [
                    'category' => $category,
                    'count' => $n,
                    'inherent_avg' => !empty($inherentScores) ? round(array_sum($inherentScores) / count($inherentScores), 2) : 0,
                    'residual_avg' => !empty($residualScores) ? round(array_sum($residualScores) / count($residualScores), 2) : 0,
                ];
            })
            ->sortByDesc('count')
            ->values();

        return $data->toArray();
    }

    /**
     * Hitung trend per status
     */
    private function calculateStatusTrends(array $trendData): array
    {
        $statusTrends = [];

        foreach (['Open', 'Monitoring', 'Closed'] as $status) {
            $trends = [];

            foreach ($trendData as $periodData) {
                $count = (int) ($periodData['status'][$status] ?? 0);
                $trends[] = [
                    'period' => (string) ($periodData['period'] ?? '-'),
                    'count' => $count,
                ];
            }

            $statusTrends[$status] = $trends;
        }

        return $statusTrends;
    }

    /**
     * Ringkasan risiko per owner/divisi untuk chart konsentrasi.
     */
    private function calculateOwnerTrends(Collection $risks): array
    {
        if ($risks->isEmpty()) {
            return [];
        }

        $data = $risks
            ->groupBy(function ($risk) {
                return RiskOwnerAccess::displayLabel($risk->risk_owner);
            })
            ->map(function ($risks, $owner) {
                $risks = collect($risks)->values();
                $inherentScores = $risks->pluck('inherentAnalysis.inherent_risk_score')
                    ->filter(function ($value) {
                        return is_numeric($value);
                    })
                    ->map(function ($value) {
                        return (float) $value;
                    })
                    ->values();
                $residualScores = $risks->pluck('residualAnalysis.residual_risk_score')
                    ->filter(function ($value) {
                        return is_numeric($value);
                    })
                    ->map(function ($value) {
                        return (float) $value;
                    })
                    ->values();

                return [
                    'owner' => (string) $owner,
                    'count' => (int) $risks->count(),
                    'inherent_avg' => $inherentScores->isEmpty() ? 0.0 : round((float) $inherentScores->avg(), 2),
                    'residual_avg' => $residualScores->isEmpty() ? 0.0 : round((float) $residualScores->avg(), 2),
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->take(10);

        return $data->toArray();
    }

    /**
     * Trend high-risk exposure per periode berdasarkan score >= 16.
     */
    private function calculateHeatmapTrends(Collection $risks, string $granularity): array
    {
        if ($risks->isEmpty()) {
            return [
                'rows' => [],
                'latest_period' => '-',
                'previous_period' => '-',
                'latest_residual_high_count' => 0,
                'previous_residual_high_count' => 0,
                'change_residual_high_count' => 0,
                'direction' => 'stable',
            ];
        }

        $grouped = $this->groupByPeriod($risks, $granularity);
        $highRiskThreshold = 16;
        $rows = [];

        foreach ($grouped as $periodData) {
            $bucketRisks = $periodData['risks'];
            $riskCount = count($bucketRisks);
            $inherentHighCount = 0;
            $residualHighCount = 0;
            $inherentScoreSum = 0.0;
            $residualScoreSum = 0.0;
            $inherentScoreCount = 0;
            $residualScoreCount = 0;
            $residualCells = [];

            foreach ($bucketRisks as $risk) {
                $inherentScore = optional($risk->inherentAnalysis)->inherent_risk_score;
                if (is_numeric($inherentScore)) {
                    $inherentScore = (float) $inherentScore;
                    $inherentScoreSum += $inherentScore;
                    $inherentScoreCount++;
                    if ($inherentScore >= $highRiskThreshold) {
                        $inherentHighCount++;
                    }
                }

                $residualScore = optional($risk->residualAnalysis)->residual_risk_score;
                if (is_numeric($residualScore)) {
                    $residualScore = (float) $residualScore;
                    $residualScoreSum += $residualScore;
                    $residualScoreCount++;
                    if ($residualScore >= $highRiskThreshold) {
                        $residualHighCount++;
                    }
                }

                $residualProbability = (int) (optional($risk->residualAnalysis)->probability_score ?? 0);
                $residualImpact = (int) (optional($risk->residualAnalysis)->impact_score ?? 0);
                if ($residualProbability >= 1 && $residualProbability <= 5 && $residualImpact >= 1 && $residualImpact <= 5) {
                    $cellKey = 'P' . $residualProbability . 'xI' . $residualImpact;
                    $residualCells[$cellKey] = (int) (($residualCells[$cellKey] ?? 0) + 1);
                }
            }

            $dominantCell = '-';
            if (!empty($residualCells)) {
                arsort($residualCells);
                $dominantCellKey = (string) array_key_first($residualCells);
                if ($dominantCellKey !== '') {
                    $dominantCell = (string) preg_replace('/xI/', ' x I', $dominantCellKey);
                }
            }

            $avgInherent = $inherentScoreCount > 0 ? round($inherentScoreSum / $inherentScoreCount, 2) : 0.0;
            $avgResidual = $residualScoreCount > 0 ? round($residualScoreSum / $residualScoreCount, 2) : 0.0;
            $highRiskReduction = $inherentHighCount - $residualHighCount;
            $highRiskReductionPercent = $inherentHighCount > 0
                ? round(($highRiskReduction / $inherentHighCount) * 100, 2)
                : 0.0;

            $rows[] = [
                'period' => (string) $periodData['period_label'],
                'period_key' => (string) $periodData['period_key'],
                'risk_count' => (int) $riskCount,
                'inherent_high_count' => (int) $inherentHighCount,
                'residual_high_count' => (int) $residualHighCount,
                'high_risk_reduction' => (int) $highRiskReduction,
                'high_risk_reduction_percent' => (float) $highRiskReductionPercent,
                'avg_inherent_score' => (float) $avgInherent,
                'avg_residual_score' => (float) $avgResidual,
                'dominant_residual_cell' => (string) $dominantCell,
            ];
        }

        $latest = !empty($rows) ? $rows[count($rows) - 1] : null;
        $previous = count($rows) > 1 ? $rows[count($rows) - 2] : null;
        $latestResidualHigh = (int) ($latest['residual_high_count'] ?? 0);
        $previousResidualHigh = (int) ($previous['residual_high_count'] ?? 0);
        $changeResidualHigh = $latestResidualHigh - $previousResidualHigh;
        $direction = $changeResidualHigh < 0
            ? 'improving'
            : ($changeResidualHigh > 0 ? 'deteriorating' : 'stable');

        return [
            'rows' => $rows,
            'latest_period' => (string) ($latest['period'] ?? '-'),
            'previous_period' => (string) ($previous['period'] ?? '-'),
            'latest_residual_high_count' => $latestResidualHigh,
            'previous_residual_high_count' => $previousResidualHigh,
            'change_residual_high_count' => $changeResidualHigh,
            'direction' => $direction,
        ];
    }
}
