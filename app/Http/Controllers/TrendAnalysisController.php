<?php

namespace App\Http\Controllers;

use App\Risk;
use App\Support\RiskOwnerAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TrendAnalysisController extends Controller
{
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

        $trendData = $this->calculateTrendData($granularity, $daysBack);
        $statistics = $this->calculateTrendStatistics($trendData);
        $categories = $this->calculateCategoryTrends($granularity, $daysBack);
        $statusTrends = $this->calculateStatusTrends($trendData);
        $ownerTrends = $this->calculateOwnerTrends($daysBack);
        $heatmapTrends = $this->calculateHeatmapTrends($granularity, $daysBack);

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

        $trendData = $this->calculateTrendData($granularity, $daysBack);
        $statistics = $this->calculateTrendStatistics($trendData);
        $categories = $this->calculateCategoryTrends($granularity, $daysBack);
        $statusTrends = $this->calculateStatusTrends($trendData);
        $ownerTrends = $this->calculateOwnerTrends($daysBack);
        $heatmapTrends = $this->calculateHeatmapTrends($granularity, $daysBack);

        return response()->json([
            'trend' => $trendData,
            'statistics' => $statistics,
            'categories' => $categories,
            'status' => $statusTrends,
            'owners' => $ownerTrends,
            'heatmap_trends' => $heatmapTrends,
        ]);
    }

    /**
     * Hitung trend data risk score seiring waktu
     */
    private function calculateTrendData(string $granularity, int $daysBack): array
    {
        $startDate = now()->subDays($daysBack)->startOfDay();

        $query = Risk::with(['inherentAnalysis', 'residualAnalysis'])
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at');

        $risks = $query->get();

        if ($risks->isEmpty()) {
            return [];
        }

        $grouped = $this->groupByPeriod($risks, $granularity, $startDate);

        return $this->aggregateTrendMetrics($grouped, $granularity);
    }

    /**
     * Kelompokkan risk berdasarkan periode (monthly, quarterly, yearly)
     */
    private function groupByPeriod($risks, string $granularity, Carbon $startDate): array
    {
        $grouped = [];

        foreach ($risks as $risk) {
            $createdAt = Carbon::parse($risk->created_at)->startOfDay();
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

        $inherentValues = array_filter(array_column($trendData, 'inherent_avg'));
        $residualValues = array_filter(array_column($trendData, 'residual_avg'));
        $riskCounts = array_column($trendData, 'risk_count');

        $avgInherent = !empty($inherentValues) ? round(array_sum($inherentValues) / count($inherentValues), 2) : 0;
        $avgResidual = !empty($residualValues) ? round(array_sum($residualValues) / count($residualValues), 2) : 0;
        $maxInherent = max(array_filter(array_column($trendData, 'inherent_max'))) ?: 0;
        $maxResidual = max(array_filter(array_column($trendData, 'residual_max'))) ?: 0;
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
     * Hitung trend per kategori risk
     */
    private function calculateCategoryTrends(string $granularity, int $daysBack): array
    {
        $startDate = now()->subDays($daysBack);

        $data = Risk::with(['category', 'inherentAnalysis', 'residualAnalysis'])
            ->where('created_at', '>=', $startDate)
            ->get()
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
    private function calculateOwnerTrends(int $daysBack): array
    {
        $startDate = now()->subDays($daysBack)->startOfDay();

        $data = Risk::with(['inherentAnalysis', 'residualAnalysis'])
            ->where('created_at', '>=', $startDate)
            ->get()
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
    private function calculateHeatmapTrends(string $granularity, int $daysBack): array
    {
        $startDate = now()->subDays($daysBack)->startOfDay();
        $risks = Risk::with(['inherentAnalysis', 'residualAnalysis'])
            ->where('created_at', '>=', $startDate)
            ->orderBy('created_at')
            ->get();

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

        $grouped = $this->groupByPeriod($risks, $granularity, $startDate);
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
