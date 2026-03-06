<?php

namespace App\Services;

use App\RadarGroup;
use App\Risk;
use App\RiskScoreMatrix;
use App\User;
use App\Support\RiskOwnerAccess;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ReportDataService
{
    public function buildPayload(string $scope, ?int $year = null, array $filters = [], ?User $viewer = null): array
    {
        $scope = strtolower(trim($scope));
        $filters = $this->normalizeFilters($filters);
        $risks = $this->loadRisks($year, $filters, $viewer);

        switch ($scope) {
            case 'monitoring':
                return [
                    'title' => 'Risk Monitoring Report',
                    'file' => $year ? ('risk-monitoring-report-' . $year) : 'risk-monitoring-report',
                    'sections' => $this->buildMonitoringSections($risks),
                ];
            case 'board':
                $sections = [];
                $sections = array_merge($sections, $this->buildBoardSections($risks));
                $sections = array_merge($sections, $this->buildHeatmapSections($risks));
                $sections = array_merge($sections, $this->buildRecapSections($risks));

                return [
                    'title' => 'Board of Directors Risk Briefing',
                    'file' => $year ? ('board-risk-briefing-' . $year) : 'board-risk-briefing',
                    'sections' => $sections,
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
            'risk_owner' => null,
        ];

        if (array_key_exists('risk_category_id', $filters) && is_numeric($filters['risk_category_id'])) {
            $normalized['risk_category_id'] = (int) $filters['risk_category_id'];
        }

        if (array_key_exists('risk_cause_category_id', $filters) && is_numeric($filters['risk_cause_category_id'])) {
            $normalized['risk_cause_category_id'] = (int) $filters['risk_cause_category_id'];
        }

        if (array_key_exists('risk_owner', $filters)) {
            $owner = trim((string) $filters['risk_owner']);
            if ($owner !== '') {
                $normalized['risk_owner'] = RiskOwnerAccess::canonicalOption($owner) ?: $owner;
            }
        }

        return $normalized;
    }

    private function loadRisks(?int $year = null, array $filters = [], ?User $viewer = null)
    {
        $query = Risk::with([
            'category',
            'causeCategory',
            'inherentAnalysis',
            'residualAnalysis.radarGroup',
            'treatments',
            'creator',
        ])->orderBy('id');

        $this->applyViewerScope($query, $viewer);

        if (!is_null($year)) {
            $query->where('year', $year);
        }

        if (!empty($filters['risk_category_id'])) {
            $query->where('risk_category_id', (int) $filters['risk_category_id']);
        }

        if (!empty($filters['risk_cause_category_id'])) {
            $query->where('risk_cause_category_id', (int) $filters['risk_cause_category_id']);
        }

        if (!empty($filters['risk_owner'])) {
            $query->where('risk_owner', (string) $filters['risk_owner']);
        }

        return $query->get();
    }

    private function applyViewerScope(Builder $query, ?User $viewer): void
    {
        if (is_null($viewer)) {
            return;
        }

        if ($viewer->isSuperadmin()) {
            $query->whereRaw('1 = 0');

            return;
        }

        if ($viewer->isComplianceDivision()) {
            return;
        }

        $allowedOwners = $viewer->allowedRiskOwners();
        if (empty($allowedOwners)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('risk_owner', $allowedOwners);
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
                RiskOwnerAccess::displayLabel($risk->risk_owner),
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

            $owner = RiskOwnerAccess::displayLabel($risk->risk_owner);
            if ($owner !== '-') {
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

        $trendQuarterlyRows = [];
        foreach (($summary['trend_quarterly'] ?? []) as $row) {
            $trendQuarterlyRows[] = [
                (string) ($row['label'] ?? ($row['period'] ?? '-')),
                (string) number_format((float) ($row['inherent'] ?? 0), 2, '.', ''),
                (string) number_format((float) ($row['residual'] ?? 0), 2, '.', ''),
                (string) number_format((float) ($row['overall'] ?? 0), 2, '.', ''),
            ];
        }

        $trendYearlyRows = [];
        foreach (($summary['trend_yearly'] ?? []) as $row) {
            $trendYearlyRows[] = [
                (string) ($row['label'] ?? ($row['period'] ?? '-')),
                (string) number_format((float) ($row['inherent'] ?? 0), 2, '.', ''),
                (string) number_format((float) ($row['residual'] ?? 0), 2, '.', ''),
                (string) number_format((float) ($row['overall'] ?? 0), 2, '.', ''),
            ];
        }

        $benchmarkScoring = (array) ($summary['benchmark_scoring'] ?? []);
        $benchmarkRows = [
            ['Internal Average Score', (string) number_format((float) ($benchmarkScoring['internal_average_score'] ?? 0), 2, '.', '')],
            ['Industry Baseline Score', (string) number_format((float) ($benchmarkScoring['industry_baseline_score'] ?? 0), 2, '.', '')],
            ['Target Score', (string) number_format((float) ($benchmarkScoring['target_score'] ?? 0), 2, '.', '')],
            ['Gap vs Industry', (string) number_format((float) ($benchmarkScoring['gap_vs_industry'] ?? 0), 2, '.', '')],
            ['Gap vs Target', (string) number_format((float) ($benchmarkScoring['gap_vs_target'] ?? 0), 2, '.', '')],
            ['Position', (string) ($benchmarkScoring['position_label'] ?? '-')],
            ['Industry Source', (string) ($benchmarkScoring['source_name'] ?? '-')],
            ['Source Reference', (string) ($benchmarkScoring['source_reference'] ?? '-')],
            ['Source Year', (string) ($benchmarkScoring['source_year'] ?? '-')],
            ['Score Scale', (string) ($benchmarkScoring['score_scale_min'] ?? 1) . '-' . (string) ($benchmarkScoring['score_scale_max'] ?? 25)],
            ['Methodology', (string) ($benchmarkScoring['methodology'] ?? '-')],
            ['Position Reason', (string) ($benchmarkScoring['position_reason'] ?? '-')],
        ];

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

        $kpiDashboard = (array) ($summary['kpi_dashboard'] ?? []);
        $kpiRows = [
            ['Avg Response Time (Days)', (string) number_format((float) ($kpiDashboard['avg_response_time_days'] ?? 0), 2, '.', '')],
            ['Response SLA Threshold (Days)', (string) ($kpiDashboard['response_sla_days'] ?? 7)],
            ['Response Within SLA', (string) number_format((float) ($kpiDashboard['response_within_sla_percent'] ?? 0), 2, '.', '') . '%'],
            ['Treatment Effectiveness Rate', (string) number_format((float) ($kpiDashboard['treatment_effectiveness_rate'] ?? 0), 2, '.', '') . '%'],
            ['Risk Treatment Coverage Rate', (string) number_format((float) ($kpiDashboard['risk_treatment_coverage_rate'] ?? 0), 2, '.', '') . '%'],
            ['Closed Risk Rate', (string) number_format((float) ($kpiDashboard['closed_risk_rate'] ?? 0), 2, '.', '') . '%'],
            ['Overdue Treatment Rate', (string) number_format((float) ($kpiDashboard['overdue_treatment_rate'] ?? 0), 2, '.', '') . '%'],
            ['Response Sample Count', (string) ($kpiDashboard['response_sample_count'] ?? 0)],
        ];

        $heatmapTrendPayload = (array) ($summary['heatmap_trends'] ?? []);
        $heatmapTrendRows = [];
        foreach (($heatmapTrendPayload['rows'] ?? []) as $row) {
            $heatmapTrendRows[] = [
                (string) ($row['label'] ?? '-'),
                (string) ($row['risk_count'] ?? 0),
                (string) ($row['inherent_high_count'] ?? 0),
                (string) ($row['residual_high_count'] ?? 0),
                (string) ($row['high_risk_reduction'] ?? 0),
                (string) number_format((float) ($row['avg_inherent_score'] ?? 0), 2, '.', ''),
                (string) number_format((float) ($row['avg_residual_score'] ?? 0), 2, '.', ''),
                (string) ($row['dominant_residual_cell'] ?? '-'),
            ];
        }
        $heatmapComparisonRows = [
            ['Latest Period', (string) ($heatmapTrendPayload['latest_period'] ?? '-')],
            ['Previous Period', (string) ($heatmapTrendPayload['previous_period'] ?? '-')],
            ['Latest Residual High-Risk Count', (string) ($heatmapTrendPayload['latest_residual_high_count'] ?? 0)],
            ['Previous Residual High-Risk Count', (string) ($heatmapTrendPayload['previous_residual_high_count'] ?? 0)],
            ['Change Residual High-Risk Count', (string) ($heatmapTrendPayload['change_residual_high_count'] ?? 0)],
            ['Direction', (string) ($heatmapTrendPayload['direction'] ?? '-')],
        ];

        $departmentPerformancePayload = (array) ($summary['department_performance'] ?? []);
        $departmentRows = [];
        foreach (($departmentPerformancePayload['rows'] ?? []) as $row) {
            $departmentRows[] = [
                (string) ($row['rank'] ?? '-'),
                (string) ($row['division'] ?? '-'),
                (string) ($row['risk_count'] ?? 0),
                (string) number_format((float) ($row['closed_rate'] ?? 0), 2, '.', '') . '%',
                (string) number_format((float) ($row['avg_residual_score'] ?? 0), 2, '.', ''),
                (string) number_format((float) ($row['treatment_coverage_percent'] ?? 0), 2, '.', '') . '%',
                (string) number_format((float) ($row['avg_response_time_days'] ?? 0), 2, '.', ''),
                (string) number_format((float) ($row['performance_score'] ?? 0), 2, '.', ''),
            ];
        }

        $rootCausePayload = (array) ($summary['root_cause_analysis'] ?? []);
        $rootCauseCategoryRows = [];
        foreach (($rootCausePayload['top_categories'] ?? []) as $row) {
            $rootCauseCategoryRows[] = [
                (string) ($row['category'] ?? '-'),
                (string) ($row['count'] ?? 0),
                (string) number_format((float) ($row['share_percent'] ?? 0), 2, '.', '') . '%',
                (string) number_format((float) ($row['avg_residual_score'] ?? 0), 2, '.', ''),
            ];
        }
        $rootCauseKeywordRows = [];
        foreach (($rootCausePayload['top_keywords'] ?? []) as $row) {
            $rootCauseKeywordRows[] = [
                (string) ($row['keyword'] ?? '-'),
                (string) ($row['count'] ?? 0),
            ];
        }
        $rootCausePatternRows = [];
        foreach (($rootCausePayload['patterns'] ?? []) as $row) {
            $rootCausePatternRows[] = [
                (string) ($row['pattern'] ?? '-'),
                (string) ($row['evidence'] ?? '-'),
                (string) ($row['recommendation'] ?? '-'),
            ];
        }

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
                'title' => 'Trend Analysis by Quarter',
                'headings' => ['Quarter', 'Inherent', 'Residual', 'Overall'],
                'rows' => $trendQuarterlyRows,
            ],
            [
                'title' => 'Trend Analysis by Year',
                'headings' => ['Year', 'Inherent', 'Residual', 'Overall'],
                'rows' => $trendYearlyRows,
            ],
            [
                'title' => 'Benchmark Scoring',
                'headings' => ['Metric', 'Value'],
                'rows' => $benchmarkRows,
            ],
            [
                'title' => 'Monitoring Mitigation & SLA',
                'headings' => ['Indicator', 'Value'],
                'rows' => $performanceRows,
            ],
            [
                'title' => 'KPI Dashboard',
                'headings' => ['Metric', 'Value'],
                'rows' => $kpiRows,
            ],
            [
                'title' => 'Heat Map Trends - Historical Comparison',
                'headings' => ['Quarter', 'Risk Count', 'Inherent High', 'Residual High', 'Reduction', 'Avg Inherent', 'Avg Residual', 'Dominant Residual Cell'],
                'rows' => $heatmapTrendRows,
            ],
            [
                'title' => 'Heat Map Trend Snapshot',
                'headings' => ['Metric', 'Value'],
                'rows' => $heatmapComparisonRows,
            ],
            [
                'title' => 'Department / Division Performance',
                'headings' => ['Rank', 'Division', 'Risk Count', 'Closed Rate', 'Avg Residual', 'Coverage', 'Avg Response (Days)', 'Performance Score'],
                'rows' => $departmentRows,
            ],
            [
                'title' => 'Root Cause Analysis - Top Categories',
                'headings' => ['Cause Category', 'Count', 'Share', 'Avg Residual'],
                'rows' => $rootCauseCategoryRows,
            ],
            [
                'title' => 'Root Cause Analysis - Recurring Keywords',
                'headings' => ['Keyword', 'Occurrences'],
                'rows' => $rootCauseKeywordRows,
            ],
            [
                'title' => 'Root Cause Analysis - Pattern Insights',
                'headings' => ['Pattern', 'Evidence', 'Recommendation'],
                'rows' => $rootCausePatternRows,
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
        $responseTimeByRiskId = $this->buildResponseTimeByRiskId($risks);

        $statusSummary = $this->buildGroupedCountRows($risks->groupBy(function ($risk) {
            $status = trim((string) ($risk->status ?? 'Unknown'));

            return $status !== '' ? $status : 'Unknown';
        }));

        $categorySummary = $this->buildGroupedCountRows($risks->groupBy(function ($risk) {
            $category = trim((string) (optional($risk->category)->name ?? '-'));

            return $category !== '' ? $category : '-';
        }));

        $ownerSummary = $this->buildGroupedCountRows($risks->groupBy(function ($risk) {
            return RiskOwnerAccess::displayLabel($risk->risk_owner);
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

        $trendMonthlyRows = $this->buildTrendRows($risks, 'monthly');
        $trendQuarterlyRows = $this->buildTrendRows($risks, 'quarterly');
        $trendYearlyRows = $this->buildTrendRows($risks, 'yearly');

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
        $benchmarkScoring = $this->buildBenchmarkScoring($overallAverage);
        $kpiDashboard = $this->buildKpiDashboard(
            $totalRisks,
            $closedCount,
            $mitigationEffectivenessPercent,
            $riskCoveragePercent,
            $dueCount,
            $overdueCount,
            $responseTimeByRiskId
        );
        $departmentPerformance = $this->buildDepartmentPerformance($risks, $responseTimeByRiskId);
        $rootCauseAnalysis = $this->buildRootCauseAnalysis($risks, $overallAverage);
        $portfolioOverview = [
            'by_priority' => $this->buildPrioritySummary($risks),
            'by_category' => array_slice($categorySummary, 0, 6),
            'by_division' => $this->buildDivisionSummary($risks, 6),
            'treatment_timeline' => $this->buildTreatmentTimelineSummary($risks),
            'risk_status' => $statusSummary,
            'top_risks' => $this->buildTopRiskRows($risks, 10),
        ];

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
            'trend' => $trendQuarterlyRows,
            'trend_monthly' => $trendMonthlyRows,
            'trend_quarterly' => $trendQuarterlyRows,
            'trend_yearly' => $trendYearlyRows,
            'trend_granularity' => 'quarterly',
            'benchmark_scoring' => $benchmarkScoring,
            'mitigation' => [
                'treatment_count' => $treatmentCount,
                'covered_risk_count' => $coveredRiskCount,
                'risk_coverage_percent' => $riskCoveragePercent,
                'average_effectiveness_score' => $effectivenessAverageScore,
                'effectiveness_percent' => $mitigationEffectivenessPercent,
            ],
            'kpi_dashboard' => $kpiDashboard,
            'department_performance' => $departmentPerformance,
            'root_cause_analysis' => $rootCauseAnalysis,
            'portfolio_overview' => $portfolioOverview,
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

    private function buildResponseTimeByRiskId($risks): array
    {
        $responseByRiskId = [];

        foreach ($risks as $risk) {
            $riskId = (int) ($risk->id ?? 0);
            $createdAt = $risk->created_at;
            if ($riskId <= 0 || is_null($createdAt)) {
                continue;
            }

            $firstTreatment = collect($risk->treatments ?? [])
                ->filter(function ($treatment) {
                    return !is_null(optional($treatment)->created_at);
                })
                ->sortBy('created_at')
                ->first();

            $firstTreatmentAt = optional($firstTreatment)->created_at;
            if (is_null($firstTreatmentAt)) {
                continue;
            }

            try {
                $hours = Carbon::parse($createdAt)->diffInHours(Carbon::parse($firstTreatmentAt));
            } catch (\Throwable $e) {
                continue;
            }

            $responseByRiskId[$riskId] = round(max(0.0, (float) $hours) / 24, 2);
        }

        return $responseByRiskId;
    }

    private function buildKpiDashboard(
        int $totalRisks,
        int $closedCount,
        float $mitigationEffectivenessPercent,
        float $riskCoveragePercent,
        int $dueCount,
        int $overdueCount,
        array $responseTimeByRiskId
    ): array {
        $responseSlaDays = 7;
        $responseSamples = collect($responseTimeByRiskId)
            ->values()
            ->filter(function ($value) {
                return is_numeric($value);
            })
            ->map(function ($value) {
                return (float) $value;
            })
            ->values();

        $responseSampleCount = (int) $responseSamples->count();
        $avgResponseTimeDays = $responseSampleCount > 0
            ? round((float) $responseSamples->avg(), 2)
            : 0.0;
        $responseWithinSlaCount = (int) $responseSamples->filter(function ($value) use ($responseSlaDays) {
            return (float) $value <= $responseSlaDays;
        })->count();
        $responseWithinSlaPercent = $responseSampleCount > 0
            ? round(($responseWithinSlaCount / $responseSampleCount) * 100, 2)
            : 0.0;
        $closedRiskRate = $totalRisks > 0
            ? round(($closedCount / $totalRisks) * 100, 2)
            : 0.0;
        $overdueTreatmentRate = $dueCount > 0
            ? round(($overdueCount / $dueCount) * 100, 2)
            : 0.0;

        return [
            'avg_response_time_days' => $avgResponseTimeDays,
            'response_sla_days' => $responseSlaDays,
            'response_sample_count' => $responseSampleCount,
            'response_within_sla_count' => $responseWithinSlaCount,
            'response_within_sla_percent' => $responseWithinSlaPercent,
            'treatment_effectiveness_rate' => round($mitigationEffectivenessPercent, 2),
            'risk_treatment_coverage_rate' => round($riskCoveragePercent, 2),
            'closed_risk_rate' => $closedRiskRate,
            'overdue_treatment_rate' => $overdueTreatmentRate,
        ];
    }

    private function buildDepartmentPerformance($risks, array $responseTimeByRiskId): array
    {
        $today = Carbon::today();
        $grouped = collect($risks)->groupBy(function ($risk) {
            return RiskOwnerAccess::displayLabel($risk->risk_owner);
        });

        $rows = $grouped->map(function ($items, $division) use ($responseTimeByRiskId, $today) {
            $items = collect($items)->values();
            $riskCount = (int) $items->count();
            $closedCount = (int) $items->filter(function ($risk) {
                return strtolower(trim((string) ($risk->status ?? ''))) === 'closed';
            })->count();
            $closedRate = $riskCount > 0 ? round(($closedCount / $riskCount) * 100, 2) : 0.0;

            $avgResidualScore = $this->averageFromCollection($items, function ($risk) {
                return optional($risk->residualAnalysis)->residual_risk_score;
            });

            $coveredRiskCount = (int) $items->filter(function ($risk) {
                return collect($risk->treatments ?? [])->isNotEmpty();
            })->count();
            $coveragePercent = $riskCount > 0 ? round(($coveredRiskCount / $riskCount) * 100, 2) : 0.0;

            $responseSamples = $items->map(function ($risk) use ($responseTimeByRiskId) {
                $riskId = (int) ($risk->id ?? 0);
                $value = $responseTimeByRiskId[$riskId] ?? null;

                return is_numeric($value) ? (float) $value : null;
            })->filter(function ($value) {
                return is_numeric($value);
            })->values();

            $avgResponseTimeDays = $responseSamples->isEmpty()
                ? 0.0
                : round((float) $responseSamples->avg(), 2);
            $responseWithinSlaPercent = $responseSamples->isEmpty()
                ? 0.0
                : round(($responseSamples->filter(function ($value) {
                    return (float) $value <= 7;
                })->count() / $responseSamples->count()) * 100, 2);

            $riskReductionValues = $items->map(function ($risk) {
                $inherent = optional($risk->inherentAnalysis)->inherent_risk_score;
                $residual = optional($risk->residualAnalysis)->residual_risk_score;
                if (!is_numeric($inherent) || !is_numeric($residual) || (float) $inherent <= 0) {
                    return null;
                }

                $reduction = (((float) $inherent - (float) $residual) / (float) $inherent) * 100;

                return max(0.0, min(100.0, $reduction));
            })->filter(function ($value) {
                return is_numeric($value);
            })->values();

            $riskReductionPercent = $riskReductionValues->isEmpty()
                ? 0.0
                : round((float) $riskReductionValues->avg(), 2);

            $priorityTemplate = [
                'low' => 0,
                'moderate' => 0,
                'high' => 0,
                'extreme' => 0,
            ];
            $inherentPriority = $priorityTemplate;
            $residualPriority = $priorityTemplate;

            foreach ($items as $risk) {
                $inherentBucket = $this->resolveRiskPriorityBucket(optional($risk->inherentAnalysis)->inherent_risk_score);
                if (!is_null($inherentBucket)) {
                    $inherentPriority[$inherentBucket] = (int) ($inherentPriority[$inherentBucket] + 1);
                }

                $residualBucket = $this->resolveRiskPriorityBucket(optional($risk->residualAnalysis)->residual_risk_score);
                if (!is_null($residualBucket)) {
                    $residualPriority[$residualBucket] = (int) ($residualPriority[$residualBucket] + 1);
                }
            }

            $treatments = $items->flatMap(function ($risk) {
                return collect($risk->treatments ?? [])->values();
            })->values();
            $activityCount = (int) $treatments->count();
            $dueTreatments = $treatments->filter(function ($treatment) {
                return !empty($treatment->deadline);
            });
            $dueCount = (int) $dueTreatments->count();
            $overdueCount = (int) $dueTreatments->filter(function ($treatment) use ($today) {
                try {
                    return Carbon::parse($treatment->deadline)->startOfDay()->lt($today);
                } catch (\Throwable $e) {
                    return false;
                }
            })->count();
            $effectivenessScore = $this->averageFromCollection($treatments, function ($treatment) {
                return optional($treatment)->effectiveness_score;
            });
            $effectivenessPercent = round($effectivenessScore * 100, 2);

            $residualComponent = max(0.0, 100.0 - min(100.0, $avgResidualScore * 4));
            $responseComponent = $responseSamples->isEmpty()
                ? 50.0
                : max(0.0, 100.0 - min(100.0, $avgResponseTimeDays * 8));
            $performanceScore = round(
                ($closedRate * 0.30) +
                ($coveragePercent * 0.20) +
                ($residualComponent * 0.25) +
                ($responseComponent * 0.15) +
                ($riskReductionPercent * 0.10),
                2
            );

            return [
                'division' => (string) $division,
                'risk_count' => $riskCount,
                'closed_rate' => $closedRate,
                'avg_residual_score' => $avgResidualScore,
                'treatment_coverage_percent' => $coveragePercent,
                'avg_response_time_days' => $avgResponseTimeDays,
                'response_within_sla_percent' => $responseWithinSlaPercent,
                'risk_reduction_percent' => $riskReductionPercent,
                'performance_score' => $performanceScore,
                'priority_breakdown' => [
                    'inherent' => $inherentPriority,
                    'residual' => $residualPriority,
                ],
                'treatment_stats' => [
                    'activity_count' => $activityCount,
                    'due_count' => $dueCount,
                    'overdue_count' => $overdueCount,
                    'effectiveness_percent' => $effectivenessPercent,
                ],
            ];
        })->sortByDesc('performance_score')->values();

        $rankedRows = $rows->values()->map(function ($row, $index) {
            $row['rank'] = (int) $index + 1;

            return $row;
        })->all();

        $averageScore = !empty($rankedRows)
            ? round((float) collect($rankedRows)->avg('performance_score'), 2)
            : 0.0;
        $topDivision = !empty($rankedRows)
            ? (string) ($rankedRows[0]['division'] ?? '-')
            : '-';
        $totalRisks = (int) collect($rankedRows)->sum(function ($row) {
            return (int) ($row['risk_count'] ?? 0);
        });

        return [
            'rows' => $rankedRows,
            'average_score' => $averageScore,
            'top_division' => $topDivision,
            'total_risks' => $totalRisks,
            'division_count' => (int) count($rankedRows),
        ];
    }

    private function resolveRiskPriorityBucket($score): ?string
    {
        if (!is_numeric($score)) {
            return null;
        }

        $numeric = (float) $score;
        if ($numeric <= 5) {
            return 'low';
        }

        if ($numeric <= 10) {
            return 'moderate';
        }

        if ($numeric <= 15) {
            return 'high';
        }

        return 'extreme';
    }

    private function resolveRiskDisplayScore(Risk $risk): float
    {
        $residualScore = optional($risk->residualAnalysis)->residual_risk_score;
        if (is_numeric($residualScore)) {
            return round((float) $residualScore, 2);
        }

        $inherentScore = optional($risk->inherentAnalysis)->inherent_risk_score;
        if (is_numeric($inherentScore)) {
            return round((float) $inherentScore, 2);
        }

        $baseScore = $risk->risk_score;
        if (is_numeric($baseScore)) {
            return round((float) $baseScore, 2);
        }

        return 0.0;
    }

    private function resolveRiskPriorityLabel(?string $bucket): string
    {
        $map = [
            'low' => 'Low',
            'moderate' => 'Moderate',
            'high' => 'High',
            'extreme' => 'Extreme',
        ];

        $normalized = strtolower(trim((string) $bucket));

        return $map[$normalized] ?? '-';
    }

    private function buildPrioritySummary($risks): array
    {
        $bucketCounts = [
            'low' => 0,
            'moderate' => 0,
            'high' => 0,
            'extreme' => 0,
        ];

        foreach ($risks as $risk) {
            $bucket = $this->resolveRiskPriorityBucket($this->resolveRiskDisplayScore($risk));
            if (is_null($bucket)) {
                continue;
            }

            $bucketCounts[$bucket] = (int) ($bucketCounts[$bucket] + 1);
        }

        return [
            ['label' => 'Low', 'count' => (int) $bucketCounts['low']],
            ['label' => 'Moderate', 'count' => (int) $bucketCounts['moderate']],
            ['label' => 'High', 'count' => (int) $bucketCounts['high']],
            ['label' => 'Extreme', 'count' => (int) $bucketCounts['extreme']],
        ];
    }

    private function buildDivisionSummary($risks, int $limit = 6): array
    {
        $limit = max(1, $limit);

        return collect($risks)
            ->groupBy(function ($risk) {
                return RiskOwnerAccess::displayLabel($risk->risk_owner);
            })
            ->map(function ($items, $label) {
                return [
                    'label' => (string) $label,
                    'count' => (int) collect($items)->count(),
                ];
            })
            ->sortByDesc('count')
            ->take($limit)
            ->values()
            ->all();
    }

    private function buildTreatmentTimelineSummary($risks): array
    {
        $today = Carbon::today();
        $dueSoonThreshold = (clone $today)->addDays(14);
        $counts = [
            'overdue' => 0,
            'due_soon' => 0,
            'on_track' => 0,
            'no_deadline' => 0,
        ];

        $treatments = collect($risks)->flatMap(function ($risk) {
            return collect($risk->treatments ?? [])->values();
        })->values();

        foreach ($treatments as $treatment) {
            $deadline = optional($treatment)->deadline;
            if (empty($deadline)) {
                $counts['no_deadline'] = (int) ($counts['no_deadline'] + 1);
                continue;
            }

            try {
                $deadlineDate = Carbon::parse($deadline)->startOfDay();
            } catch (\Throwable $e) {
                $counts['no_deadline'] = (int) ($counts['no_deadline'] + 1);
                continue;
            }

            if ($deadlineDate->lt($today)) {
                $counts['overdue'] = (int) ($counts['overdue'] + 1);
                continue;
            }

            if ($deadlineDate->lte($dueSoonThreshold)) {
                $counts['due_soon'] = (int) ($counts['due_soon'] + 1);
                continue;
            }

            $counts['on_track'] = (int) ($counts['on_track'] + 1);
        }

        return [
            ['label' => 'Overdue', 'count' => (int) $counts['overdue']],
            ['label' => 'Due Soon', 'count' => (int) $counts['due_soon']],
            ['label' => 'On Track', 'count' => (int) $counts['on_track']],
            ['label' => 'No Deadline', 'count' => (int) $counts['no_deadline']],
        ];
    }

    private function buildTopRiskRows($risks, int $limit = 10): array
    {
        $limit = max(1, $limit);

        return collect($risks)->map(function ($risk) {
            $riskValue = $this->resolveRiskDisplayScore($risk);
            $priorityBucket = $this->resolveRiskPriorityBucket($riskValue) ?: 'low';
            $division = RiskOwnerAccess::displayLabel($risk->risk_owner);
            $category = trim((string) (optional($risk->category)->name ?? '-'));
            $owner = trim((string) (optional($risk->creator)->name ?? ''));
            if ($owner === '') {
                $owner = $division;
            }

            $likelihood = optional($risk->residualAnalysis)->probability_score;
            if (!is_numeric($likelihood)) {
                $likelihood = optional($risk->inherentAnalysis)->probability_score;
            }
            if (!is_numeric($likelihood)) {
                $likelihood = $risk->probability_score;
            }

            $impact = optional($risk->residualAnalysis)->impact_score;
            if (!is_numeric($impact)) {
                $impact = optional($risk->inherentAnalysis)->impact_score;
            }
            if (!is_numeric($impact)) {
                $impact = $risk->impact_score;
            }

            $description = trim((string) ($risk->risk_description ?: $risk->cause ?: '-'));
            if ($description === '') {
                $description = '-';
            }

            $controlFocus = trim((string) ($risk->existing_control ?: '-'));
            if ($controlFocus === '') {
                $controlFocus = '-';
            }

            return [
                '_sort_score' => $riskValue,
                'risk_id' => (string) ($risk->risk_id ?: ('RISK-' . (string) ($risk->id ?? '-'))),
                'risk_title' => (string) ($risk->risk_title ?: '-'),
                'description' => Str::limit($description, 110),
                'priority_bucket' => $priorityBucket,
                'priority_label' => $this->resolveRiskPriorityLabel($priorityBucket),
                'risk_value' => round($riskValue, 2),
                'division' => $division,
                'owner' => $owner,
                'category' => $category !== '' ? $category : '-',
                'likelihood' => is_numeric($likelihood) ? (int) $likelihood : null,
                'impact' => is_numeric($impact) ? (int) $impact : null,
                'control_focus' => Str::limit($controlFocus, 72),
            ];
        })
            ->sortByDesc('_sort_score')
            ->take($limit)
            ->values()
            ->map(function ($row) {
                unset($row['_sort_score']);

                return $row;
            })
            ->all();
    }

    private function buildRootCauseAnalysis($risks, float $overallAverageScore): array
    {
        $risks = collect($risks)->values();
        $totalRisks = (int) $risks->count();
        $topCategories = $risks->groupBy(function ($risk) {
            $category = trim((string) (optional($risk->causeCategory)->name ?? 'Tanpa Kategori'));

            return $category !== '' ? $category : 'Tanpa Kategori';
        })->map(function ($items, $label) use ($totalRisks) {
            $count = (int) collect($items)->count();
            $sharePercent = $totalRisks > 0
                ? round(($count / $totalRisks) * 100, 2)
                : 0.0;
            $avgResidualScore = $this->averageFromCollection($items, function ($risk) {
                return optional($risk->residualAnalysis)->residual_risk_score;
            });

            return [
                'category' => (string) $label,
                'count' => $count,
                'share_percent' => $sharePercent,
                'avg_residual_score' => $avgResidualScore,
            ];
        })->sortByDesc('count')->take(6)->values()->all();

        $topKeywords = $this->extractCauseKeywords($risks);
        $patterns = [];

        if (!empty($topCategories)) {
            $dominantCategory = $topCategories[0];
            if ((float) ($dominantCategory['share_percent'] ?? 0) >= 35) {
                $patterns[] = [
                    'pattern' => 'Klaster Penyebab Dominan',
                    'evidence' => (string) ($dominantCategory['category'] ?? '-') . ' menyumbang ' . number_format((float) ($dominantCategory['share_percent'] ?? 0), 2, '.', '') . '% dari risiko.',
                    'recommendation' => 'Prioritaskan redesign kontrol dan checklist pencegahan untuk kategori penyebab ini.',
                ];
            }
        }

        $aboveAverageCategory = collect($topCategories)->first(function ($row) use ($overallAverageScore) {
            return (float) ($row['avg_residual_score'] ?? 0) > (float) $overallAverageScore
                && (int) ($row['count'] ?? 0) >= 2;
        });
        if (is_array($aboveAverageCategory)) {
            $patterns[] = [
                'pattern' => 'Kategori Penyebab Residual Tinggi',
                'evidence' => (string) ($aboveAverageCategory['category'] ?? '-') . ' memiliki rata-rata skor residual ' . number_format((float) ($aboveAverageCategory['avg_residual_score'] ?? 0), 2, '.', ''),
                'recommendation' => 'Eskalasi kategori ini untuk review mitigasi terfokus bersama divisi owner.',
            ];
        }

        if (!empty($topKeywords) && (int) ($topKeywords[0]['count'] ?? 0) >= 2) {
            $patterns[] = [
                'pattern' => 'Kata Kunci Akar Penyebab Berulang',
                'evidence' => 'Kata kunci "' . (string) ($topKeywords[0]['keyword'] ?? '-') . '" muncul pada ' . (string) ($topKeywords[0]['count'] ?? 0) . ' penyebab risiko.',
                'recommendation' => 'Buat rencana aksi tematik dan pantau kemunculan ulang bulanan untuk klaster kata kunci ini.',
            ];
        }

        if (empty($patterns)) {
            $patterns[] = [
                'pattern' => 'Sinyal Pola Masih Terbatas',
                'evidence' => 'Dataset saat ini belum menunjukkan pola konsentrasi yang dominan.',
                'recommendation' => 'Tingkatkan konsistensi pernyataan penyebab dan lanjutkan pemantauan tren dari waktu ke waktu.',
            ];
        }

        return [
            'top_categories' => $topCategories,
            'top_keywords' => $topKeywords,
            'patterns' => $patterns,
        ];
    }

    private function extractCauseKeywords($risks, int $limit = 8): array
    {
        $limit = max(1, $limit);
        $stopWords = [
            'dan', 'yang', 'dengan', 'untuk', 'pada', 'dari', 'atau', 'karena', 'akibat',
            'tidak', 'belum', 'sudah', 'dalam', 'oleh', 'serta', 'pihak', 'adanya', 'terjadi',
            'risk', 'risiko', 'issue', 'masalah', 'proses', 'process', 'internal', 'external',
            'system', 'sistem', 'data', 'the', 'and', 'with', 'from', 'that', 'this', 'into',
            'terhadap', 'kurangnya', 'kurang', 'dilakukan', 'lakukan', 'melakukan', 'terdampak',
            'dampak', 'adalah', 'sebagai', 'antara', 'melalui', 'berdasarkan', 'terkait', 'masih',
            'dapat', 'akan', 'perlu', 'setelah', 'sebelum', 'setiap', 'semua', 'banyak', 'lebih',
            'sangat', 'agar', 'guna', 'supaya', 'sehingga', 'namun', 'maupun', 'bila', 'jika',
            'ketika', 'saat', 'tanpa', 'dimana', 'tersebut', 'lainnya', 'maka', 'yaitu', 'yakni',
        ];
        $stopWordLookup = array_fill_keys($stopWords, true);
        $isStopWord = function (string $word) use ($stopWordLookup): bool {
            if (isset($stopWordLookup[$word])) {
                return true;
            }

            if (strlen($word) > 5 && substr($word, -3) === 'nya') {
                $baseWord = substr($word, 0, -3);
                if ($baseWord !== '' && isset($stopWordLookup[$baseWord])) {
                    return true;
                }
            }

            if (strlen($word) > 6 && substr($word, 0, 2) === 'di') {
                $baseWord = substr($word, 2);
                if ($baseWord !== '' && isset($stopWordLookup[$baseWord])) {
                    return true;
                }
            }

            if (strlen($word) > 7 && substr($word, 0, 3) === 'ter') {
                $baseWord = substr($word, 3);
                if ($baseWord !== '' && isset($stopWordLookup[$baseWord])) {
                    return true;
                }
            }

            return false;
        };
        $keywords = [];

        foreach ($risks as $risk) {
            $cause = strtolower(trim((string) ($risk->cause ?? '')));
            if ($cause === '') {
                continue;
            }

            $tokens = preg_split('/[^a-z0-9]+/i', $cause) ?: [];
            foreach ($tokens as $token) {
                $word = strtolower(trim((string) $token));
                if ($word === '' || strlen($word) < 4 || is_numeric($word)) {
                    continue;
                }

                if ($isStopWord($word)) {
                    continue;
                }

                $keywords[$word] = (int) (($keywords[$word] ?? 0) + 1);
            }
        }

        arsort($keywords);
        $rows = [];
        foreach (array_slice($keywords, 0, $limit, true) as $keyword => $count) {
            $rows[] = [
                'keyword' => (string) $keyword,
                'count' => (int) $count,
            ];
        }

        return $rows;
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

    private function buildTrendRows($risks, string $granularity = 'monthly'): array
    {
        $granularity = $this->normalizeTrendGranularity($granularity);

        $periodCount = [
            'monthly' => 6,
            'quarterly' => 8,
            'yearly' => 5,
        ][$granularity];

        $trendMap = [];
        $start = Carbon::now();
        for ($i = $periodCount - 1; $i >= 0; $i--) {
            if ($granularity === 'monthly') {
                $period = (clone $start)->startOfMonth()->subMonths($i);
                $key = $period->format('Y-m');
            } elseif ($granularity === 'quarterly') {
                $period = (clone $start)->firstOfQuarter()->subQuarters($i);
                $key = $period->format('Y') . '-Q' . $period->quarter;
            } else {
                $period = (clone $start)->startOfYear()->subYears($i);
                $key = $period->format('Y');
            }

            $trendMap[$key] = [
                'period' => $key,
                'label' => $this->buildTrendLabel($period, $granularity),
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

            if ($granularity === 'monthly') {
                $key = $createdAt->format('Y-m');
            } elseif ($granularity === 'quarterly') {
                $key = $createdAt->format('Y') . '-Q' . $createdAt->quarter;
            } else {
                $key = $createdAt->format('Y');
            }

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
                'period' => (string) $row['period'],
                'label' => (string) $row['label'],
                'inherent' => $inherentAvg,
                'residual' => $residualAvg,
                'overall' => $overallAvg,
            ];
        })->values()->all();
    }

    private function normalizeTrendGranularity(string $granularity): string
    {
        $normalized = strtolower(trim($granularity));
        if (!in_array($normalized, ['monthly', 'quarterly', 'yearly'], true)) {
            return 'quarterly';
        }

        return $normalized;
    }

    private function buildTrendLabel(Carbon $period, string $granularity): string
    {
        if ($granularity === 'monthly') {
            return $period->format('M Y');
        }

        if ($granularity === 'quarterly') {
            return 'Q' . $period->quarter . ' ' . $period->format('Y');
        }

        return $period->format('Y');
    }

    private function buildBenchmarkScoring(float $internalAverageScore): array
    {
        $industryBaseline = (float) config('risk_benchmark.industry_baseline_score', 12.0);
        $targetScore = (float) config('risk_benchmark.target_score', 9.0);
        $sourceName = (string) config(
            'risk_benchmark.industry_source_name',
            'NHSCFA Risk Management Guidance (public-sector 5x5 governance benchmark)'
        );
        $sourceReference = (string) config(
            'risk_benchmark.industry_source_reference',
            'https://cfa.nhs.uk/about-nhscfa/information-hub/corporate-information-governance/risk-management-guidance (score 9+ discussion trigger; 12+ escalation threshold)'
        );
        $sourceYear = (int) config('risk_benchmark.industry_source_year', 2022);
        $scaleMin = 1;
        $scaleMax = 25;

        $position = 'above_industry_risk';
        $label = 'Above Industry Risk';
        $positionReason = 'Internal average is above industry baseline (' . round($internalAverageScore, 2) . ' > ' . round($industryBaseline, 2) . ').';
        if ($internalAverageScore <= $targetScore) {
            $position = 'ahead_of_target';
            $label = 'Ahead of Target';
            $positionReason = 'Internal average is at or below target (' . round($internalAverageScore, 2) . ' <= ' . round($targetScore, 2) . ').';
        } elseif ($internalAverageScore <= $industryBaseline) {
            $position = 'better_than_industry';
            $label = 'Better Than Industry';
            $positionReason = 'Internal average is below industry baseline but still above target (' . round($internalAverageScore, 2) . ' <= ' . round($industryBaseline, 2) . ').';
        }

        return [
            'industry_baseline_score' => round($industryBaseline, 2),
            'target_score' => round($targetScore, 2),
            'internal_average_score' => round($internalAverageScore, 2),
            'gap_vs_industry' => round($internalAverageScore - $industryBaseline, 2),
            'gap_vs_target' => round($internalAverageScore - $targetScore, 2),
            'position' => $position,
            'position_label' => $label,
            'source_name' => $sourceName,
            'source_reference' => $sourceReference,
            'source_year' => $sourceYear,
            'score_scale_min' => $scaleMin,
            'score_scale_max' => $scaleMax,
            'methodology' => 'Internal Avg = average of overall risk score (Residual if available, fallback Inherent) on the active filter. Baseline 12 and target 9 follow NHSCFA 5x5 governance thresholds (12+ escalated risk, 9+ discussion trigger). Lower score indicates lower risk exposure on a ' . $scaleMin . '-' . $scaleMax . ' scale.',
            'position_reason' => $positionReason,
        ];
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
                    'owner' => RiskOwnerAccess::displayLabel($risk->risk_owner),
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

        $canonicalOwner = RiskOwnerAccess::canonicalOption($risk->risk_owner);
        $value = strtoupper(trim((string) ($canonicalOwner ?: ($risk->risk_owner ?: ''))));
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
