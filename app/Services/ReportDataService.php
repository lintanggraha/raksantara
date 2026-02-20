<?php

namespace App\Services;

use App\RadarGroup;
use App\Risk;
use App\RiskScoreMatrix;

class ReportDataService
{
    public function buildPayload(string $scope, ?int $year = null): array
    {
        switch ($scope) {
            case 'risks':
                return [
                    'title' => 'Risk Register Report',
                    'file' => 'risk-register-report',
                    'sections' => [$this->buildRiskRegisterSection()],
                ];
            case 'heatmap':
                return [
                    'title' => 'Risk Heatmap Report',
                    'file' => 'risk-heatmap-report',
                    'sections' => $this->buildHeatmapSections(),
                ];
            case 'recap':
                return [
                    'title' => 'Risk Recapitulation Report',
                    'file' => $year ? ('risk-recap-report-' . $year) : 'risk-recap-report',
                    'sections' => $this->buildRecapSections($year),
                ];
            case 'overall':
                $sections = [];
                $sections[] = $this->buildRiskRegisterSection();
                $sections = array_merge($sections, $this->buildHeatmapSections());
                $sections = array_merge($sections, $this->buildRecapSections($year));

                return [
                    'title' => 'Overall Risk Management Report',
                    'file' => $year ? ('overall-risk-report-' . $year) : 'overall-risk-report',
                    'sections' => $sections,
                ];
            default:
                abort(404, 'Unknown report scope.');
        }
    }

    private function buildRiskRegisterSection(): array
    {
        $risks = Risk::with([
            'category',
            'causeCategory',
            'inherentAnalysis',
            'residualAnalysis.radarGroup',
            'treatments',
        ])->orderBy('id')->get();

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

    private function buildHeatmapSections(): array
    {
        $risks = Risk::with([
            'category',
            'inherentAnalysis',
            'residualAnalysis.radarGroup',
        ])->get();
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

    private function buildRecapSections(?int $year = null): array
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

        $riskQuery = Risk::with([
            'category',
            'inherentAnalysis',
            'residualAnalysis.radarGroup',
        ]);

        if (!is_null($year)) {
            $riskQuery->where('year', $year);
        }

        $risks = $riskQuery->get();
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
