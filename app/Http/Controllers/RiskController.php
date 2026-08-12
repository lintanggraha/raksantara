<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\AuditLog;
use App\EvidenceAttachment;
use App\Risk;
use App\RiskAlert;
use App\RiskCategory;
use App\RiskCauseCategory;
use App\RiskComment;
use App\ImpactCategory;
use App\ImpactScale;
use App\ProbabilityScale;
use App\RiskTreatment;
use App\RiskTreatmentTemplate;
use App\RiskScoreMatrix;
use App\RiskInherentAnalysis;
use App\RiskResidualAnalysis;
use App\RiskStatusChangeRequest;
use App\RadarGroup;
use App\User;
use App\Support\AuditLogger;
use App\Support\RiskAppetiteEvaluator;
use App\Support\RiskAccess;
use App\Support\InputValidation;
use App\Support\RiskAnalyticsCacheVersion;
use App\Support\RiskOwnerAccess;

class RiskController extends Controller
{
    private const DASHBOARD_SUMMARY_CACHE_TTL_SECONDS = 300;

    public function heatmap()
    {
        $query = Risk::with([
            'category',
            'inherentAnalysis',
            'residualAnalysis.radarGroup',
        ]);
        RiskAccess::applyVisibilityScope($query, auth()->user());

        $risks = $query->get();

        return view('risks.heatmap', $this->buildHeatmapViewDataFromRisks($risks));
    }

    private function buildHeatmapViewDataFromRisks($risks): array
    {
        $codeBuckets = $this->buildHeatmapCodeBuckets($risks);

        $matrix = [];
        $markers = [];

        for ($probability = 1; $probability <= 5; $probability++) {
            for ($impact = 1; $impact <= 5; $impact++) {
                $matrix[$probability][$impact] = [
                    'score' => null,
                    'level' => 'Low',
                    'class' => $this->heatLevelClass('Low'),
                ];
                $markers[$probability][$impact] = [];
            }
        }

        $matrixRows = RiskScoreMatrix::all();
        foreach ($matrixRows as $row) {
            $probability = (int) $row->probability_score;
            $impact = (int) $row->impact_score;
            $score = (int) $row->risk_score;
            $level = RiskScoreMatrix::getRiskLevel($score);

            if ($probability >= 1 && $probability <= 5 && $impact >= 1 && $impact <= 5) {
                $matrix[$probability][$impact] = [
                    'score' => $score,
                    'level' => $level,
                    'class' => $this->heatLevelClass($level),
                ];
            }
        }

        $eventRows = [];

        foreach ($codeBuckets as $bucket) {
            $this->pushHeatMarkerByScores(
                $markers,
                $bucket['inherent_probability'],
                $bucket['inherent_impact'],
                'inherent',
                $bucket['code'],
                $bucket['risk_label']
            );

            $this->pushHeatMarkerByScores(
                $markers,
                $bucket['residual_probability'],
                $bucket['residual_impact'],
                'residual',
                $bucket['code'],
                $bucket['risk_label']
            );

            $eventRows[] = [
                'id' => $bucket['id'],
                'code' => $bucket['code'],
                'category' => $bucket['category'],
                'risk_event' => $bucket['risk_event'],
                'risk_owner' => $bucket['risk_owner'],
            ];
        }

        usort($eventRows, function ($a, $b) {
            $sortByCode = strcmp($this->recapSortKey($a['code']), $this->recapSortKey($b['code']));
            if ($sortByCode !== 0) {
                return $sortByCode;
            }

            return strcmp((string) $a['risk_event'], (string) $b['risk_event']);
        });

        return [
            'matrix' => $matrix,
            'markers' => $markers,
            'eventRows' => $eventRows,
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
            $inherentRiskScore = (float) (optional($inherent)->inherent_risk_score ?? 0);
            $buckets[$radarCode]['inherent_risk_sum'] += $inherentRiskScore > 0 ? $inherentRiskScore : 0.0;

            $residual = $risk->residualAnalysis;
            $residualRiskScore = (float) (optional($residual)->residual_risk_score ?? 0);
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
                ?? optional($representative->residualAnalysis)->radar_description
                ?? '';

            [$inherentProbability, $inherentImpact] = $this->resolveHeatmapCellFromAverageRisk(
                (float) $bucket['inherent_risk_sum'],
                (int) $bucket['risk_n']
            );
            [$residualProbability, $residualImpact] = $this->resolveHeatmapCellFromAverageRisk(
                (float) $bucket['residual_risk_sum'],
                (int) $bucket['risk_n']
            );

            $selected[] = [
                'id' => (int) $representative->id,
                'code' => (string) $bucket['code'],
                'category' => (string) $bucket['category'],
                'risk_event' => $this->resolveHeatmapRiskEvent((string) $bucket['code'], (string) $radarDescription, $representative),
                'risk_owner' => $riskOwner,
                'inherent_probability' => $inherentProbability,
                'inherent_impact' => $inherentImpact,
                'residual_probability' => $residualProbability,
                'residual_impact' => $residualImpact,
                'risk_label' => (string) ($representative->risk_id ?: ('ID-' . $representative->id)),
            ];
        }

        usort($selected, function ($a, $b) {
            return strcmp($this->recapSortKey($a['code']), $this->recapSortKey($b['code']));
        });

        return $selected;
    }

    private function compareHeatmapRiskPriority(Risk $a, Risk $b): int
    {
        $aResidual = (int) (optional($a->residualAnalysis)->residual_risk_score ?? 0);
        $bResidual = (int) (optional($b->residualAnalysis)->residual_risk_score ?? 0);
        if ($aResidual !== $bResidual) {
            return $bResidual <=> $aResidual;
        }

        $aAdjustment = $this->resolveHeatmapAdjustmentValue($a);
        $bAdjustment = $this->resolveHeatmapAdjustmentValue($b);
        if ($aAdjustment !== $bAdjustment) {
            return $bAdjustment <=> $aAdjustment;
        }

        $aInherent = (int) (optional($a->inherentAnalysis)->inherent_risk_score ?? 0);
        $bInherent = (int) (optional($b->inherentAnalysis)->inherent_risk_score ?? 0);
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
            ?? optional($residual)->radar_code
            ?? '';

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

    private function pushHeatMarkerByScores(
        array &$markers,
        ?int $probability,
        ?int $impact,
        string $type,
        string $code,
        string $riskLabel
    ): void
    {
        if (is_null($probability) || is_null($impact)) {
            return;
        }

        if ($probability < 1 || $probability > 5 || $impact < 1 || $impact > 5) {
            return;
        }

        $markers[$probability][$impact][] = [
            'type' => $type,
            'code' => $code,
            'risk_label' => $riskLabel,
        ];
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

        return optional($risk->category)->name ?? '-';
    }

    private function isExcludedHeatmapRadarCode(?string $code): bool
    {
        if (is_null($code)) {
            return true;
        }

        $normalized = strtoupper(trim($code));

        return in_array($normalized, ['N/A', 'NA', 'NONE', '-'], true);
    }

    private function heatLevelClass(string $level): string
    {
        switch ($level) {
            case 'Low':
                return 'level-low';
            case 'Low to Moderate':
                return 'level-low-moderate';
            case 'Moderate':
                return 'level-moderate';
            case 'Moderate to High':
                return 'level-moderate-high';
            default:
                return 'level-high';
        }
    }

    private function resolveScore(int $probability, int $impact): int
    {
        $score = RiskScoreMatrix::getScore($probability, $impact);

        return is_null($score) ? ($probability * $impact) : (int) $score;
    }

    public function recap(Request $request)
    {
        $request->validate([
            'year' => 'nullable|integer|min:2000|max:2100',
        ]);

        $selectedYear = $request->input('year');

        return view('risks.recap', $this->buildRecapViewData($selectedYear));
    }

    private function buildRecapViewData($selectedYear = null): array
    {
        $availableYearsQuery = Risk::query()
            ->whereNotNull('year')
            ->orderBy('year', 'desc')
            ->distinct();
        RiskAccess::applyVisibilityScope($availableYearsQuery, auth()->user());

        $availableYears = $availableYearsQuery->pluck('year');

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
        RiskAccess::applyVisibilityScope($riskQuery, auth()->user());

        if (!empty($selectedYear)) {
            $riskQuery->where('year', (int) $selectedYear);
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

        $summaryTotals = [
            'n' => 0,
            'inh_prob_sum' => 0,
            'inh_imp_sum' => 0,
            'inh_risk_sum' => 0,
            'res_prob_sum' => 0,
            'res_imp_sum' => 0,
            'res_risk_sum' => 0,
            'res_adj_sum' => 0,
        ];

        $detailTotals = [
            'n' => 0,
            'potential_loss_inherent' => 0,
            'potential_loss_residual' => 0,
        ];

        $quantRows = [];
        $quantCategoryTotals = [
            'Strategic' => ['inherent_loss' => 0, 'residual_loss' => 0],
            'Operational' => ['inherent_loss' => 0, 'residual_loss' => 0],
            'Compliance' => ['inherent_loss' => 0, 'residual_loss' => 0],
            'Financial' => ['inherent_loss' => 0, 'residual_loss' => 0],
        ];
        $quantGrandTotals = [
            'risk_count' => 0,
            'inherent_loss' => 0,
            'residual_loss' => 0,
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

            $avgInhProb = round($inhProbSum / $n, 2);
            $avgInhImp = round($inhImpSum / $n, 2);
            $avgInhRisk = round($inhRiskSum / $n, 2);
            $avgResProb = round($resProbSum / $n, 2);
            $avgResImp = round($resImpSum / $n, 2);
            $avgResRisk = round($resRiskSum / $n, 2);
            $avgResAdj = round($resAdjSum / $n, 2);

            $inherentLevel = RiskScoreMatrix::getRiskLevel($avgInhRisk);
            $residualLevel = RiskScoreMatrix::getRiskLevel($avgResRisk);

            $quantMetrics = $this->buildQuantitativeMetricsFromBucket($bucket, $radarGroup);
            $potentialLossInherent = (int) $quantMetrics['inherent_loss_sum'];
            $potentialLossResidual = (int) $quantMetrics['residual_loss_sum'];
            $residualLossFactor = (float) $quantMetrics['residual_probability_avg'];

            $summaryRows[] = [
                'category' => $category,
                'code' => $code,
                'n' => $n,
                'inh_prob_sum' => $inhProbSum,
                'inh_imp_sum' => $inhImpSum,
                'inh_risk_sum' => $inhRiskSum,
                'res_prob_sum' => $resProbSum,
                'res_imp_sum' => $resImpSum,
                'res_risk_sum' => $resRiskSum,
                'res_adj_sum' => round($resAdjSum, 2),
            ];

            $summaryTotals['n'] += $n;
            $summaryTotals['inh_prob_sum'] += $inhProbSum;
            $summaryTotals['inh_imp_sum'] += $inhImpSum;
            $summaryTotals['inh_risk_sum'] += $inhRiskSum;
            $summaryTotals['res_prob_sum'] += $resProbSum;
            $summaryTotals['res_imp_sum'] += $resImpSum;
            $summaryTotals['res_risk_sum'] += $resRiskSum;
            $summaryTotals['res_adj_sum'] += $resAdjSum;

            $detailRows[] = [
                'category' => $category,
                'code' => $code,
                'n' => $n,
                'inh_prob_avg' => $avgInhProb,
                'inh_imp_avg' => $avgInhImp,
                'inh_risk_avg' => $avgInhRisk,
                'inh_level' => $inherentLevel,
                'res_prob_avg' => $avgResProb,
                'res_imp_avg' => $avgResImp,
                'res_risk_avg' => $avgResRisk,
                'res_adj_avg' => $avgResAdj,
                'res_level' => $residualLevel,
                'potential_loss_inherent' => $potentialLossInherent,
                'potential_loss_residual' => $potentialLossResidual,
                'residual_loss_factor' => $residualLossFactor,
            ];

            $detailTotals['n'] += $n;
            $detailTotals['potential_loss_inherent'] += $potentialLossInherent;
            $detailTotals['potential_loss_residual'] += $potentialLossResidual;

            $quantRows[] = [
                'category' => $category,
                'code' => $code,
                'radar_description' => (string) (optional($radarGroup)->description ?? '-'),
                'n' => $n,
                'inherent_impact' => (int) $quantMetrics['inherent_impact_sum'],
                'inherent_probability' => (float) $quantMetrics['inherent_probability_avg'],
                'inherent_loss' => $potentialLossInherent,
                'residual_impact' => (int) $quantMetrics['residual_impact_sum'],
                'residual_probability' => $residualLossFactor,
                'residual_loss' => $potentialLossResidual,
            ];

            if (!isset($quantCategoryTotals[$category])) {
                $quantCategoryTotals[$category] = ['inherent_loss' => 0, 'residual_loss' => 0];
            }

            $quantCategoryTotals[$category]['inherent_loss'] += $potentialLossInherent;
            $quantCategoryTotals[$category]['residual_loss'] += $potentialLossResidual;

            $quantGrandTotals['risk_count'] += $n;
            $quantGrandTotals['inherent_loss'] += $potentialLossInherent;
            $quantGrandTotals['residual_loss'] += $potentialLossResidual;
        }

        $summaryTotals['res_adj_sum'] = round($summaryTotals['res_adj_sum'], 2);

        return [
            'summaryRows' => $summaryRows,
            'detailRows' => $detailRows,
            'summaryTotals' => $summaryTotals,
            'detailTotals' => $detailTotals,
            'quantRows' => $quantRows,
            'quantCategoryTotals' => $quantCategoryTotals,
            'quantGrandTotals' => $quantGrandTotals,
            'availableYears' => $availableYears,
            'selectedYear' => $selectedYear,
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
            $perMenu[$menuCode]['inh_prob_sum'] += (int) (optional($inherent)->probability_score ?? 0);
            $perMenu[$menuCode]['inh_imp_sum'] += (int) (optional($inherent)->impact_score ?? 0);
            $perMenu[$menuCode]['inh_risk_sum'] += (int) (optional($inherent)->inherent_risk_score ?? 0);
            $perMenu[$menuCode]['res_prob_sum'] += (int) (optional($residual)->probability_score ?? 0);
            $perMenu[$menuCode]['res_imp_sum'] += (int) (optional($residual)->impact_score ?? 0);
            $perMenu[$menuCode]['res_risk_sum'] += (int) (optional($residual)->residual_risk_score ?? 0);
            $perMenu[$menuCode]['res_adj_sum'] += (float) ($resAdjRaw ?? 0);
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
        $value = strtoupper(trim((string) ($canonicalOwner ?: ($risk->risk_owner ?? ''))));
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

    private function resolveRecapRadarCode(Risk $risk): string
    {
        $code = optional(optional($risk->residualAnalysis)->radarGroup)->code
            ?? optional($risk->residualAnalysis)->radar_code
            ?? '';

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

    private function normalizeProbabilityPercent($value, float $default = 0.0): float
    {
        if (is_null($value) || $value === '') {
            return round(max(0, min(1, $default)), 4);
        }

        $number = (float) $value;
        if ($number > 1) {
            $number = $number / 100;
        }

        return round(max(0, min(1, $number)), 4);
    }

    private function resolvePotentialLoss(?int $impactAmount, ?float $probabilityPercent, $explicitPotentialLoss = null): int
    {
        if (!is_null($explicitPotentialLoss) && $explicitPotentialLoss !== '') {
            return (int) round((float) $explicitPotentialLoss);
        }

        $impact = max(0, (int) ($impactAmount ?? 0));
        $probability = max(0, min(1, (float) ($probabilityPercent ?? 0)));

        return (int) round($impact * $probability);
    }

    private function resolveRiskLevelAdjustment(int $residualImpactScore, float $adjustment): string
    {
        if ($residualImpactScore < 5 && $adjustment < 6) {
            return 'Rendah';
        }

        if ($residualImpactScore === 5 && $adjustment >= 10) {
            return 'Tinggi';
        }

        if ($residualImpactScore < 5 && $adjustment >= 15) {
            return 'Tinggi';
        }

        return 'Moderat';
    }

    private function resolveRiskLevelFromScore(int $riskScore): string
    {
        if ($riskScore < 6) {
            return 'Low';
        }

        if ($riskScore < 12) {
            return 'Low to Moderate';
        }

        if ($riskScore < 16) {
            return 'Moderate';
        }

        if ($riskScore < 20) {
            return 'Moderate to High';
        }

        return 'High';
    }

    private function buildQuantitativeMetricsFromBucket(
        $bucket,
        ?RadarGroup $radarGroup = null,
        bool $useProfileBaseline = true
    ): array
    {
        $quantProfile = optional($radarGroup)->quantProfile;
        $n = (int) $bucket->count();

        if ($n <= 0) {
            return [
                'n' => 0,
                'inherent_impact_sum' => 0,
                'inherent_probability_avg' => 0.0,
                'inherent_loss_sum' => 0,
                'residual_impact_sum' => 0,
                'residual_probability_avg' => 0.0,
                'residual_loss_sum' => 0,
            ];
        }

        $hasProfile = $useProfileBaseline && !is_null(optional($quantProfile)->potential_loss_inherent);
        if ($hasProfile) {
            // XLS recap uses fixed potential loss baseline per radar code (not multiplied by risk count).
            $inherentImpact = max(0, (int) optional($quantProfile)->potential_loss_inherent);
            $inherentProbability = 1.0;
            $residualProbability = $this->normalizeProbabilityPercent(
                optional($quantProfile)->residual_loss_factor,
                0.3
            );
            $residualImpact = $inherentImpact;

            return [
                'n' => $n,
                'inherent_impact_sum' => $inherentImpact,
                'inherent_probability_avg' => $inherentProbability,
                'inherent_loss_sum' => $inherentImpact,
                'residual_impact_sum' => $residualImpact,
                'residual_probability_avg' => $residualProbability,
                'residual_loss_sum' => (int) round($residualImpact * $residualProbability),
            ];
        }

        $inherentImpactSum = 0;
        $inherentProbabilitySum = 0.0;
        $inherentLossSum = 0;
        $residualImpactSum = 0;
        $residualProbabilitySum = 0.0;
        $residualLossSum = 0;

        foreach ($bucket as $risk) {
            $inherent = $risk->inherentAnalysis;
            $residual = $risk->residualAnalysis;

            $inherentImpactAmount = (int) (optional($inherent)->impact_amount ?? 0);
            $inherentProbability = $this->normalizeProbabilityPercent(optional($inherent)->probability_percent, 1.0);
            $inherentLoss = $this->resolvePotentialLoss(
                $inherentImpactAmount,
                $inherentProbability,
                optional($inherent)->potential_loss
            );

            $residualImpactAmount = optional($residual)->impact_amount;
            if (is_null($residualImpactAmount)) {
                $residualImpactAmount = $inherentImpactAmount;
            }
            $residualImpactAmount = (int) $residualImpactAmount;

            $residualProbability = $this->normalizeProbabilityPercent(
                optional($residual)->probability_percent,
                0.3
            );
            $residualLoss = $this->resolvePotentialLoss(
                $residualImpactAmount,
                $residualProbability,
                optional($residual)->potential_loss
            );

            $inherentImpactSum += $inherentImpactAmount;
            $inherentProbabilitySum += $inherentProbability;
            $inherentLossSum += $inherentLoss;

            $residualImpactSum += $residualImpactAmount;
            $residualProbabilitySum += $residualProbability;
            $residualLossSum += $residualLoss;
        }

        return [
            'n' => $n,
            'inherent_impact_sum' => $inherentImpactSum,
            'inherent_probability_avg' => round($inherentProbabilitySum / $n, 4),
            'inherent_loss_sum' => $inherentLossSum,
            'residual_impact_sum' => $residualImpactSum,
            'residual_probability_avg' => round($residualProbabilitySum / $n, 4),
            'residual_loss_sum' => $residualLossSum,
        ];
    }

    private function buildQuantitativeResultData($risks): array
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

        $quantRows = [];
        $quantCategoryTotals = [
            'Strategic' => ['inherent_loss' => 0, 'residual_loss' => 0],
            'Operational' => ['inherent_loss' => 0, 'residual_loss' => 0],
            'Compliance' => ['inherent_loss' => 0, 'residual_loss' => 0],
            'Financial' => ['inherent_loss' => 0, 'residual_loss' => 0],
        ];
        $quantGrandTotals = [
            'risk_count' => 0,
            'inherent_loss' => 0,
            'residual_loss' => 0,
        ];

        foreach ($radarGroups as $radarGroup) {
            $code = strtoupper(trim((string) $radarGroup->code));
            $category = $this->recapCategoryFromCode($code);
            $bucket = $risksByCode[$code] ?? collect();
            $n = (int) $bucket->count();

            if ($n === 0) {
                continue;
            }

            // Risk Register page must use actual values from each risk row (not recap baseline profile).
            $quantMetrics = $this->buildQuantitativeMetricsFromBucket($bucket, $radarGroup, false);
            $potentialLossInherent = (int) $quantMetrics['inherent_loss_sum'];
            $potentialLossResidual = (int) $quantMetrics['residual_loss_sum'];
            $riskIds = $bucket
                ->map(function ($risk) {
                    $riskId = trim((string) ($risk->risk_id ?? ''));
                    if ($riskId !== '') {
                        return $riskId;
                    }

                    return 'ID-' . (int) $risk->id;
                })
                ->filter(function ($riskId) {
                    return trim((string) $riskId) !== '';
                })
                ->unique()
                ->sort(function ($a, $b) {
                    return strnatcasecmp((string) $a, (string) $b);
                })
                ->values()
                ->all();

            $quantRows[] = [
                'category' => $category,
                'code' => $code,
                'n' => $n,
                'risk_ids' => $riskIds,
                'radar_description' => (string) (optional($radarGroup)->description ?? '-'),
                'inherent_impact' => (int) $quantMetrics['inherent_impact_sum'],
                'inherent_probability' => (float) $quantMetrics['inherent_probability_avg'],
                'inherent_loss' => $potentialLossInherent,
                'residual_impact' => (int) $quantMetrics['residual_impact_sum'],
                'residual_probability' => (float) $quantMetrics['residual_probability_avg'],
                'residual_loss' => $potentialLossResidual,
            ];

            if (!isset($quantCategoryTotals[$category])) {
                $quantCategoryTotals[$category] = ['inherent_loss' => 0, 'residual_loss' => 0];
            }

            $quantCategoryTotals[$category]['inherent_loss'] += $potentialLossInherent;
            $quantCategoryTotals[$category]['residual_loss'] += $potentialLossResidual;

            $quantGrandTotals['risk_count'] += $n;
            $quantGrandTotals['inherent_loss'] += $potentialLossInherent;
            $quantGrandTotals['residual_loss'] += $potentialLossResidual;
        }

        return [
            'rows' => $quantRows,
            'category_totals' => $quantCategoryTotals,
            'grand_totals' => $quantGrandTotals,
        ];
    }

    public function index(Request $request)
    {
        $request->validate([
            'risk_category_id' => 'nullable|integer|exists:risk_categories,id',
            'risk_cause_category_id' => 'nullable|integer|exists:risk_cause_categories,id',
            'risk_tab' => ['nullable', Rule::in(['register', 'heatmap', 'recap'])],
            'year' => 'nullable|integer|min:2000|max:2100',
            'risk_owner' => 'nullable|string|max:255',
        ]);

                $selectedOwnerFilter = trim((string) $request->input('risk_owner', ''));
        $ownerFilterOptions = $this->visibleRiskOwnerOptions();
        $risks = $this->buildIndexQuery($request)->get();
        $activeRiskTab = (string) ($request->input('risk_tab') ?: 'register');
        $selectedYear = $request->input('year');
        $heatmapData = $this->buildHeatmapViewDataFromRisks($risks);
        $recapData = $this->buildRecapViewData($selectedYear);

        $categories = RiskCategory::all();
        $causeCategories = RiskCauseCategory::all();

        return view('risks.index', array_merge(
            compact(
                'risks',
                'categories',
                'causeCategories',
                'activeRiskTab',
                'ownerFilterOptions',
                'selectedOwnerFilter'
            ),
            $heatmapData,
            $recapData
        ));
    }

    public function monitoring(Request $request)
    {
        $request->validate([
            'risk_category_id' => 'nullable|integer|exists:risk_categories,id',
            'risk_cause_category_id' => 'nullable|integer|exists:risk_cause_categories,id',
            'trend_granularity' => 'nullable|in:monthly,quarterly,yearly',
        ]);

        $trendGranularity = $this->resolvePreferredTrendGranularity($request);
        $dashboardSummary = $this->getCachedDashboardSummary($request, $trendGranularity);

        return view('risks.monitoring', compact(
            'dashboardSummary'
        ));
    }

    public function dashboardSummary(Request $request)
    {
        $request->validate([
            'risk_category_id' => 'nullable|integer|exists:risk_categories,id',
            'risk_cause_category_id' => 'nullable|integer|exists:risk_cause_categories,id',
            'trend_granularity' => 'nullable|in:monthly,quarterly,yearly',
        ]);

        $trendGranularity = $this->resolvePreferredTrendGranularity($request);
        $summary = $this->getCachedDashboardSummary($request, $trendGranularity);

        return response()->json($summary);
    }

    private function getCachedDashboardSummary(Request $request, string $trendGranularity): array
    {
        $cacheKey = $this->buildDashboardSummaryCacheKey($request, $trendGranularity);

        return Cache::remember($cacheKey, now()->addSeconds(self::DASHBOARD_SUMMARY_CACHE_TTL_SECONDS), function () use ($request, $trendGranularity) {
            $risks = $this->buildDashboardSummaryQuery($request)->get();

            return $this->buildDashboardSummaryFromCollection($risks, $trendGranularity);
        });
    }

    private function buildDashboardSummaryCacheKey(Request $request, string $trendGranularity): string
    {
        $actor = auth()->user();
        $scopeKey = RiskAccess::scopeCacheKey($actor instanceof User ? $actor : null);
        $categoryId = (int) $request->input('risk_category_id', 0);
        $causeCategoryId = (int) $request->input('risk_cause_category_id', 0);

        return implode(':', [
            'risk',
            'dashboard_summary',
            'v2',
            'cv' . RiskAnalyticsCacheVersion::current(),
            'scope_' . $scopeKey,
            'g' . $trendGranularity,
            'cat' . $categoryId,
            'cause' . $causeCategoryId,
        ]);
    }

    private function buildDashboardSummaryQuery(Request $request)
    {
        $query = Risk::query()
            ->select([
                'id',
                'risk_id',
                'risk_title',
                'sasaran_target',
                'risk_description',
                'cause',
                'existing_control',
                'risk_owner',
                'status',
                'risk_category_id',
                'risk_cause_category_id',
                'created_by',
                'created_at',
                'probability_score',
                'impact_score',
                'risk_score',
            ])
            ->with([
                'category:id,name',
                'causeCategory:id,name',
                'creator:id,name',
                'inherentAnalysis:id,risk_id,inherent_risk_score,probability_score,impact_score',
                'residualAnalysis:id,risk_id,residual_risk_score,probability_score,impact_score,adjustment,risk_adjustment',
                'treatments' => function ($query) {
                    $query->select(['id', 'risk_id', 'deadline', 'effectiveness_score', 'created_at'])
                        ->orderBy('created_at');
                },
            ]);
        RiskAccess::applyVisibilityScope($query, auth()->user());

        if ($request->risk_category_id) {
            $query->where('risk_category_id', $request->risk_category_id);
        }

        if ($request->risk_cause_category_id) {
            $query->where('risk_cause_category_id', $request->risk_cause_category_id);
        }

        return $query;
    }

    public function create()
    {
        $this->ensureRiskWriteAllowed();

        $categories = RiskCategory::all();
        $causeCategories = RiskCauseCategory::all();
        $impactCategories = ImpactCategory::all();
        $probabilityScales = $this->loadActiveProbabilityScales();
        $radarGroups = RadarGroup::with('quantProfile')->orderBy('id')->get();
        $allowedRiskOwners = $this->allowedRiskOwners();
        $treatmentTemplates = $this->loadTreatmentTemplatesForUser();

        abort_if(
            empty($allowedRiskOwners),
            403,
            'Your account division is not configured. Please contact administrator.'
        );

        $vendors = \App\Vendor::where('status', 'Active')->get();
        $riskAppetites = \App\RiskAppetite::where('is_active', true)->orderBy('name')->get();

        return view('risks.create', compact(
            'categories',
            'causeCategories',
            'impactCategories',
            'probabilityScales',
            'radarGroups',
            'allowedRiskOwners',
            'treatmentTemplates',
            'vendors',
            'riskAppetites'
        ));
    }

    public function store(Request $request)
    {
        $this->ensureRiskWriteAllowed();

        $allowedRiskOwners = $this->allowedRiskOwners();

        abort_if(
            empty($allowedRiskOwners),
            403,
            'Your account division is not configured. Please contact administrator.'
        );

        $this->normalizeRiskNumericInputs($request);

        $validated = $request->validate(
            $this->riskValidationRules($allowedRiskOwners),
            $this->riskValidationMessages(),
            $this->riskValidationAttributes()
        );

        $this->ensureSelectableRiskAppetite(
            isset($validated['risk_appetite_id']) ? (int) $validated['risk_appetite_id'] : null
        );

        if (config('app.debug')) {
            Log::debug('RiskController@store payload keys', [
                'keys' => array_keys($validated),
            ]);
        }

        $templateMap = $this->resolveTreatmentTemplateMap(
            $validated['treatment_template_id'] ?? [],
            (string) $validated['risk_owner']
        );

        try {
            $createdRisk = null;

            DB::transaction(function () use ($validated, $templateMap, &$createdRisk) {
                $risk = Risk::createWithGeneratedRiskId([
                    'year' => now()->year,
                    'risk_title' => $validated['risk_title'],
                    'risk_category_id' => $validated['risk_category_id'],
                    'risk_appetite_id' => $validated['risk_appetite_id'] ?? null,
                    'risk_cause_category_id' => $validated['risk_cause_category_id'] ?? null,
                    'vendor_id' => $validated['vendor_id'] ?? null,
                    'cause' => $validated['cause'] ?? null,
                    'existing_control' => $validated['existing_control'] ?? null,
                    'risk_owner' => $validated['risk_owner'],
                    'created_by' => auth()->id(),
                    'sasaran_target' => $validated['sasaran_target'] ?? null,
                    'risk_description' => $validated['risk_description'] ?? null,
                    'status' => 'Open',
                ]);
                $createdRisk = $risk;

                $totalEffScore = [];

                $hasInherent = isset(
                    $validated['inherent_probability_score'],
                    $validated['inherent_impact_scale_id'],
                    $validated['inherent_impact_score']
                );

                $inherentImpactAmount = (int) ($validated['inherent_impact_amount'] ?? 0);
                $inherentProbabilityPercent = $this->normalizeProbabilityPercent(
                    $validated['inherent_probability_percent'] ?? null,
                    1.0
                );
                $inherentPotentialLoss = $this->resolvePotentialLoss(
                    $inherentImpactAmount,
                    $inherentProbabilityPercent,
                    $validated['inherent_potential_loss'] ?? null
                );

                if ($hasInherent) {
                    $inherentRiskScore = $this->resolveScore(
                        (int) $validated['inherent_probability_score'],
                        (int) $validated['inherent_impact_score']
                    );

                    RiskInherentAnalysis::create([
                        'risk_id' => $risk->id,
                        'probability_score' => (int) $validated['inherent_probability_score'],
                        'probability_description' => $validated['inherent_probability_description'] ?? null,
                        'impact_scale_id' => (int) $validated['inherent_impact_scale_id'],
                        'impact_score' => (int) $validated['inherent_impact_score'],
                        'impact_description' => $validated['inherent_impact_description'] ?? null,
                        'impact_amount' => $inherentImpactAmount,
                        'probability_percent' => $inherentProbabilityPercent,
                        'potential_loss' => $inherentPotentialLoss,
                        'inherent_risk_score' => $inherentRiskScore,
                        'risk_level' => RiskScoreMatrix::getRiskLevel($inherentRiskScore),
                    ]);
                }

                $treatments = $validated['treatment_option'] ?? [];
                $descriptions = $validated['treatment_description'] ?? [];
                $pics = $validated['treatment_pic'] ?? [];
                $deadlines = $validated['treatment_deadline'] ?? [];
                $nextActions = $validated['treatment_next_action_at'] ?? [];
                $blockedReasons = $validated['treatment_blocked_reason'] ?? [];
                $playbookChecklists = $validated['treatment_playbook_checklist'] ?? [];
                $effectiveness = $validated['treatment_effectiveness'] ?? [];
                $templateIds = $validated['treatment_template_id'] ?? [];

                foreach ($treatments as $key => $option) {
                    if (empty($option)) {
                        continue;
                    }

                    $effLabel = $effectiveness[$key] ?? null;
                    $map = [
                        'Low' => 0.25,
                        'Medium' => 0.5,
                        'High' => 1,
                    ];
                    $effScore = $map[$effLabel] ?? 0;

                    $templateId = isset($templateIds[$key]) ? (int) $templateIds[$key] : 0;

                    RiskTreatment::create([
                        'risk_id' => $risk->id,
                        'template_id' => $templateId > 0 && isset($templateMap[$templateId]) ? $templateId : null,
                        'option' => $option,
                        'description' => $descriptions[$key] ?? null,
                        'pic' => $pics[$key] ?? null,
                        'status_updated_at' => now(),
                        'deadline' => $deadlines[$key] ?? null,
                        'next_action_at' => $nextActions[$key] ?? null,
                        'blocked_reason' => $blockedReasons[$key] ?? null,
                        'playbook_checklist' => $playbookChecklists[$key] ?? null,
                        'effectiveness' => $effLabel,
                        'effectiveness_score' => $effScore,
                    ]);
                    $totalEffScore[] = $effScore;
                }

                $hasResidual = isset(
                    $validated['residual_probability_score'],
                    $validated['residual_impact_scale_id'],
                    $validated['residual_impact_score']
                );

                if ($hasResidual) {
                    $residualRiskScore = $this->resolveScore(
                        (int) $validated['residual_probability_score'],
                        (int) $validated['residual_impact_score']
                    );

                    $aa = (float) $validated['residual_probability_score'];
                    $y = !empty($totalEffScore) ? (array_sum($totalEffScore) / count($totalEffScore)) : 0;
                    $q = isset($validated['inherent_impact_score']) ? (float) $validated['inherent_impact_score'] : 0;

                    $calc = ($aa - $y) * $q;
                    $residualRiskAdjustment = $calc < 1 ? 1 : $calc;
                    $residualRiskAdjustment = (float) number_format($residualRiskAdjustment, 2, '.', '');
                    $riskAdjustmentInt = (int) round($residualRiskAdjustment);

                    $ac = (int) $validated['residual_impact_score'];
                    $ae = $residualRiskAdjustment;
                    $riskLevelAdjustment = $this->resolveRiskLevelAdjustment($ac, $ae);
                    $finalRiskLevel = $this->resolveRiskLevelFromScore($residualRiskScore);

                    $selectedRadarGroup = null;
                    if (!empty($validated['radar_group_id'])) {
                        $selectedRadarGroup = RadarGroup::find($validated['radar_group_id']);
                    }

                    if (!$selectedRadarGroup && !empty($validated['radar_code'])) {
                        $selectedRadarGroup = RadarGroup::where('code', $validated['radar_code'])->first();
                    }

                    if (!$selectedRadarGroup && !empty($validated['radar_description'])) {
                        $selectedRadarGroup = RadarGroup::where('description', $validated['radar_description'])->first();
                    }

                    $validatedRadarDescription = trim((string) ($validated['radar_description'] ?? ''));
                    $validatedRadarCode = strtoupper(trim((string) ($validated['radar_code'] ?? '')));
                    $radarDescription = optional($selectedRadarGroup)->description
                        ?: ($validatedRadarDescription !== '' ? $validatedRadarDescription : null);
                    $radarCode = optional($selectedRadarGroup)->code
                        ?: ($validatedRadarCode !== '' ? $validatedRadarCode : 'N/A');
                    $radarGroupId = optional($selectedRadarGroup)->id;
                    $defaultResidualProbability = (float) (optional(optional($selectedRadarGroup)->quantProfile)->residual_loss_factor ?? 0.3);
                    $residualImpactAmount = (int) ($validated['residual_impact_amount'] ?? 0);
                    if ($residualImpactAmount === 0 && $inherentImpactAmount > 0) {
                        $residualImpactAmount = $inherentImpactAmount;
                    }
                    $residualProbabilityPercent = $this->normalizeProbabilityPercent(
                        $validated['residual_probability_percent'] ?? null,
                        $defaultResidualProbability
                    );
                    $residualPotentialLoss = $this->resolvePotentialLoss(
                        $residualImpactAmount,
                        $residualProbabilityPercent,
                        $validated['residual_potential_loss'] ?? null
                    );

                    RiskResidualAnalysis::create([
                        'risk_id' => $risk->id,
                        'probability_score' => (int) $validated['residual_probability_score'],
                        'probability_description' => $validated['residual_probability_description'] ?? null,
                        'impact_scale_id' => (int) $validated['residual_impact_scale_id'],
                        'impact_score' => (int) $validated['residual_impact_score'],
                        'impact_description' => $validated['residual_impact_description'] ?? null,
                        'impact_amount' => $residualImpactAmount,
                        'probability_percent' => $residualProbabilityPercent,
                        'potential_loss' => $residualPotentialLoss,
                        'residual_risk_score' => $residualRiskScore,
                        'risk_level' => $finalRiskLevel,
                        'risk_adjustment' => $riskAdjustmentInt,
                        'adjustment' => (string) $residualRiskAdjustment,
                        'risk_level_adjustment' => $riskLevelAdjustment,
                        'radar_group_id' => $radarGroupId,
                        'radar_description' => $radarDescription,
                        'radar_code' => $radarCode,
                    ]);
                }

                AuditLogger::logModelEvent(
                    'risk.created',
                    $risk,
                    null,
                    $risk->fresh([
                        'inherentAnalysis',
                        'residualAnalysis',
                        'treatments',
                    ])->toArray()
                );
            });
        } catch (\Throwable $e) {
            $context = [
                'error' => $e->getMessage(),
                'payload_keys' => array_keys($validated),
            ];

            if (config('app.debug')) {
                $context['trace'] = $e->getTraceAsString();
            }

            Log::error('RiskController@store failed', $context);

            return redirect()->back()->withInput()->with('error', 'Failed to create risk. Please check the input and try again.');
        }

        if ($request->input('post_submit_action') === 'bcm' && !is_null($createdRisk)) {
            return redirect()
                ->route('risks.show', $createdRisk->id)
                ->withFragment('bcm')
                ->with('success', 'Risk created successfully. Continue with BCM (BIA/BCP).');
        }

        return redirect()->route('risks.index')->with('success', 'Risk created successfully with all analysis data');
    }

    public function show($id)
    {
        $riskQuery = Risk::with([
            'category',
            'causeCategory',
            'impactScale.category',
            'vendor',
            'riskAppetite',
            'attachments.uploader',
            'treatments.attachments.uploader',
            'treatments.template',
            'comments' => function ($query) {
                $query
                    ->with(['user', 'mentions', 'attachments.uploader'])
                    ->oldest();
            },
            'inherentAnalysis.impactScale',
            'bia.bcp',
            'residualAnalysis.impactScale',
            'residualAnalysis.radarGroup',
            'statusChangeRequests' => function ($query) {
                $query
                    ->with(['requester', 'reviewer'])
                    ->latest();
            },
            'rcsaWorkspace.updater',
            'alerts' => function ($query) {
                $query
                    ->with('treatment')
                    ->latest();
            },
        ]);
        RiskAccess::applyVisibilityScope($riskQuery, auth()->user());

        $risk = $riskQuery->findOrFail($id);

        $activityLogs = $this->loadRiskActivityLogs($risk);
        $appetiteEvaluation = RiskAppetiteEvaluator::evaluate($risk);
        $mentionUsers = User::query()
            ->select('id', 'name', 'username')
            ->whereNotNull('username')
            ->whereRaw("TRIM(username) <> ''")
            ->orderBy('name')
            ->orderBy('username')
            ->get();

        return view('risks.show', compact('risk', 'activityLogs', 'mentionUsers', 'appetiteEvaluation'));
    }


    public function edit($id)
    {
        $this->ensureRiskWriteAllowed();

        $risk = Risk::with([
            'inherentAnalysis.impactScale.category',
            'residualAnalysis.impactScale.category',
            'residualAnalysis.radarGroup',
            'treatments.template',
            'statusChangeRequests' => function ($query) {
                $query
                    ->where('status', 'pending')
                    ->latest();
            },
        ])->findOrFail($id);
        $this->ensureCanManageRisk($risk);
        $categories = RiskCategory::all();
        $causeCategories = RiskCauseCategory::all();
        $impactCategories = ImpactCategory::all();
        $probabilityScales = $this->loadEditableProbabilityScales($risk);
        $radarGroups = RadarGroup::with('quantProfile')->orderBy('id')->get();
        $allowedRiskOwners = $this->allowedRiskOwners($risk->risk_owner);
        $treatmentTemplates = $this->loadTreatmentTemplatesForUser();

        $vendors = \App\Vendor::where('status', 'Active')->get();
        $riskAppetitesQuery = \App\RiskAppetite::query()
            ->where(function ($query) use ($risk) {
                $query->where('is_active', true);

                if (!is_null($risk->risk_appetite_id)) {
                    $query->orWhere('id', $risk->risk_appetite_id);
                }
            })
            ->orderBy('name');
        $riskAppetites = $riskAppetitesQuery->get();

        return view('risks.edit', compact(
            'risk',
            'categories',
            'causeCategories',
            'impactCategories',
            'probabilityScales',
            'radarGroups',
            'allowedRiskOwners',
            'treatmentTemplates',
            'vendors',
            'riskAppetites'
        ));
    }

    private function loadActiveProbabilityScales()
    {
        return ProbabilityScale::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('score')
            ->orderBy('id')
            ->get();
    }

    private function loadEditableProbabilityScales(Risk $risk)
    {
        $scales = $this->loadActiveProbabilityScales();

        $legacyScales = [
            [
                'score' => optional($risk->inherentAnalysis)->probability_score,
                'description' => optional($risk->inherentAnalysis)->probability_description,
            ],
            [
                'score' => optional($risk->residualAnalysis)->probability_score,
                'description' => optional($risk->residualAnalysis)->probability_description,
            ],
        ];

        foreach ($legacyScales as $legacyScale) {
            $score = (int) ($legacyScale['score'] ?? 0);
            if ($score < 1 || $score > 5) {
                continue;
            }

            $alreadyLoaded = $scales->contains(function ($scale) use ($score) {
                return (int) $scale->score === $score;
            });

            if ($alreadyLoaded) {
                continue;
            }

            $scales->push(new ProbabilityScale([
                'score' => $score,
                'description' => trim((string) ($legacyScale['description'] ?? '')),
                'sort_order' => 999999,
                'is_active' => false,
            ]));
        }

        return $scales
            ->sortBy(function ($scale) {
                return sprintf(
                    '%06d-%02d-%06d',
                    (int) ($scale->sort_order ?? 0),
                    (int) ($scale->score ?? 0),
                    (int) ($scale->id ?? 0)
                );
            })
            ->values();
    }

    public function update(Request $request, $id)
    {
        $this->ensureRiskWriteAllowed();

        $risk = Risk::with([
            'inherentAnalysis',
            'residualAnalysis',
            'treatments.attachments',
            'statusChangeRequests',
            'attachments',
        ])->findOrFail($id);
        $this->ensureCanManageRisk($risk);
        $allowedRiskOwners = $this->allowedRiskOwners($risk->risk_owner);

        $this->normalizeRiskNumericInputs($request);

        $validated = $request->validate(
            $this->riskValidationRules($allowedRiskOwners, true),
            $this->riskValidationMessages(),
            $this->riskValidationAttributes()
        );

        $this->ensureSelectableRiskAppetite(
            isset($validated['risk_appetite_id']) ? (int) $validated['risk_appetite_id'] : null,
            $risk
        );

        $statusApprovalRequested = false;

        $templateMap = $this->resolveTreatmentTemplateMap(
            $validated['treatment_template_id'] ?? [],
            (string) $validated['risk_owner']
        );

        try {
            DB::transaction(function () use ($risk, $validated, &$statusApprovalRequested, $templateMap) {
                $isSuperadmin = !is_null(auth()->user()) && auth()->user()->isSuperadmin();
                $requestedStatus = (string) $validated['status'];
                $currentStatus = (string) $risk->status;
                $statusToPersist = $currentStatus;
                $statusReason = trim((string) ($validated['status_change_reason'] ?? ''));

                if ($requestedStatus !== $currentStatus) {
                    if ($isSuperadmin || $requestedStatus === 'Open') {
                        $statusToPersist = $requestedStatus;
                    } elseif (in_array($requestedStatus, ['Monitoring', 'Closed'], true)) {
                        if ($statusReason === '') {
                            throw ValidationException::withMessages([
                                'status_change_reason' => 'Status change reason is required for Monitoring or Closed request.',
                            ]);
                        }

                        $statusToPersist = $currentStatus;
                        $statusApprovalRequested = true;

                        $statusRequest = $risk->statusChangeRequests()
                            ->where('status', 'pending')
                            ->where('to_status', $requestedStatus)
                            ->latest()
                            ->first();

                        if (is_null($statusRequest)) {
                            $statusRequest = RiskStatusChangeRequest::create([
                                'risk_id' => $risk->id,
                                'requested_by' => auth()->id(),
                                'from_status' => $currentStatus,
                                'to_status' => $requestedStatus,
                                'reason' => $statusReason !== '' ? $statusReason : null,
                                'status' => 'pending',
                            ]);

                            AuditLogger::logModelEvent(
                                'risk.status_change.requested',
                                $statusRequest,
                                null,
                                $statusRequest->toArray()
                            );
                        } else {
                            $beforeRequest = $statusRequest->toArray();
                            $statusRequest->update([
                                'requested_by' => auth()->id(),
                                'from_status' => $currentStatus,
                                'reason' => $statusReason !== '' ? $statusReason : null,
                            ]);

                            AuditLogger::logModelEvent(
                                'risk.status_change.request.updated',
                                $statusRequest,
                                $beforeRequest,
                                $statusRequest->fresh()->toArray()
                            );
                        }
                    } else {
                        $statusToPersist = $requestedStatus;
                    }
                }

                $beforeRisk = $risk->toArray();

                $risk->update([
                    'risk_title' => $validated['risk_title'],
                    'sasaran_target' => $validated['sasaran_target'] ?? null,
                    'risk_description' => $validated['risk_description'] ?? null,
                    'risk_category_id' => $validated['risk_category_id'],
                    'risk_appetite_id' => $validated['risk_appetite_id'] ?? null,
                    'risk_cause_category_id' => $validated['risk_cause_category_id'] ?? null,
                    'vendor_id' => $validated['vendor_id'] ?? null,
                    'cause' => $validated['cause'] ?? null,
                    'existing_control' => $validated['existing_control'] ?? null,
                    'risk_owner' => $validated['risk_owner'],
                    'status' => $statusToPersist,
                ]);

                $totalEffScore = [];
                $existingTreatments = $risk->treatments->keyBy('id');
                $keptTreatmentIds = [];

                $treatmentIds = $validated['treatment_id'] ?? [];
                $treatments = $validated['treatment_option'] ?? [];
                $descriptions = $validated['treatment_description'] ?? [];
                $pics = $validated['treatment_pic'] ?? [];
                $deadlines = $validated['treatment_deadline'] ?? [];
                $nextActions = $validated['treatment_next_action_at'] ?? [];
                $blockedReasons = $validated['treatment_blocked_reason'] ?? [];
                $playbookChecklists = $validated['treatment_playbook_checklist'] ?? [];
                $effectiveness = $validated['treatment_effectiveness'] ?? [];
                $templateIds = $validated['treatment_template_id'] ?? [];

                foreach ($treatments as $key => $option) {
                    if (empty($option)) {
                        continue;
                    }

                    $effLabel = $effectiveness[$key] ?? null;
                    $map = [
                        'Low' => 0.25,
                        'Medium' => 0.5,
                        'High' => 1,
                    ];
                    $effScore = $map[$effLabel] ?? 0;

                    $templateId = isset($templateIds[$key]) ? (int) $templateIds[$key] : 0;

                    $payload = [
                        'template_id' => $templateId > 0 && isset($templateMap[$templateId]) ? $templateId : null,
                        'option' => $option,
                        'description' => $descriptions[$key] ?? null,
                        'pic' => $pics[$key] ?? null,
                        'deadline' => $deadlines[$key] ?? null,
                        'next_action_at' => $nextActions[$key] ?? null,
                        'blocked_reason' => $blockedReasons[$key] ?? null,
                        'playbook_checklist' => $playbookChecklists[$key] ?? null,
                        'effectiveness' => $effLabel,
                        'effectiveness_score' => $effScore,
                    ];

                    $requestedTreatmentId = isset($treatmentIds[$key]) ? (int) $treatmentIds[$key] : 0;
                    if ($requestedTreatmentId > 0) {
                        $existingTreatment = $existingTreatments->get($requestedTreatmentId);
                        if (is_null($existingTreatment)) {
                            throw ValidationException::withMessages([
                                'treatment_id' => 'Invalid treatment reference.',
                            ]);
                        }

                        $existingTreatment->update($payload);
                        $keptTreatmentIds[] = (int) $existingTreatment->id;
                    } else {
                        $createdTreatment = RiskTreatment::create(array_merge(
                            ['risk_id' => $risk->id],
                            $payload
                        ));
                        $keptTreatmentIds[] = (int) $createdTreatment->id;
                    }

                    $totalEffScore[] = $effScore;
                }

                $removedTreatments = $risk->treatments()
                    ->with('attachments')
                    ->when(!empty($keptTreatmentIds), function ($query) use ($keptTreatmentIds) {
                        $query->whereNotIn('id', $keptTreatmentIds);
                    }, function ($query) {
                        $query->whereNotNull('id');
                    })
                    ->get();

                foreach ($removedTreatments as $removedTreatment) {
                    $this->deleteAttachmentCollection($removedTreatment->attachments, 'evidence.deleted.by_treatment_remove');
                    $removedTreatment->delete();
                }

                $hasInherent = isset(
                    $validated['inherent_probability_score'],
                    $validated['inherent_impact_scale_id'],
                    $validated['inherent_impact_score']
                );

                $defaultInherentProbability = optional($risk->inherentAnalysis)->probability_percent;
                $inherentImpactAmount = (int) ($validated['inherent_impact_amount'] ?? optional($risk->inherentAnalysis)->impact_amount ?? 0);
                $inherentProbabilityPercent = $this->normalizeProbabilityPercent(
                    $validated['inherent_probability_percent'] ?? null,
                    is_null($defaultInherentProbability) ? 1.0 : (float) $defaultInherentProbability
                );
                $inherentPotentialLoss = $this->resolvePotentialLoss(
                    $inherentImpactAmount,
                    $inherentProbabilityPercent,
                    $validated['inherent_potential_loss'] ?? optional($risk->inherentAnalysis)->potential_loss
                );

                if ($hasInherent) {
                    $inherentRiskScore = $this->resolveScore(
                        (int) $validated['inherent_probability_score'],
                        (int) $validated['inherent_impact_score']
                    );

                    RiskInherentAnalysis::updateOrCreate(
                        ['risk_id' => $risk->id],
                        [
                            'probability_score' => (int) $validated['inherent_probability_score'],
                            'probability_description' => $validated['inherent_probability_description'] ?? null,
                            'impact_scale_id' => (int) $validated['inherent_impact_scale_id'],
                            'impact_score' => (int) $validated['inherent_impact_score'],
                            'impact_description' => $validated['inherent_impact_description'] ?? null,
                            'impact_amount' => $inherentImpactAmount,
                            'probability_percent' => $inherentProbabilityPercent,
                            'potential_loss' => $inherentPotentialLoss,
                            'inherent_risk_score' => $inherentRiskScore,
                            'risk_level' => RiskScoreMatrix::getRiskLevel($inherentRiskScore),
                        ]
                    );
                }

                $selectedRadarGroup = null;
                if (!empty($validated['radar_group_id'])) {
                    $selectedRadarGroup = RadarGroup::find($validated['radar_group_id']);
                }

                if (!$selectedRadarGroup && !empty($validated['radar_code'])) {
                    $selectedRadarGroup = RadarGroup::where('code', $validated['radar_code'])->first();
                }

                if (!$selectedRadarGroup && !empty($validated['radar_description'])) {
                    $selectedRadarGroup = RadarGroup::where('description', $validated['radar_description'])->first();
                }

                if (!$selectedRadarGroup && !is_null(optional($risk->residualAnalysis)->radar_group_id)) {
                    $selectedRadarGroup = RadarGroup::find(optional($risk->residualAnalysis)->radar_group_id);
                }

                $hasResidual = isset(
                    $validated['residual_probability_score'],
                    $validated['residual_impact_scale_id'],
                    $validated['residual_impact_score']
                );

                if ($hasResidual) {
                    $residualRiskScore = $this->resolveScore(
                        (int) $validated['residual_probability_score'],
                        (int) $validated['residual_impact_score']
                    );

                    $aa = (float) $validated['residual_probability_score'];
                    $y = !empty($totalEffScore) ? ((float) array_sum($totalEffScore) / count($totalEffScore)) : 0;
                    $q = isset($validated['inherent_impact_score'])
                        ? (float) $validated['inherent_impact_score']
                        : (float) (optional($risk->inherentAnalysis)->impact_score ?? 0);

                    $calc = ($aa - $y) * $q;
                    $computedAdjustment = $calc < 1 ? 1 : $calc;
                    $computedAdjustment = (float) number_format($computedAdjustment, 2, '.', '');
                    $residualRiskAdjustment = $computedAdjustment;
                    $riskAdjustmentInt = (int) round($residualRiskAdjustment);
                    $riskLevelAdjustment = $this->resolveRiskLevelAdjustment(
                        (int) $validated['residual_impact_score'],
                        $residualRiskAdjustment
                    );
                    $finalRiskLevel = $this->resolveRiskLevelFromScore($residualRiskScore);

                    $validatedRadarDescription = trim((string) ($validated['radar_description'] ?? ''));
                    $validatedRadarCode = strtoupper(trim((string) ($validated['radar_code'] ?? '')));
                    $radarDescription = optional($selectedRadarGroup)->description
                        ?: ($validatedRadarDescription !== ''
                            ? $validatedRadarDescription
                            : optional($risk->residualAnalysis)->radar_description);
                    $radarCode = optional($selectedRadarGroup)->code
                        ?: ($validatedRadarCode !== ''
                            ? $validatedRadarCode
                            : (optional($risk->residualAnalysis)->radar_code ?? 'N/A'));
                    $radarGroupId = optional($selectedRadarGroup)->id ?: optional($risk->residualAnalysis)->radar_group_id;

                    $defaultResidualProbability = (float) (
                        optional(optional($selectedRadarGroup)->quantProfile)->residual_loss_factor
                        ?? optional($risk->residualAnalysis)->probability_percent
                        ?? 0.3
                    );
                    $residualImpactAmount = (int) ($validated['residual_impact_amount'] ?? optional($risk->residualAnalysis)->impact_amount ?? 0);
                    if ($residualImpactAmount === 0 && $inherentImpactAmount > 0) {
                        $residualImpactAmount = $inherentImpactAmount;
                    }
                    $residualProbabilityPercent = $this->normalizeProbabilityPercent(
                        $validated['residual_probability_percent'] ?? null,
                        $defaultResidualProbability
                    );
                    $residualPotentialLoss = $this->resolvePotentialLoss(
                        $residualImpactAmount,
                        $residualProbabilityPercent,
                        $validated['residual_potential_loss'] ?? optional($risk->residualAnalysis)->potential_loss
                    );

                    RiskResidualAnalysis::updateOrCreate(
                        ['risk_id' => $risk->id],
                        [
                            'probability_score' => (int) $validated['residual_probability_score'],
                            'probability_description' => $validated['residual_probability_description'] ?? null,
                            'impact_scale_id' => (int) $validated['residual_impact_scale_id'],
                            'impact_score' => (int) $validated['residual_impact_score'],
                            'impact_description' => $validated['residual_impact_description'] ?? null,
                            'impact_amount' => $residualImpactAmount,
                            'probability_percent' => $residualProbabilityPercent,
                            'potential_loss' => $residualPotentialLoss,
                            'residual_risk_score' => $residualRiskScore,
                            'risk_level' => $finalRiskLevel,
                            'risk_adjustment' => $riskAdjustmentInt,
                            'adjustment' => (string) $residualRiskAdjustment,
                            'risk_level_adjustment' => $riskLevelAdjustment,
                            'radar_group_id' => $radarGroupId,
                            'radar_description' => $radarDescription,
                            'radar_code' => $radarCode,
                        ]
                    );
                }

                AuditLogger::logModelEvent(
                    'risk.updated',
                    $risk,
                    $beforeRisk,
                    $risk->fresh([
                        'inherentAnalysis',
                        'residualAnalysis',
                        'treatments',
                    ])->toArray()
                );
            });
        } catch (\Throwable $e) {
            Log::error('RiskController@update failed', [
                'risk_id' => $risk->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update risk. Please check the input and try again.');
        }

        if ($statusApprovalRequested) {
            return redirect()
                ->route('risks.index')
                ->with('success', 'Risk updated. Status change request submitted and waiting for superadmin approval.');
        }

        if ($request->input('post_submit_action') === 'bcm') {
            return redirect()
                ->route('risks.show', $risk->id)
                ->withFragment('bcm')
                ->with('success', 'Risk updated successfully. Continue with BCM (BIA/BCP).');
        }

        return redirect()->route('risks.index')->with('success', 'Risk updated successfully with analysis data');
    }

    public function destroy($id)
    {
        $this->ensureRiskWriteAllowed();

        $risk = Risk::with([
            'attachments',
            'treatments.attachments',
            'inherentAnalysis',
            'residualAnalysis',
        ])->findOrFail($id);
        $this->ensureCanManageRisk($risk);
        $beforeRisk = $risk->toArray();

        $this->deleteAttachmentCollection($risk->attachments, 'evidence.deleted.by_risk_remove');
        foreach ($risk->treatments as $treatment) {
            $this->deleteAttachmentCollection($treatment->attachments, 'evidence.deleted.by_treatment_remove');
        }

        $risk->delete();

        AuditLogger::logModelEvent('risk.deleted', $risk, $beforeRisk, null);

        return redirect()->route('risks.index')->with('success', 'Risk deleted successfully');
    }

    private function normalizeRiskNumericInputs(Request $request): void
    {
        $request->merge([
            'inherent_impact_amount' => InputValidation::normalizeGroupedInteger($request->input('inherent_impact_amount')),
            'inherent_probability_percent' => InputValidation::normalizeLocalizedDecimal($request->input('inherent_probability_percent')),
            'inherent_potential_loss' => InputValidation::normalizeGroupedInteger($request->input('inherent_potential_loss')),
            'residual_impact_amount' => InputValidation::normalizeGroupedInteger($request->input('residual_impact_amount')),
            'residual_probability_percent' => InputValidation::normalizeLocalizedDecimal($request->input('residual_probability_percent')),
            'residual_potential_loss' => InputValidation::normalizeGroupedInteger($request->input('residual_potential_loss')),
        ]);
    }

    private function riskValidationRules(array $allowedRiskOwners, bool $includeStatus = false): array
    {
        $textRule255 = 'regex:' . InputValidation::SAFE_TEXT_REGEX;
        $textRule1000 = 'regex:' . InputValidation::SAFE_TEXT_REGEX;
        $textRule5000 = 'regex:' . InputValidation::SAFE_TEXT_REGEX;
        $strictInteger = 'regex:' . InputValidation::STRICT_INTEGER_REGEX;
        $strictDecimal = 'regex:' . InputValidation::STRICT_DECIMAL_REGEX;

        $rules = [
            'risk_title' => ['required', 'string', 'max:255', $textRule255],
            'sasaran_target' => ['nullable', 'string', 'max:5000', $textRule5000],
            'risk_description' => ['nullable', 'string', 'max:5000', $textRule5000],
            'risk_category_id' => 'required|integer|exists:risk_categories,id',
            'risk_appetite_id' => 'nullable|integer|exists:risk_appetites,id',
            'risk_cause_category_id' => 'nullable|integer|exists:risk_cause_categories,id',
            'vendor_id' => 'nullable|integer|exists:vendors,id',
            'cause' => ['nullable', 'string', 'max:5000', $textRule5000],
            'existing_control' => ['nullable', 'string', 'max:5000', $textRule5000],
            'risk_owner' => ['required', 'string', 'max:255', Rule::in($allowedRiskOwners)],
            'inherent_probability_score' => 'nullable|integer|between:1,5|required_with:inherent_impact_scale_id,inherent_impact_score',
            'inherent_probability_description' => ['nullable', 'string', 'max:255', $textRule255],
            'inherent_impact_scale_id' => 'nullable|integer|exists:impact_scales,id|required_with:inherent_probability_score,inherent_impact_score',
            'inherent_impact_score' => 'nullable|integer|between:1,5|required_with:inherent_probability_score,inherent_impact_scale_id',
            'inherent_impact_description' => ['nullable', 'string', 'max:255', $textRule255],
            'inherent_impact_amount' => ['nullable', 'numeric', $strictInteger, 'min:0'],
            'inherent_probability_percent' => ['nullable', 'numeric', $strictDecimal, 'min:0', 'max:100'],
            'inherent_potential_loss' => ['nullable', 'numeric', $strictInteger, 'min:0'],
            'residual_probability_score' => 'nullable|integer|between:1,5|required_with:residual_impact_scale_id,residual_impact_score',
            'residual_probability_description' => ['nullable', 'string', 'max:255', $textRule255],
            'residual_impact_scale_id' => 'nullable|integer|exists:impact_scales,id|required_with:residual_probability_score,residual_impact_score',
            'residual_impact_score' => 'nullable|integer|between:1,5|required_with:residual_probability_score,residual_impact_scale_id',
            'residual_impact_description' => ['nullable', 'string', 'max:255', $textRule255],
            'residual_impact_amount' => ['nullable', 'numeric', $strictInteger, 'min:0'],
            'residual_probability_percent' => ['nullable', 'numeric', $strictDecimal, 'min:0', 'max:100'],
            'residual_potential_loss' => ['nullable', 'numeric', $strictInteger, 'min:0'],
            'radar_group_id' => 'nullable|integer|exists:radar_groups,id',
            'radar_description' => 'nullable|string|max:255',
            'radar_code' => 'nullable|string|max:10|regex:/^[A-Za-z0-9\\/]+$/',
            'status_change_reason' => ['nullable', 'string', 'max:2000', $textRule5000],
            'treatment_id' => 'nullable|array|max:20',
            'treatment_id.*' => 'nullable|integer|min:1',
            'treatment_template_id' => 'nullable|array|max:20',
            'treatment_template_id.*' => 'nullable|integer|exists:risk_treatment_templates,id',
            'treatment_option' => 'nullable|array|max:20',
            'treatment_option.*' => 'nullable|string|in:Avoidance,Mitigation,Transfer,Acceptance',
            'treatment_description' => 'nullable|array|max:20',
            'treatment_description.*' => ['nullable', 'string', 'max:1000', $textRule1000],
            'treatment_pic' => 'nullable|array|max:20',
            'treatment_pic.*' => ['nullable', 'string', 'max:255', $textRule255],
            'treatment_deadline' => 'nullable|array|max:20',
            'treatment_deadline.*' => 'nullable|date',
            'treatment_next_action_at' => 'nullable|array|max:20',
            'treatment_next_action_at.*' => 'nullable|date',
            'treatment_blocked_reason' => 'nullable|array|max:20',
            'treatment_blocked_reason.*' => ['nullable', 'string', 'max:1000', $textRule1000],
            'treatment_playbook_checklist' => 'nullable|array|max:20',
            'treatment_playbook_checklist.*' => ['nullable', 'string', 'max:5000', $textRule5000],
            'treatment_effectiveness' => 'nullable|array|max:20',
            'treatment_effectiveness.*' => 'nullable|string|in:Low,Medium,High',
        ];

        if ($includeStatus) {
            $rules['status'] = 'required|string|in:Open,Monitoring,Closed';
        }

        return $rules;
    }

    private function riskValidationMessages(): array
    {
        return [
            'risk_title.regex' => InputValidation::safeTextMessage(),
            'sasaran_target.regex' => InputValidation::safeTextMessage(),
            'risk_description.regex' => InputValidation::safeTextMessage(),
            'cause.regex' => InputValidation::safeTextMessage(),
            'existing_control.regex' => InputValidation::safeTextMessage(),
            'inherent_probability_description.regex' => InputValidation::safeTextMessage(),
            'inherent_impact_description.regex' => InputValidation::safeTextMessage(),
            'residual_probability_description.regex' => InputValidation::safeTextMessage(),
            'residual_impact_description.regex' => InputValidation::safeTextMessage(),
            'treatment_description.*.regex' => InputValidation::safeTextMessage(),
            'treatment_pic.*.regex' => InputValidation::safeTextMessage(),
            'treatment_blocked_reason.*.regex' => InputValidation::safeTextMessage(),
            'treatment_playbook_checklist.*.regex' => InputValidation::safeTextMessage(),
            'inherent_impact_amount.regex' => InputValidation::strictIntegerMessage(),
            'inherent_probability_percent.regex' => InputValidation::strictDecimalMessage(),
            'inherent_potential_loss.regex' => InputValidation::strictIntegerMessage(),
            'residual_impact_amount.regex' => InputValidation::strictIntegerMessage(),
            'residual_probability_percent.regex' => InputValidation::strictDecimalMessage(),
            'residual_potential_loss.regex' => InputValidation::strictIntegerMessage(),
            'radar_code.regex' => 'Radar code hanya boleh berisi huruf, angka, dan slash (/).',
            'status_change_reason.regex' => InputValidation::safeTextMessage(),
        ];
    }

    private function riskValidationAttributes(): array
    {
        return [
            'risk_title' => 'Process',
            'risk_appetite_id' => 'Corporate Risk Appetite',
            'sasaran_target' => 'Sasaran Target',
            'risk_description' => 'Risk Identification',
            'cause' => 'Cause',
            'existing_control' => 'Existing Control',
            'inherent_probability_description' => 'Inherent Probability Description',
            'inherent_impact_description' => 'Inherent Impact Description',
            'residual_probability_description' => 'Residual Probability Description',
            'residual_impact_description' => 'Residual Impact Description',
            'inherent_impact_amount' => 'Inherent Impact (Rp)',
            'inherent_probability_percent' => 'Inherent Probability (%)',
            'inherent_potential_loss' => 'Inherent Potential Loss (Rp)',
            'residual_impact_amount' => 'Residual Impact (Rp)',
            'residual_probability_percent' => 'Residual Probability (%)',
            'residual_potential_loss' => 'Residual Potential Loss (Rp)',
            'status_change_reason' => 'Status Change Reason',
            'treatment_template_id.*' => 'Mitigation Playbook',
            'treatment_description.*' => 'Treatment Description',
            'treatment_pic.*' => 'Treatment PIC',
            'treatment_next_action_at.*' => 'Next Action Date',
            'treatment_blocked_reason.*' => 'Blocked Reason',
            'treatment_playbook_checklist.*' => 'Playbook Checklist',
        ];
    }

    private function loadTreatmentTemplatesForUser()
    {
        $user = auth()->user();

        if (is_null($user)) {
            return collect();
        }

        return RiskTreatmentTemplate::query()
            ->visibleTo($user)
            ->where('is_active', true)
            ->orderBy('risk_owner')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'risk_owner',
                'option',
                'description',
                'default_pic',
                'default_effectiveness',
                'target_days',
                'next_action_offset_days',
                'checklist',
            ]);
    }

    private function resolveTreatmentTemplateMap(array $templateIds, string $riskOwner): array
    {
        $requestedIds = collect($templateIds)
            ->map(function ($value) {
                return (int) $value;
            })
            ->filter(function ($value) {
                return $value > 0;
            })
            ->unique()
            ->values();

        if ($requestedIds->isEmpty()) {
            return [];
        }

        $templates = RiskTreatmentTemplate::query()
            ->visibleTo(auth()->user())
            ->where('risk_owner', $riskOwner)
            ->where('is_active', true)
            ->whereIn('id', $requestedIds->all())
            ->get()
            ->keyBy('id');

        if ($templates->count() !== $requestedIds->count()) {
            throw ValidationException::withMessages([
                'treatment_template_id' => 'One or more selected mitigation playbooks are invalid for this risk owner.',
            ]);
        }

        return $templates->all();
    }

    private function ensureSelectableRiskAppetite(?int $riskAppetiteId, ?Risk $risk = null): void
    {
        if (is_null($riskAppetiteId) || $riskAppetiteId <= 0) {
            return;
        }

        $existingAppetite = \App\RiskAppetite::query()
            ->where('id', $riskAppetiteId)
            ->first();

        if (is_null($existingAppetite)) {
            throw ValidationException::withMessages([
                'risk_appetite_id' => 'The selected corporate risk appetite is no longer available.',
            ]);
        }

        if (!is_null($risk) && (int) $risk->risk_appetite_id === $riskAppetiteId) {
            return;
        }

        if (!$existingAppetite->is_active) {
            throw ValidationException::withMessages([
                'risk_appetite_id' => 'The selected corporate risk appetite is inactive and cannot be newly assigned.',
            ]);
        }
    }

    private function loadRiskActivityLogs(Risk $risk)
    {
        $auditableMap = $this->buildActivityAuditableMap($risk);
        if (empty($auditableMap)) {
            return collect();
        }

        $logs = AuditLog::with('actor')
            ->where(function ($query) use ($auditableMap) {
                foreach ($auditableMap as $auditableType => $ids) {
                    if (empty($ids)) {
                        continue;
                    }

                    $query->orWhere(function ($nested) use ($auditableType, $ids) {
                        $nested
                            ->where('auditable_type', $auditableType)
                            ->whereIn('auditable_id', $ids);
                    });
                }
            })
            ->orderByDesc('created_at')
            ->limit(150)
            ->get();

        return $logs->map(function (AuditLog $log) {
            return [
                'id' => (int) $log->id,
                'time' => optional($log->created_at)->format('d-m-Y H:i:s') ?? '-',
                'actor' => optional($log->actor)->name ?? 'System',
                'event_label' => $this->activityEventLabel((string) $log->event),
                'entity' => class_basename((string) $log->auditable_type) . '#' . (string) $log->auditable_id,
                'details' => $this->activityDetails($log),
            ];
        });
    }

    private function buildActivityAuditableMap(Risk $risk): array
    {
        $attachmentIds = collect()
            ->merge($risk->attachments->pluck('id'))
            ->merge($risk->treatments->flatMap(function ($treatment) {
                return $treatment->attachments->pluck('id');
            }))
            ->merge($risk->comments->flatMap(function ($comment) {
                return $comment->attachments->pluck('id');
            }))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $map = [
            Risk::class => [(int) $risk->id],
            RiskTreatment::class => $risk->treatments->pluck('id')->filter()->values()->all(),
            RiskStatusChangeRequest::class => $risk->statusChangeRequests->pluck('id')->filter()->values()->all(),
            RiskAlert::class => $risk->alerts->pluck('id')->filter()->values()->all(),
            RiskComment::class => $risk->comments->pluck('id')->filter()->values()->all(),
            EvidenceAttachment::class => $attachmentIds,
        ];

        $inherentId = optional($risk->inherentAnalysis)->id;
        if (!is_null($inherentId)) {
            $map[RiskInherentAnalysis::class] = [(int) $inherentId];
        }

        $residualId = optional($risk->residualAnalysis)->id;
        if (!is_null($residualId)) {
            $map[RiskResidualAnalysis::class] = [(int) $residualId];
        }

        return array_filter($map, function ($ids) {
            return !empty($ids);
        });
    }

    private function activityEventLabel(string $event): string
    {
        $labels = [
            'risk.created' => 'Risk Created',
            'risk.updated' => 'Risk Updated',
            'risk.deleted' => 'Risk Deleted',
            'risk.status_change.requested' => 'Status Change Requested',
            'risk.status_change.request.updated' => 'Status Change Request Updated',
            'risk.status_change.approved' => 'Status Change Approved',
            'risk.status_change.rejected' => 'Status Change Rejected',
            'risk.status.changed_by_approval' => 'Risk Status Updated by Approval',
            'risk.comment.created' => 'Comment Created',
            'risk.comment.updated' => 'Comment Updated',
            'risk.comment.deleted' => 'Comment Deleted',
            'evidence.risk.uploaded' => 'Risk Evidence Uploaded',
            'evidence.treatment.uploaded' => 'Treatment Evidence Uploaded',
            'evidence.comment.uploaded' => 'Comment Attachment Uploaded',
            'evidence.deleted' => 'Evidence Deleted',
            'evidence.deleted.by_comment_remove' => 'Comment Attachment Deleted',
            'evidence.deleted.by_risk_remove' => 'Risk Attachment Deleted',
            'evidence.deleted.by_treatment_remove' => 'Treatment Attachment Deleted',
            'risk.alert.resolved' => 'Risk Alert Resolved',
        ];

        return $labels[$event] ?? ucwords(str_replace(['.', '_'], ' ', $event));
    }

    private function activityDetails(AuditLog $log): string
    {
        $after = is_array($log->after_state) ? $log->after_state : [];
        $before = is_array($log->before_state) ? $log->before_state : [];

        if (isset($after['content'])) {
            return Str::limit((string) $after['content'], 140);
        }

        if (isset($before['content']) && (string) $before['content'] !== '') {
            return Str::limit((string) $before['content'], 140);
        }

        if (isset($after['original_name'])) {
            return 'File: ' . (string) $after['original_name'];
        }

        if (isset($before['original_name'])) {
            return 'File: ' . (string) $before['original_name'];
        }

        if (isset($after['from_status']) || isset($after['to_status'])) {
            return sprintf(
                '%s -> %s',
                (string) ($after['from_status'] ?? ($before['from_status'] ?? '-')),
                (string) ($after['to_status'] ?? ($before['to_status'] ?? '-'))
            );
        }

        if (isset($before['status']) && isset($after['status']) && (string) $before['status'] !== (string) $after['status']) {
            return 'Status: ' . (string) $before['status'] . ' -> ' . (string) $after['status'];
        }

        if (!empty($after)) {
            return 'Fields: ' . implode(', ', array_slice(array_keys($after), 0, 5));
        }

        if (!empty($before)) {
            return 'Fields: ' . implode(', ', array_slice(array_keys($before), 0, 5));
        }

        return '-';
    }

    private function buildIndexQuery(Request $request)
    {
        $query = Risk::with([
            'category',
            'causeCategory',
            'impactScale.category',
            'inherentAnalysis.impactScale',
            'residualAnalysis.impactScale',
            'residualAnalysis.radarGroup',
            'treatments',
            'creator',
        ])->withCount([
            'kris',
            'kris as kris_green_count' => function ($kriQuery) {
                $kriQuery->where('status', 'Green');
            },
            'kris as kris_amber_count' => function ($kriQuery) {
                $kriQuery->where('status', 'Amber');
            },
            'kris as kris_red_count' => function ($kriQuery) {
                $kriQuery->where('status', 'Red');
            },
        ]);
        RiskAccess::applyVisibilityScope($query, auth()->user());

        if ($request->risk_category_id) {
            $query->where('risk_category_id', $request->risk_category_id);
        }

        if ($request->risk_cause_category_id) {
            $query->where('risk_cause_category_id', $request->risk_cause_category_id);
        }

        if ($request->filled('year')) {
            $query->where('year', (int) $request->input('year'));
        }

        $selectedOwner = trim((string) $request->input('risk_owner', ''));
        if ($selectedOwner !== '') {
            $ownerValues = RiskOwnerAccess::filterValuesForDisplayLabel($selectedOwner);
            $query->whereIn('risk_owner', $ownerValues);
        }

        return $query;
    }

    private function visibleRiskOwnerOptions()
    {
        $query = Risk::query()
            ->select('risk_owner')
            ->whereNotNull('risk_owner')
            ->distinct();

        RiskAccess::applyVisibilityScope($query, auth()->user());

        return $query->pluck('risk_owner')
            ->map(function ($owner) {
                return RiskOwnerAccess::displayLabel($owner);
            })
            ->filter(function ($owner) {
                return $owner !== '-';
            })
            ->unique()
            ->sort(function ($left, $right) {
                return strnatcasecmp((string) $left, (string) $right);
            })
            ->values();
    }

    private function buildDashboardSummaryFromCollection($risks, string $trendGranularity = 'quarterly'): array
    {
        $totalRisks = (int) $risks->count();
        $trendGranularity = $this->normalizeTrendGranularity($trendGranularity);
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

        $trendRowsByGranularity = [
            'monthly' => $trendMonthlyRows,
            'quarterly' => $trendQuarterlyRows,
            'yearly' => $trendYearlyRows,
        ];

        $activeTrendRows = $trendRowsByGranularity[$trendGranularity] ?? $trendQuarterlyRows;

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
            'trend' => $activeTrendRows,
            'trend_monthly' => $trendMonthlyRows,
            'trend_quarterly' => $trendQuarterlyRows,
            'trend_yearly' => $trendYearlyRows,
            'trend_granularity' => $trendGranularity,
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

        $rows = collect($risks)->map(function ($risk) {
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

        return $rows;
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

    private function resolvePreferredTrendGranularity(Request $request): string
    {
        $requestValue = strtolower(trim((string) $request->input('trend_granularity', '')));
        if (in_array($requestValue, ['monthly', 'quarterly', 'yearly'], true)) {
            return $requestValue;
        }

        $userPreference = strtolower(trim((string) optional(optional(auth()->user())->dashboardPreference)->trend_granularity));
        if (in_array($userPreference, ['monthly', 'quarterly', 'yearly'], true)) {
            return $userPreference;
        }

        return 'quarterly';
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

    private function deleteAttachmentCollection($attachments, string $auditEvent): void
    {
        foreach ($attachments as $attachment) {
            $before = $attachment->toArray();

            if (!empty($attachment->storage_path) && Storage::disk('local')->exists($attachment->storage_path)) {
                Storage::disk('local')->delete($attachment->storage_path);
            }

            $attachment->delete();
            AuditLogger::logModelEvent($auditEvent, $attachment, $before, null);
        }
    }

    private function ensureCanManageRisk(Risk $risk): void
    {
        $user = auth()->user();
        $isCreator = !is_null($user) && (int) $risk->created_by > 0 && (int) $risk->created_by === (int) $user->id;
        $allowOwnerManage = !is_null($user) && $user->canManageRiskOwner($risk->risk_owner);

        abort_unless(
            !is_null($user) && !$user->isSuperadmin() && ($isCreator || $allowOwnerManage),
            403,
            'You are not allowed to modify this risk.'
        );
    }

    private function ensureRiskWriteAllowed(): void
    {
        $user = auth()->user();
        abort_if(
            !is_null($user) && $user->isSuperadmin(),
            403,
            'Superadmin is read-only for risk dashboard.'
        );
    }

    private function allowedRiskOwners(?string $includeCurrent = null): array
    {
        $user = auth()->user();
        if (is_null($user)) {
            return [];
        }

        return $user->allowedRiskOwners($includeCurrent);
    }
}

