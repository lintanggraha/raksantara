<?php

namespace App\Services;

use App\RadarGroup;
use App\Risk;
use App\RiskScoreMatrix;
use Carbon\Carbon;

class ReportDataService
{
    public function buildPayload(string $scope, ?int $year = null, array $filters = []): array
    {
        $scope = strtolower(trim($scope));
        $filters = $this->normalizeFilters($filters);
        $risks = $this->loadRisks($year, $filters);

        switch ($scope) {
            case 'monitoring':
                return [
                    'title' => 'Risk Monitoring Report',
                    'file' => $year ? ('risk-monitoring-report-' . $year) : 'risk-monitoring-report',
                    'sections' => $this->buildMonitoringSections($risks),
                ];
            case 'board':
                return [
                    'title' => 'Board of Directors Risk Briefing',
                    'file' => $year ? ('board-risk-briefing-' . $year) : 'board-risk-briefing',
                    'sections' => $this->buildBoardSections($risks),
                ];
            case 'risks':
                return [
                    'title' => 'Risk Register Report',
                    'file' => $year ? ('risk-register-report-' . $year) : 'risk-register-report',
                    'sections' => [$this->buildRiskRegisterSection($risks)],
                ];
            case 'heatmap':
                return [
                    'title' => 'Risk Heatmap Report',
                    'file' => $year ? ('risk-heatmap-report-' . $year) : 'risk-heatmap-report',
                    'sections' => $this->buildHeatmapSections($risks),
                ];
            case 'recap':
                return [
                    'title' => 'Risk Recapitulation Report',
                    'file' => $year ? ('risk-recap-report-' . $year) : 'risk-recap-report',
                    'sections' => $this->buildRecapSections($risks),
                ];
            case 'overall':
                $sections = [];
                $sections = array_merge($sections, $this->buildMonitoringSections($risks));
                $sections[] = $this->buildRiskRegisterSection($risks);
                $sections = array_merge($sections, $this->buildHeatmapSections($risks));
                $sections = array_merge($sections, $this->buildRecapSections($risks));

                return [
                    'title' => 'Overall Risk Management Report',
                    'file' => $year ? ('overall-risk-report-' . $year) : 'overall-risk-report',
                    'sections' => $sections,
                ];
            default:
                abort(404, 'Unknown report scope.');
        }
    }

    private function normalizeFilters(array $filters): array
    {
        $normalized = [
            'risk_category_id' => null,
            'risk_cause_category_id' => null,
        ];

        if (array_key_exists('risk_category_id', $filters) && is_numeric($filters['risk_category_id'])) {
            $normalized['risk_category_id'] = (int) $filters['risk_category_id'];
        }

        if (array_key_exists('risk_cause_category_id', $filters) && is_numeric($filters['risk_cause_category_id'])) {
            $normalized['risk_cause_category_id'] = (int) $filters['risk_cause_category_id'];
        }

        return $normalized;
    }

    private function loadRisks(?int $year = null, array $filters = [])
    {
        $query = Risk::with([
            'category',
            'causeCategory',
            'inherentAnalysis',
            'residualAnalysis.radarGroup',
            'treatments',
        ])->orderBy('id');

        if (!is_null($year)) {
            $query->where('year', $year);
        }

        if (!empty($filters['risk_category_id'])) {
            $query->where('risk_category_id', (int) $filters['risk_category_id']);
        }

        if (!empty($filters['risk_cause_category_id'])) {
            $query->where('risk_cause_category_id', (int) $filters['risk_cause_category_id']);
        }

        return $query->get();
    }

    private function buildRiskRegisterSection($risks): array
    {
        $risks = collect($risks)->sortBy('id')->values();

        $rows = [];
        foreach ($risks as $risk) {
            $radarCode = optional(optional($risk->residualAnalysis)->radarGroup)->code
                ?: (optional($risk->residualAnalysis)->radar_code ?: '-');

            $rows[] = [
                (string) ($risk->risk_id ?: '-'),
                (string) ($risk->year ?: '-'),
                (string) ($risk->risk_title ?: '-'),
                (string) (optional($risk->category)->name ?: '-'),
                (string) (optional($risk->causeCategory)->name ?: '-'),
                (string) ($risk->risk_owner ?: '-'),
                (string) ($risk->status ?: '-'),
                (string) (optional($risk->inherentAnalysis)->inherent_risk_score ?: '-'),
                (string) (optional($risk->inherentAnalysis)->risk_level ?: '-'),
                (string) (optional($risk->residualAnalysis)->residual_risk_score ?: '-'),
                (string) (optional($risk->residualAnalysis)->risk_level ?: '-'),
                (string) $radarCode,
                (string) $risk->treatments->count(),
            ];
        }

        return [
            'title' => 'Risk Register',
            'headings' => [
                'Risk ID',
                'Year',
                'Risk Title',
                'Category',
                'Cause Category',
                'Risk Owner',
                'Status',
                'Inherent Risk',
                'Inherent Level',
                'Residual Risk',
                'Residual Level',
                'Radar Code',
                'Treatments',
            ],
            'rows' => $rows,
        ];
    }

    private function buildHeatmapSections($risks): array
    {
        $risks = collect($risks)->values();
        $codeBuckets = $this->buildHeatmapCodeBuckets($risks);

        $matrix = [];
        for ($probability = 1; $probability <= 5; $probability++) {
            for ($impact = 1; $impact <= 5; $impact++) {
                $matrix[$probability][$impact] = [
                    'score' => null,
                    'level' => '-',
                    'inherent_count' => 0,
                    'residual_count' => 0,
                ];
            }
        }

        foreach (RiskScoreMatrix::all() as $row) {
            $probability = (int) $row->probability_score;
            $impact = (int) $row->impact_score;
            if ($probability >= 1 && $probability <= 5 && $impact >= 1 && $impact <= 5) {
                $score = (int) $row->risk_score;
                $matrix[$probability][$impact]['score'] = $score;
                $matrix[$probability][$impact]['level'] = (string) (RiskScoreMatrix::getRiskLevel($score) ?: '-');
            }
        }

        $eventRows = [];

        foreach ($codeBuckets as $bucket) {
            if (!is_null($bucket['inherent_probability']) && !is_null($bucket['inherent_impact'])) {
                $matrix[$bucket['inherent_probability']][$bucket['inherent_impact']]['inherent_count']++;
            }

            if (!is_null($bucket['residual_probability']) && !is_null($bucket['residual_impact'])) {
                $matrix[$bucket['residual_probability']][$bucket['residual_impact']]['residual_count']++;
            }

            $eventRows[] = [
                'code' => (string) $bucket['code'],
                'category' => (string) $bucket['category'],
                'risk_event' => (string) $bucket['risk_event'],
                'risk_owner' => (string) $bucket['risk_owner'],
            ];
        }

        usort($eventRows, function ($a, $b) {
            $sortByCode = strcmp($this->recapSortKey($a['code']), $this->recapSortKey($b['code']));
            if ($sortByCode !== 0) {
                return $sortByCode;
            }

            return strcmp((string) $a['risk_event'], (string) $b['risk_event']);
        });

        $rows = [];
        for ($probability = 5; $probability >= 1; $probability--) {
            for ($impact = 1; $impact <= 5; $impact++) {
                $cell = $matrix[$probability][$impact];
                $rows[] = [
                    'Matrix',
                    (string) $probability,
                    (string) $impact,
                    (string) ($cell['score'] ?? '-'),
                    (string) $cell['level'],
                    (string) $cell['inherent_count'],
                    (string) $cell['residual_count'],
                    '',
                    '',
                    '',
                    '',
                ];
            }
        }

        $rows[] = array_fill(0, 11, '');

        if (empty($eventRows)) {
            $rows[] = ['Risk Event', '', '', '', '', '', '', '-', '-', 'No risk data available.', '-'];
        } else {
            foreach ($eventRows as $row) {
                $rows[] = [
                    'Risk Event',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    $row['code'],
                    $row['category'],
                    $row['risk_event'],
                    $row['risk_owner'],
                ];
            }
        }

        return [
            [
                'title' => 'Heatmap Dashboard',
                'type' => 'heatmap_dashboard',
                'headings' => [
                    'Section',
                    'Probability',
                    'Impact',
                    'Risk Score',
                    'Risk Level',
                    'Inherent Count',
                    'Residual Count',
                    'Code',
                    'Category',
                    'Risk Event',
                    'Risk Owner',
                ],
                'rows' => $rows,
                'matrix' => $matrix,
                'event_rows' => $eventRows,
            ],
        ];
    }

    private function buildHeatmapCodeBuckets($risks): array
    {
        $buckets = [];

        foreach ($risks as $risk) {
            $radarCode = $this->resolveHeatmapRadarCode($risk);
            if ($this->isExcludedHeatmapRadarCode($radarCode) || !$this->isRecapRadarCode($radarCode)) {
                continue;
            }

            if (!isset($buckets[$radarCode])) {
                $buckets[$radarCode] = [
                    'code' => $radarCode,
                    'category' => $this->resolveHeatCategory($risk, $radarCode),
                    'owners' => [],
                    'risk_n' => 0,
                    'inherent_risk_sum' => 0.0,
                    'residual_risk_sum' => 0.0,
                    'representative' => null,
                ];
            }

            $buckets[$radarCode]['risk_n']++;

            $owner = trim((string) ($risk->risk_owner ?: '-'));
            if ($owner !== '') {
                $buckets[$radarCode]['owners'][$owner] = true;
            }

            $inherent = $risk->inherentAnalysis;
            $inherentRiskScore = (float) (optional($inherent)->inherent_risk_score ?: 0);
            $buckets[$radarCode]['inherent_risk_sum'] += $inherentRiskScore > 0 ? $inherentRiskScore : 0.0;

            $residual = $risk->residualAnalysis;
            $residualRiskScore = (float) (optional($residual)->residual_risk_score ?: 0);
            $buckets[$radarCode]['residual_risk_sum'] += $residualRiskScore > 0 ? $residualRiskScore : 0.0;

            $currentRepresentative = $buckets[$radarCode]['representative'];
            if (is_null($currentRepresentative) || $this->compareHeatmapRiskPriority($risk, $currentRepresentative) < 0) {
                $buckets[$radarCode]['representative'] = $risk;
            }
        }

        $selected = [];
        foreach ($buckets as $bucket) {
            $representative = $bucket['representative'];
            if (is_null($representative)) {
                continue;
            }

            $owners = array_keys($bucket['owners']);
            sort($owners, SORT_NATURAL | SORT_FLAG_CASE);
            $riskOwner = !empty($owners) ? implode(' / ', $owners) : '-';

            $radarDescription = optional(optional($representative->residualAnalysis)->radarGroup)->description
                ?: (optional($representative->residualAnalysis)->radar_description ?: '');

            [$inherentProbability, $inherentImpact] = $this->resolveHeatmapCellFromAverageRisk(
                (float) $bucket['inherent_risk_sum'],
                (int) $bucket['risk_n']
            );
            [$residualProbability, $residualImpact] = $this->resolveHeatmapCellFromAverageRisk(
                (float) $bucket['residual_risk_sum'],
                (int) $bucket['risk_n']
            );

            $selected[] = [
                'code' => (string) $bucket['code'],
                'category' => (string) $bucket['category'],
                'risk_event' => $this->resolveHeatmapRiskEvent((string) $bucket['code'], (string) $radarDescription, $representative),
                'risk_owner' => $riskOwner,
                'inherent_probability' => $inherentProbability,
                'inherent_impact' => $inherentImpact,
                'residual_probability' => $residualProbability,
                'residual_impact' => $residualImpact,
            ];
        }

        usort($selected, function ($a, $b) {
            return strcmp($this->recapSortKey($a['code']), $this->recapSortKey($b['code']));
        });

        return $selected;
    }

    private function compareHeatmapRiskPriority(Risk $a, Risk $b): int
    {
        $aResidual = (int) (optional($a->residualAnalysis)->residual_risk_score ?: 0);
        $bResidual = (int) (optional($b->residualAnalysis)->residual_risk_score ?: 0);
        if ($aResidual !== $bResidual) {
            return $bResidual <=> $aResidual;
        }

        $aAdjustment = $this->resolveHeatmapAdjustmentValue($a);
        $bAdjustment = $this->resolveHeatmapAdjustmentValue($b);
        if ($aAdjustment !== $bAdjustment) {
            return $bAdjustment <=> $aAdjustment;
        }

        $aInherent = (int) (optional($a->inherentAnalysis)->inherent_risk_score ?: 0);
        $bInherent = (int) (optional($b->inherentAnalysis)->inherent_risk_score ?: 0);
        if ($aInherent !== $bInherent) {
            return $bInherent <=> $aInherent;
        }

        return ((int) $a->id) <=> ((int) $b->id);
    }

    private function resolveHeatmapAdjustmentValue(Risk $risk): float
    {
        $residual = $risk->residualAnalysis;
        $raw = optional($residual)->adjustment;
        if (is_null($raw) || $raw === '') {
            $raw = optional($residual)->risk_adjustment;
        }

        return is_numeric($raw) ? (float) $raw : 0.0;
    }

    private function resolveHeatmapRadarCode(Risk $risk): string
    {
        $residual = $risk->residualAnalysis;

        $radarCode = optional(optional($residual)->radarGroup)->code
            ?: (optional($residual)->radar_code ?: '');

        if ((!$radarCode || trim((string) $radarCode) === '') && !empty(optional($residual)->radar_description)) {
            if (preg_match('/\b([SOCF]\d{1,2})\b/i', (string) optional($residual)->radar_description, $matches) === 1) {
                $radarCode = strtoupper((string) $matches[1]);
            }
        }

        if (!$radarCode || trim((string) $radarCode) === '') {
            $radarCode = 'N/A';
        }

        return strtoupper(trim((string) $radarCode));
    }

    private function resolveHeatmapCellFromAverageRisk(float $riskSum, int $riskCount): array
    {
        if ($riskCount <= 0) {
            return [null, null];
        }

        $avgRisk = $riskSum / $riskCount;
        $score = (int) floor($avgRisk);

        if ($score < 1) {
            return [null, null];
        }

        if ($score > 25) {
            $score = 25;
        }

        $matrixByScore = $this->heatmapScoreCoordinateMap();
        if (!isset($matrixByScore[$score])) {
            return [null, null];
        }

        return [
            (int) $matrixByScore[$score]['probability'],
            (int) $matrixByScore[$score]['impact'],
        ];
    }

    private function heatmapScoreCoordinateMap(): array
    {
        static $map = null;

        if (!is_null($map)) {
            return $map;
        }

        $map = [];

        foreach (RiskScoreMatrix::all() as $row) {
            $score = (int) $row->risk_score;
            $probability = (int) $row->probability_score;
            $impact = (int) $row->impact_score;

            if ($score < 1 || $score > 25) {
                continue;
            }

            if (!isset($map[$score])) {
                $map[$score] = [
                    'probability' => $probability,
                    'impact' => $impact,
                ];
            }
        }

        return $map;
    }

    private function resolveHeatmapRiskEvent(string $code, string $radarDescription, Risk $risk): string
    {
        $description = trim($radarDescription);
        if ($description !== '' && !$this->isExcludedHeatmapRadarCode($description)) {
            if (preg_match('/^[SOCF]\d{1,2}\s*:\s*(.+)$/i', $description, $matches) === 1) {
                return trim((string) $matches[1]);
            }

            return $description;
        }

        return (string) ($risk->risk_description ?: ($risk->risk_title ?: '-'));
    }

    private function isExcludedHeatmapRadarCode(?string $code): bool
    {
        if (is_null($code)) {
            return true;
        }

        $normalized = strtoupper(trim($code));

        return in_array($normalized, ['N/A', 'NA', 'NONE', '-'], true);
    }

    private function buildMonitoringSections($risks): array
    {
        $summary = $this->buildMonitoringSummaryFromCollection(collect($risks)->values());

        $overviewRows = [
            ['Total Risks', (string) ($summary['total_risks'] ?? 0)],
            ['Open Risks', (string) ($summary['open_count'] ?? 0)],
            ['Monitoring Risks', (string) ($summary['monitoring_count'] ?? 0)],
            ['Closed Risks', (string) ($summary['closed_count'] ?? 0)],
            ['Avg Inherent Score', (string) number_format((float) ($summary['average_scores']['inherent'] ?? 0), 2, '.', '')],
            ['Avg Residual Score', (string) number_format((float) ($summary['average_scores']['residual'] ?? 0), 2, '.', '')],
            ['Avg Overall Score', (string) number_format((float) ($summary['average_scores']['overall'] ?? 0), 2, '.', '')],
            ['Global Variance (Inherent - Residual)', (string) number_format((float) ($summary['variance']['global'] ?? 0), 2, '.', '')],
            ['Generated At', (string) ($summary['generated_at'] ?? '-')],
        ];

        $trendRows = [];
        foreach (($summary['trend'] ?? []) as $row) {
            $trendRows[] = [
                (string) ($row['label'] ?? ($row['month'] ?? '-')),
                (string) number_format((float) ($row['inherent'] ?? 0), 2, '.', ''),
                (string) number_format((float) ($row['residual'] ?? 0), 2, '.', ''),
                (string) number_format((float) ($row['overall'] ?? 0), 2, '.', ''),
            ];
        }

        $performanceRows = [
            ['Mitigation - Treatment Count', (string) ($summary['mitigation']['treatment_count'] ?? 0)],
            ['Mitigation - Covered Risk Count', (string) ($summary['mitigation']['covered_risk_count'] ?? 0)],
            ['Mitigation - Risk Coverage Percent', (string) number_format((float) ($summary['mitigation']['risk_coverage_percent'] ?? 0), 2, '.', '') . '%'],
            ['Mitigation - Avg Effectiveness Score', (string) number_format((float) ($summary['mitigation']['average_effectiveness_score'] ?? 0), 2, '.', '')],
            ['Mitigation - Effectiveness Percent', (string) number_format((float) ($summary['mitigation']['effectiveness_percent'] ?? 0), 2, '.', '') . '%'],
            ['SLA - Target Percent', (string) number_format((float) ($summary['sla']['target_percent'] ?? 95), 2, '.', '') . '%'],
            ['SLA - Actual Percent', (string) number_format((float) ($summary['sla']['actual_percent'] ?? 0), 2, '.', '') . '%'],
            ['SLA - Due Count', (string) ($summary['sla']['due_count'] ?? 0)],
            ['SLA - Compliant Count', (string) ($summary['sla']['compliant_count'] ?? 0)],
            ['SLA - Overdue Count', (string) ($summary['sla']['overdue_count'] ?? 0)],
        ];

        $breakdownRows = [];
        $appendBreakdownRows = function (string $group, array $rows) use (&$breakdownRows): void {
            if (empty($rows)) {
                $breakdownRows[] = [$group, '-', '0'];

                return;
            }

            foreach ($rows as $row) {
                $breakdownRows[] = [
                    $group,
                    (string) ($row['label'] ?? '-'),
                    (string) ($row['count'] ?? 0),
                ];
            }
        };

        $appendBreakdownRows('Status', (array) ($summary['summary_by_status'] ?? []));
        $appendBreakdownRows('Category', (array) ($summary['summary_by_category'] ?? []));
        $appendBreakdownRows('Owner', (array) ($summary['summary_by_owner'] ?? []));

        $varianceRows = [];
        foreach (($summary['variance']['rows'] ?? []) as $row) {
            $varianceRows[] = [
                (string) ($row['category'] ?? '-'),
                (string) ($row['risk_count'] ?? 0),
                (string) number_format((float) ($row['avg_inherent'] ?? 0), 2, '.', ''),
                (string) number_format((float) ($row['avg_residual'] ?? 0), 2, '.', ''),
                (string) number_format((float) ($row['variance'] ?? 0), 2, '.', ''),
            ];
        }

        return [
            [
                'title' => 'Monitoring Overview',
                'type' => 'monitoring_overview',
                'headings' => ['Metric', 'Value'],
                'rows' => $overviewRows,
                'kpi_cards' => [
                    ['label' => 'Total Risks', 'value' => (string) ($summary['total_risks'] ?? 0), 'tone' => 'blue'],
                    ['label' => 'Open', 'value' => (string) ($summary['open_count'] ?? 0), 'tone' => 'red'],
                    ['label' => 'Monitoring', 'value' => (string) ($summary['monitoring_count'] ?? 0), 'tone' => 'amber'],
                    ['label' => 'Closed', 'value' => (string) ($summary['closed_count'] ?? 0), 'tone' => 'green'],
                ],
            ],
            [
                'title' => 'Monitoring Trend Scores',
                'headings' => ['Month', 'Inherent', 'Residual', 'Overall'],
                'rows' => $trendRows,
            ],
            [
                'title' => 'Monitoring Mitigation & SLA',
                'headings' => ['Indicator', 'Value'],
                'rows' => $performanceRows,
            ],
            [
                'title' => 'Monitoring Breakdown',
                'headings' => ['Group', 'Label', 'Count'],
                'rows' => $breakdownRows,
            ],
            [
                'title' => 'Monitoring Variance Analysis',
                'headings' => ['Category', 'Risk Count', 'Avg Inherent', 'Avg Residual', 'Variance'],
                'rows' => $varianceRows,
            ],
        ];
    }

    private function buildMonitoringSummaryFromCollection($risks): array
    {
        $risks = collect($risks)->values();
        $totalRisks = (int) $risks->count();

        $statusSummary = $this->buildGroupedCountRows($risks->groupBy(function ($risk) {
            $status = trim((string) ($risk->status ?? 'Unknown'));

            return $status !== '' ? $status : 'Unknown';
        }));

        $categorySummary = $this->buildGroupedCountRows($risks->groupBy(function ($risk) {
            $category = trim((string) (optional($risk->category)->name ?? '-'));

            return $category !== '' ? $category : '-';
        }));

        $ownerSummary = $this->buildGroupedCountRows($risks->groupBy(function ($risk) {
            $owner = trim((string) ($risk->risk_owner ?? '-'));

            return $owner !== '' ? $owner : '-';
        }));

        $statusSummaryCollection = collect($statusSummary)->keyBy('label');
        $openCount = (int) ($statusSummaryCollection->get('Open')['count'] ?? 0);
        $closedCount = (int) ($statusSummaryCollection->get('Closed')['count'] ?? 0);
        $monitoringCount = (int) ($statusSummaryCollection->get('Monitoring')['count'] ?? 0);

        $inherentAverage = $this->averageFromCollection($risks, function ($risk) {
            return optional($risk->inherentAnalysis)->inherent_risk_score;
        });
        $residualAverage = $this->averageFromCollection($risks, function ($risk) {
            return optional($risk->residualAnalysis)->residual_risk_score;
        });
        $overallAverage = $this->averageFromCollection($risks, function ($risk) {
            $residual = optional($risk->residualAnalysis)->residual_risk_score;
            if (is_numeric($residual)) {
                return (float) $residual;
            }

            $inherent = optional($risk->inherentAnalysis)->inherent_risk_score;

            return is_numeric($inherent) ? (float) $inherent : null;
        });

        $trendRows = $this->buildTrendRows($risks);

        $treatments = $risks->flatMap(function ($risk) {
            return $risk->treatments->map(function ($treatment) use ($risk) {
                return [
                    'risk_id' => (int) $risk->id,
                    'deadline' => $treatment->deadline,
                    'effectiveness_score' => $treatment->effectiveness_score,
                ];
            });
        })->values();

        $treatmentCount = (int) $treatments->count();
        $effectivenessAverageScore = $this->averageFromCollection($treatments, function ($row) {
            return $row['effectiveness_score'] ?? null;
        });
        $mitigationEffectivenessPercent = round($effectivenessAverageScore * 100, 2);
        $coveredRiskCount = (int) $treatments->pluck('risk_id')->unique()->count();
        $riskCoveragePercent = $totalRisks > 0
            ? round(($coveredRiskCount / $totalRisks) * 100, 2)
            : 0.0;

        $today = Carbon::today();
        $dueTreatments = $treatments->filter(function ($row) {
            return !empty($row['deadline']);
        });
        $dueCount = (int) $dueTreatments->count();
        $compliantCount = (int) $dueTreatments->filter(function ($row) use ($today) {
            try {
                return Carbon::parse($row['deadline'])->startOfDay()->greaterThanOrEqualTo($today);
            } catch (\Throwable $e) {
                return false;
            }
        })->count();
        $overdueCount = max(0, $dueCount - $compliantCount);

        $slaTargetPercent = 95.0;
        $slaActualPercent = $dueCount > 0
            ? round(($compliantCount / $dueCount) * 100, 2)
            : 100.0;

        $varianceRows = $this->buildVarianceRows($risks);
        $globalVariance = round($inherentAverage - $residualAverage, 2);

        return [
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'total_risks' => $totalRisks,
            'open_count' => $openCount,
            'closed_count' => $closedCount,
            'monitoring_count' => $monitoringCount,
            'summary_by_status' => $statusSummary,
            'summary_by_category' => $categorySummary,
            'summary_by_owner' => $ownerSummary,
            'average_scores' => [
                'inherent' => $inherentAverage,
                'residual' => $residualAverage,
                'overall' => $overallAverage,
            ],
            'trend' => $trendRows,
            'mitigation' => [
                'treatment_count' => $treatmentCount,
                'covered_risk_count' => $coveredRiskCount,
                'risk_coverage_percent' => $riskCoveragePercent,
                'average_effectiveness_score' => $effectivenessAverageScore,
                'effectiveness_percent' => $mitigationEffectivenessPercent,
            ],
            'sla' => [
                'target_percent' => $slaTargetPercent,
                'actual_percent' => $slaActualPercent,
                'due_count' => $dueCount,
                'compliant_count' => $compliantCount,
                'overdue_count' => $overdueCount,
            ],
            'variance' => [
                'global' => $globalVariance,
                'rows' => $varianceRows,
            ],
        ];
    }

    private function buildGroupedCountRows($grouped): array
    {
        return $grouped
            ->map(function ($items, $label) {
                return [
                    'label' => (string) $label,
                    'count' => (int) $items->count(),
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->all();
    }

    private function averageFromCollection($collection, callable $resolver): float
    {
        $numbers = collect($collection)
            ->map($resolver)
            ->filter(function ($value) {
                return is_numeric($value);
            })
            ->map(function ($value) {
                return (float) $value;
            })
            ->values();

        if ($numbers->isEmpty()) {
            return 0.0;
        }

        return round((float) $numbers->avg(), 2);
    }

    private function buildTrendRows($risks): array
    {
        $trendMap = [];
        for ($i = 2; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $key = $month->format('Y-m');
            $trendMap[$key] = [
                'month' => $key,
                'label' => $month->format('M Y'),
                'inherent_scores' => [],
                'residual_scores' => [],
                'overall_scores' => [],
            ];
        }

        foreach ($risks as $risk) {
            $createdAt = optional($risk->created_at);
            if (is_null($createdAt)) {
                continue;
            }

            $key = $createdAt->format('Y-m');
            if (!isset($trendMap[$key])) {
                continue;
            }

            $inherent = optional($risk->inherentAnalysis)->inherent_risk_score;
            $residual = optional($risk->residualAnalysis)->residual_risk_score;
            $overall = is_numeric($residual)
                ? (float) $residual
                : (is_numeric($inherent) ? (float) $inherent : null);

            if (is_numeric($inherent)) {
                $trendMap[$key]['inherent_scores'][] = (float) $inherent;
            }

            if (is_numeric($residual)) {
                $trendMap[$key]['residual_scores'][] = (float) $residual;
            }

            if (is_numeric($overall)) {
                $trendMap[$key]['overall_scores'][] = (float) $overall;
            }
        }

        return collect($trendMap)->map(function ($row) {
            $inherentAvg = !empty($row['inherent_scores'])
                ? round(array_sum($row['inherent_scores']) / count($row['inherent_scores']), 2)
                : 0.0;
            $residualAvg = !empty($row['residual_scores'])
                ? round(array_sum($row['residual_scores']) / count($row['residual_scores']), 2)
                : 0.0;
            $overallAvg = !empty($row['overall_scores'])
                ? round(array_sum($row['overall_scores']) / count($row['overall_scores']), 2)
                : 0.0;

            return [
                'month' => $row['month'],
                'label' => $row['label'],
                'inherent' => $inherentAvg,
                'residual' => $residualAvg,
                'overall' => $overallAvg,
            ];
        })->values()->all();
    }

    private function buildVarianceRows($risks): array
    {
        return $risks->groupBy(function ($risk) {
            $category = trim((string) (optional($risk->category)->name ?? '-'));

            return $category !== '' ? $category : '-';
        })->map(function ($items, $category) {
            $inherent = collect($items)
                ->map(function ($risk) {
                    return optional($risk->inherentAnalysis)->inherent_risk_score;
                })
                ->filter(function ($value) {
                    return is_numeric($value);
                })
                ->map(function ($value) {
                    return (float) $value;
                })
                ->values();

            $residual = collect($items)
                ->map(function ($risk) {
                    return optional($risk->residualAnalysis)->residual_risk_score;
                })
                ->filter(function ($value) {
                    return is_numeric($value);
                })
                ->map(function ($value) {
                    return (float) $value;
                })
                ->values();

            $inherentAvg = $inherent->isEmpty() ? 0.0 : (float) $inherent->avg();
            $residualAvg = $residual->isEmpty() ? 0.0 : (float) $residual->avg();
            $variance = $inherentAvg - $residualAvg;

            return [
                'category' => (string) $category,
                'risk_count' => (int) $items->count(),
                'avg_inherent' => round($inherentAvg, 2),
                'avg_residual' => round($residualAvg, 2),
                'variance' => round($variance, 2),
            ];
        })
            ->sortByDesc(function ($row) {
                return abs((float) $row['variance']);
            })
            ->values()
            ->all();
    }

    private function buildBoardSections($risks): array
    {
        $risks = collect($risks)->values();
        $summary = $this->buildMonitoringSummaryFromCollection($risks);

        $statusRows = (array) ($summary['summary_by_status'] ?? []);
        $categoryRows = collect((array) ($summary['summary_by_category'] ?? []))
            ->sortByDesc('count')
            ->take(6)
            ->values()
            ->all();
        $ownerRows = collect((array) ($summary['summary_by_owner'] ?? []))
            ->sortByDesc('count')
            ->take(5)
            ->values()
            ->all();
        $trendRows = array_slice((array) ($summary['trend'] ?? []), -6);

        $criticalRisks = $risks
            ->map(function ($risk) {
                $inherentScore = (float) (optional($risk->inherentAnalysis)->inherent_risk_score ?: 0);
                $residualScore = (float) (optional($risk->residualAnalysis)->residual_risk_score ?: 0);
                $gap = round($inherentScore - $residualScore, 2);
                $level = (string) (optional($risk->residualAnalysis)->risk_level ?: (RiskScoreMatrix::getRiskLevel((int) $residualScore) ?: '-'));
                $treatmentCount = (int) $risk->treatments->count();
                $nearestDeadline = $risk->treatments
                    ->filter(function ($treatment) {
                        return !empty($treatment->deadline);
                    })
                    ->sortBy('deadline')
                    ->pluck('deadline')
                    ->first();

                return [
                    'risk_id' => (string) ($risk->risk_id ?: '-'),
                    'risk_title' => (string) ($risk->risk_title ?: '-'),
                    'owner' => (string) ($risk->risk_owner ?: '-'),
                    'status' => (string) ($risk->status ?: '-'),
                    'inherent_score' => round($inherentScore, 2),
                    'residual_score' => round($residualScore, 2),
                    'residual_level' => $level,
                    'gap' => $gap,
                    'treatments' => $treatmentCount,
                    'nearest_deadline' => $nearestDeadline ? Carbon::parse($nearestDeadline)->format('Y-m-d') : '-',
                ];
            })
            ->sort(function ($a, $b) {
                $residualCompare = ((float) ($b['residual_score'] ?? 0)) <=> ((float) ($a['residual_score'] ?? 0));
                if ($residualCompare !== 0) {
                    return $residualCompare;
                }

                $inherentCompare = ((float) ($b['inherent_score'] ?? 0)) <=> ((float) ($a['inherent_score'] ?? 0));
                if ($inherentCompare !== 0) {
                    return $inherentCompare;
                }

                return ((int) ($b['treatments'] ?? 0)) <=> ((int) ($a['treatments'] ?? 0));
            })
            ->take(10)
            ->values();

        $highResidualCount = (int) $risks->filter(function ($risk) {
            $score = (float) (optional($risk->residualAnalysis)->residual_risk_score ?: 0);

            return $score >= 15;
        })->count();

        $topCategoryLabel = !empty($categoryRows) ? (string) $categoryRows[0]['label'] : '-';
        $topOwnerLabel = !empty($ownerRows) ? (string) $ownerRows[0]['label'] : '-';
        $topOwnerCount = !empty($ownerRows) ? (int) $ownerRows[0]['count'] : 0;

        $overviewRows = [
            ['Total Active Risks', (string) ($summary['total_risks'] ?? 0)],
            ['High Residual Exposure (score >= 15)', (string) $highResidualCount],
            ['Top Exposure Category', $topCategoryLabel],
            ['Top Exposure Owner', $topOwnerLabel . ($topOwnerCount > 0 ? ' (' . $topOwnerCount . ')' : '')],
            ['Average Residual Score', number_format((float) ($summary['average_scores']['residual'] ?? 0), 2, '.', '')],
            ['Mitigation Coverage', number_format((float) ($summary['mitigation']['risk_coverage_percent'] ?? 0), 2, '.', '') . '%'],
            ['SLA Compliance', number_format((float) ($summary['sla']['actual_percent'] ?? 0), 2, '.', '') . '%'],
            ['Global Risk Variance', number_format((float) ($summary['variance']['global'] ?? 0), 2, '.', '')],
        ];

        $statusChartRows = collect($statusRows)->map(function ($row) {
            return [
                (string) ($row['label'] ?? '-'),
                (string) ($row['count'] ?? 0),
            ];
        })->values()->all();

        $categoryChartRows = collect($categoryRows)->map(function ($row) {
            return [
                (string) ($row['label'] ?? '-'),
                (string) ($row['count'] ?? 0),
            ];
        })->values()->all();

        $trendChartRows = collect($trendRows)->map(function ($row) {
            return [
                (string) ($row['label'] ?? ($row['month'] ?? '-')),
                number_format((float) ($row['inherent'] ?? 0), 2, '.', ''),
                number_format((float) ($row['residual'] ?? 0), 2, '.', ''),
            ];
        })->values()->all();

        $topRiskRows = $criticalRisks->map(function ($row) {
            return [
                (string) $row['risk_id'],
                (string) $row['risk_title'],
                (string) $row['owner'],
                (string) $row['status'],
                number_format((float) $row['inherent_score'], 2, '.', ''),
                number_format((float) $row['residual_score'], 2, '.', ''),
                (string) $row['residual_level'],
                number_format((float) $row['gap'], 2, '.', ''),
                (string) $row['treatments'],
                (string) $row['nearest_deadline'],
            ];
        })->values()->all();

        $recommendationRows = $this->buildBoardRecommendationRows($summary, $criticalRisks->all());

        return [
            [
                'title' => 'Board Executive Snapshot',
                'type' => 'board_executive_summary',
                'headings' => ['Metric', 'Value'],
                'rows' => $overviewRows,
                'kpi_cards' => [
                    ['label' => 'Total Risks', 'value' => (string) ($summary['total_risks'] ?? 0), 'tone' => 'blue'],
                    ['label' => 'High Residual', 'value' => (string) $highResidualCount, 'tone' => 'red'],
                    ['label' => 'Mitigation Coverage', 'value' => number_format((float) ($summary['mitigation']['risk_coverage_percent'] ?? 0), 1, '.', '') . '%', 'tone' => 'amber'],
                    ['label' => 'SLA Compliance', 'value' => number_format((float) ($summary['sla']['actual_percent'] ?? 0), 1, '.', '') . '%', 'tone' => 'green'],
                ],
                'insight' => 'Snapshot ini merangkum level eksposur dan efektivitas pengendalian sebagai baseline pengambilan keputusan Board.',
            ],
            [
                'title' => 'Board Chart - Status Distribution',
                'type' => 'board_status_chart',
                'headings' => ['Status', 'Risk Count'],
                'rows' => $statusChartRows,
                'chart' => [
                    'labels' => collect($statusRows)->pluck('label')->map(function ($value) {
                        return (string) $value;
                    })->values()->all(),
                    'values' => collect($statusRows)->pluck('count')->map(function ($value) {
                        return (int) $value;
                    })->values()->all(),
                ],
                'insight' => 'Distribusi status menunjukkan porsi risiko yang masih membutuhkan aksi versus risiko yang sudah ditutup.',
            ],
            [
                'title' => 'Board Chart - Category Exposure',
                'type' => 'board_category_chart',
                'headings' => ['Category', 'Risk Count'],
                'rows' => $categoryChartRows,
                'chart' => [
                    'labels' => collect($categoryRows)->pluck('label')->map(function ($value) {
                        return (string) $value;
                    })->values()->all(),
                    'values' => collect($categoryRows)->pluck('count')->map(function ($value) {
                        return (int) $value;
                    })->values()->all(),
                ],
                'insight' => 'Kategori dengan eksposur tertinggi menjadi prioritas untuk penguatan kontrol dan alokasi resource lintas fungsi.',
            ],
            [
                'title' => 'Board Chart - Risk Score Trend',
                'type' => 'board_trend_chart',
                'headings' => ['Period', 'Avg Inherent', 'Avg Residual'],
                'rows' => $trendChartRows,
                'chart' => [
                    'labels' => collect($trendRows)->pluck('label')->map(function ($value) {
                        return (string) $value;
                    })->values()->all(),
                    'inherent_values' => collect($trendRows)->pluck('inherent')->map(function ($value) {
                        return (float) $value;
                    })->values()->all(),
                    'residual_values' => collect($trendRows)->pluck('residual')->map(function ($value) {
                        return (float) $value;
                    })->values()->all(),
                ],
                'insight' => 'Tren skor inherent dan residual menggambarkan kualitas mitigasi aktual dan arah perubahan profil risiko.',
            ],
            [
                'title' => 'Board Priority Watchlist (Top Risks)',
                'type' => 'board_priority_table',
                'headings' => [
                    'Risk ID',
                    'Risk Event',
                    'Owner',
                    'Status',
                    'Inherent',
                    'Residual',
                    'Residual Level',
                    'Gap',
                    'Treatments',
                    'Nearest Deadline',
                ],
                'rows' => $topRiskRows,
                'insight' => 'Watchlist ini fokus pada risiko dengan residual tertinggi untuk memastikan eskalasi dan eksekusi aksi berjalan tepat waktu.',
            ],
            [
                'title' => 'Board Management Narrative',
                'type' => 'board_narrative',
                'headings' => ['Theme', 'Recommendation'],
                'rows' => $recommendationRows,
            ],
        ];
    }

    private function buildBoardRecommendationRows(array $summary, array $criticalRisks): array
    {
        $rows = [];

        $slaActual = (float) ($summary['sla']['actual_percent'] ?? 0);
        $slaTarget = (float) ($summary['sla']['target_percent'] ?? 95);
        if ($slaActual < $slaTarget) {
            $rows[] = [
                'SLA at Risk',
                'Aktual SLA ' . number_format($slaActual, 2, '.', '') . '% masih di bawah target ' . number_format($slaTarget, 2, '.', '') . '%. Board perlu mendorong corrective action untuk treatment yang mendekati/melewati deadline.',
            ];
        } else {
            $rows[] = [
                'SLA Stable',
                'Aktual SLA ' . number_format($slaActual, 2, '.', '') . '% berada di atas target. Fokus diarahkan pada sustainment kontrol dan validasi efektivitas mitigasi.',
            ];
        }

        $mitigationCoverage = (float) ($summary['mitigation']['risk_coverage_percent'] ?? 0);
        if ($mitigationCoverage < 80) {
            $rows[] = [
                'Coverage Gap',
                'Coverage mitigasi baru ' . number_format($mitigationCoverage, 2, '.', '') . '%. Rekomendasi: wajibkan action plan pada risiko dengan residual score tinggi.',
            ];
        } else {
            $rows[] = [
                'Coverage Progress',
                'Coverage mitigasi mencapai ' . number_format($mitigationCoverage, 2, '.', '') . '%. Rekomendasi: lanjutkan quality assurance untuk efektivitas treatment.',
            ];
        }

        $highestResidual = collect($criticalRisks)->max(function ($risk) {
            return (float) ($risk['residual_score'] ?? 0);
        });
        $highestResidual = is_null($highestResidual) ? 0.0 : (float) $highestResidual;

        $rows[] = [
            'Residual Exposure',
            'Residual score tertinggi saat ini ' . number_format($highestResidual, 2, '.', '') . '. Prioritaskan review mingguan pada risk owner dengan eksposur tertinggi.',
        ];

        $variance = (float) ($summary['variance']['global'] ?? 0);
        if ($variance <= 0) {
            $rows[] = [
                'Risk Reduction Signal',
                'Perbedaan inherent-residual relatif rendah. Evaluasi ulang scoring dan evidence mitigasi agar mencerminkan risk reduction yang terukur.',
            ];
        } else {
            $rows[] = [
                'Risk Reduction Signal',
                'Global variance ' . number_format($variance, 2, '.', '') . ' mengindikasikan mitigasi menurunkan risiko. Pertahankan disiplin monitoring pada top exposure.',
            ];
        }

        return $rows;
    }

    private function buildRecapSections($risks): array
    {
        $radarGroups = RadarGroup::with('quantProfile')
            ->get()
            ->filter(function ($group) {
                return $this->isRecapRadarCode($group->code);
            })
            ->sortBy(function ($group) {
                return $this->recapSortKey($group->code);
            })
            ->values();

        $risks = collect($risks)->values();
        $risksByCode = [];

        foreach ($risks as $risk) {
            $code = $this->resolveRecapRadarCode($risk);
            if (!$this->isRecapRadarCode($code)) {
                continue;
            }

            if (!isset($risksByCode[$code])) {
                $risksByCode[$code] = collect();
            }

            $risksByCode[$code]->push($risk);
        }

        $summaryRows = [];
        $detailRows = [];
        $quantRows = [];
        $quantTotals = [
            'Strategic' => ['inherent_loss' => 0, 'residual_loss' => 0],
            'Operational' => ['inherent_loss' => 0, 'residual_loss' => 0],
            'Compliance' => ['inherent_loss' => 0, 'residual_loss' => 0],
            'Financial' => ['inherent_loss' => 0, 'residual_loss' => 0],
        ];

        foreach ($radarGroups as $radarGroup) {
            $code = strtoupper(trim((string) $radarGroup->code));
            $category = $this->recapCategoryFromCode($code);
            $bucket = $risksByCode[$code] ?? collect();
            $metrics = $this->calculateRecapMetricsFromBucket($bucket);

            $n = (int) $metrics['n'];
            $inhProbSum = (int) $metrics['inh_prob_sum'];
            $inhImpSum = (int) $metrics['inh_imp_sum'];
            $inhRiskSum = (int) $metrics['inh_risk_sum'];
            $resProbSum = (int) $metrics['res_prob_sum'];
            $resImpSum = (int) $metrics['res_imp_sum'];
            $resRiskSum = (int) $metrics['res_risk_sum'];
            $resAdjSum = (float) $metrics['res_adj_sum'];

            if ($n === 0) {
                continue;
            }

            $summaryRows[] = [
                (string) $category,
                (string) $code,
                (string) $n,
                (string) $inhProbSum,
                (string) $inhImpSum,
                (string) $inhRiskSum,
                (string) $resProbSum,
                (string) $resImpSum,
                (string) $resRiskSum,
                (string) round($resAdjSum, 2),
            ];

            $avgInhRisk = round($inhRiskSum / $n, 2);
            $avgResRisk = round($resRiskSum / $n, 2);

            // Keep recap quantitative values aligned with XLS: fixed baseline per risk code.
            $baseInherentImpact = (int) (optional($radarGroup->quantProfile)->potential_loss_inherent ?: 0);
            $residualLossFactor = (float) (optional($radarGroup->quantProfile)->residual_loss_factor ?: 0.3);
            $potentialLossInherent = (int) round($baseInherentImpact);
            $potentialLossResidual = (int) round($baseInherentImpact * $residualLossFactor);

            $detailRows[] = [
                (string) $category,
                (string) $code,
                (string) $n,
                (string) round($inhProbSum / $n, 2),
                (string) round($inhImpSum / $n, 2),
                (string) $avgInhRisk,
                (string) (RiskScoreMatrix::getRiskLevel($avgInhRisk) ?: '-'),
                (string) round($resProbSum / $n, 2),
                (string) round($resImpSum / $n, 2),
                (string) round($avgResRisk, 2),
                (string) round($resAdjSum / $n, 2),
                (string) (RiskScoreMatrix::getRiskLevel($avgResRisk) ?: '-'),
                (string) $potentialLossInherent,
                (string) $potentialLossResidual,
            ];

            $quantRows[] = [
                (string) $category,
                (string) $code,
                (string) $n,
                (string) $baseInherentImpact,
                '100%',
                (string) $potentialLossInherent,
                (string) $baseInherentImpact,
                (string) round($residualLossFactor * 100, 2) . '%',
                (string) $potentialLossResidual,
            ];

            if (!isset($quantTotals[$category])) {
                $quantTotals[$category] = ['inherent_loss' => 0, 'residual_loss' => 0];
            }

            $quantTotals[$category]['inherent_loss'] += $potentialLossInherent;
            $quantTotals[$category]['residual_loss'] += $potentialLossResidual;
        }

        $quantTotalRows = [];
        foreach ($quantTotals as $category => $totals) {
            $quantTotalRows[] = [
                (string) $category,
                (string) $totals['inherent_loss'],
                (string) $totals['residual_loss'],
            ];
        }

        return [
            [
                'title' => 'Recap Summary',
                'headings' => [
                    'Category',
                    'Code',
                    'Risk Count',
                    'Inh Prob Sum',
                    'Inh Impact Sum',
                    'Inh Risk Sum',
                    'Res Prob Sum',
                    'Res Impact Sum',
                    'Res Risk Sum',
                    'Res Adj Sum',
                ],
                'rows' => $summaryRows,
            ],
            [
                'title' => 'Recap Detail',
                'headings' => [
                    'Category',
                    'Code',
                    'Risk Count',
                    'Inh Prob Avg',
                    'Inh Impact Avg',
                    'Inh Risk Avg',
                    'Inh Level',
                    'Res Prob Avg',
                    'Res Impact Avg',
                    'Res Risk Avg',
                    'Res Adj Avg',
                    'Res Level',
                    'Potential Loss Inh',
                    'Potential Loss Res',
                ],
                'rows' => $detailRows,
            ],
            [
                'title' => 'Quantitative Result by Risk Code',
                'headings' => [
                    'Category',
                    'Code',
                    'Risk Count',
                    'Inherent Impact (Rp)',
                    'Inherent Probability',
                    'Inherent Loss (Rp)',
                    'Residual Impact (Rp)',
                    'Residual Probability',
                    'Residual Loss (Rp)',
                ],
                'rows' => $quantRows,
            ],
            [
                'title' => 'Quantitative Totals by Category',
                'headings' => [
                    'Category',
                    'Inherent Loss (Rp)',
                    'Residual Loss (Rp)',
                ],
                'rows' => $quantTotalRows,
            ],
        ];
    }

    /**
     * Translate recap formulas like:
     * BT + DI + QA + CR + PM + OM + IF + CS + RI + BS + HC + CO
     * with an OTHER fallback for legacy/unmapped entries.
     */
    private function calculateRecapMetricsFromBucket($bucket): array
    {
        $perMenu = $this->initRecapMenuMetrics();

        foreach ($bucket as $risk) {
            $menuCode = $this->resolveRecapMenuCodeFromRisk($risk);
            if (!isset($perMenu[$menuCode])) {
                $menuCode = 'OTHER';
            }

            $inherent = $risk->inherentAnalysis;
            $residual = $risk->residualAnalysis;

            $resAdjRaw = optional($residual)->adjustment;
            if (is_null($resAdjRaw)) {
                $resAdjRaw = optional($residual)->risk_adjustment;
            }

            $perMenu[$menuCode]['n']++;
            $perMenu[$menuCode]['inh_prob_sum'] += (int) (optional($inherent)->probability_score ?: 0);
            $perMenu[$menuCode]['inh_imp_sum'] += (int) (optional($inherent)->impact_score ?: 0);
            $perMenu[$menuCode]['inh_risk_sum'] += (int) (optional($inherent)->inherent_risk_score ?: 0);
            $perMenu[$menuCode]['res_prob_sum'] += (int) (optional($residual)->probability_score ?: 0);
            $perMenu[$menuCode]['res_imp_sum'] += (int) (optional($residual)->impact_score ?: 0);
            $perMenu[$menuCode]['res_risk_sum'] += (int) (optional($residual)->residual_risk_score ?: 0);
            $perMenu[$menuCode]['res_adj_sum'] += (float) ($resAdjRaw ?: 0);
        }

        $totals = [
            'n' => 0,
            'inh_prob_sum' => 0,
            'inh_imp_sum' => 0,
            'inh_risk_sum' => 0,
            'res_prob_sum' => 0,
            'res_imp_sum' => 0,
            'res_risk_sum' => 0,
            'res_adj_sum' => 0.0,
        ];

        foreach ($perMenu as $menuMetrics) {
            $totals['n'] += (int) $menuMetrics['n'];
            $totals['inh_prob_sum'] += (int) $menuMetrics['inh_prob_sum'];
            $totals['inh_imp_sum'] += (int) $menuMetrics['inh_imp_sum'];
            $totals['inh_risk_sum'] += (int) $menuMetrics['inh_risk_sum'];
            $totals['res_prob_sum'] += (int) $menuMetrics['res_prob_sum'];
            $totals['res_imp_sum'] += (int) $menuMetrics['res_imp_sum'];
            $totals['res_risk_sum'] += (int) $menuMetrics['res_risk_sum'];
            $totals['res_adj_sum'] += (float) $menuMetrics['res_adj_sum'];
        }

        return $totals;
    }

    private function initRecapMenuMetrics(): array
    {
        $metrics = [];

        foreach (array_merge($this->recapFormulaMenuCodes(), ['OTHER']) as $code) {
            $metrics[$code] = [
                'n' => 0,
                'inh_prob_sum' => 0,
                'inh_imp_sum' => 0,
                'inh_risk_sum' => 0,
                'res_prob_sum' => 0,
                'res_imp_sum' => 0,
                'res_risk_sum' => 0,
                'res_adj_sum' => 0.0,
            ];
        }

        return $metrics;
    }

    private function recapFormulaMenuCodes(): array
    {
        return ['BT', 'DI', 'QA', 'CR', 'PM', 'OM', 'IF', 'CS', 'RI', 'BS', 'HC', 'CO'];
    }

    private function resolveRecapMenuCodeFromRisk(Risk $risk): string
    {
        $menuCodes = $this->recapFormulaMenuCodes();

        $value = strtoupper(trim((string) ($risk->risk_owner ?: '')));
        if ($value === '') {
            return 'OTHER';
        }

        foreach ($menuCodes as $menuCode) {
            if (
                $value === $menuCode
                || strpos($value, $menuCode . ' ') === 0
                || strpos($value, $menuCode . '-') === 0
                || preg_match('/\b' . preg_quote($menuCode, '/') . '\b/', $value) === 1
            ) {
                return $menuCode;
            }
        }

        return 'OTHER';
    }

    private function resolveHeatCategory(Risk $risk, string $code): string
    {
        $prefix = strtoupper(substr((string) $code, 0, 1));
        $map = [
            'S' => 'Strategic',
            'O' => 'Operation',
            'C' => 'Compliance',
            'F' => 'Finance',
        ];

        if (isset($map[$prefix])) {
            return $map[$prefix];
        }

        return optional($risk->category)->name ?: '-';
    }

    private function resolveRecapRadarCode(Risk $risk): string
    {
        $code = optional(optional($risk->residualAnalysis)->radarGroup)->code
            ?: (optional($risk->residualAnalysis)->radar_code ?: '');

        return strtoupper(trim((string) $code));
    }

    private function isRecapRadarCode(?string $code): bool
    {
        if (is_null($code)) {
            return false;
        }

        return preg_match('/^[SOCF]\d+$/', strtoupper(trim($code))) === 1;
    }

    private function recapCategoryFromCode(string $code): string
    {
        $prefix = strtoupper(substr($code, 0, 1));
        switch ($prefix) {
            case 'S':
                return 'Strategic';
            case 'O':
                return 'Operational';
            case 'C':
                return 'Compliance';
            case 'F':
                return 'Financial';
            default:
                return 'Other';
        }
    }

    private function recapSortKey(string $code): string
    {
        $code = strtoupper(trim($code));
        $prefix = substr($code, 0, 1);
        $number = (int) preg_replace('/\D+/', '', $code);

        $prefixOrder = [
            'S' => 1,
            'O' => 2,
            'C' => 3,
            'F' => 4,
        ];

        $order = $prefixOrder[$prefix] ?? 99;

        return sprintf('%02d-%03d', $order, $number);
    }
}
