<?php

namespace App\Support;

use App\Risk;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Single Source of Truth for Enterprise Risk Metrics, Scoring Thresholds,
 * Priority Classifications, and SLA Computations across all Dashboards and Reports.
 */
class RiskMetricService
{
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MODERATE = 'moderate';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_EXTREME = 'extreme';

    public const THRESHOLD_LOW_MAX = 5.0;
    public const THRESHOLD_MODERATE_MAX = 10.0;
    public const THRESHOLD_HIGH_MAX = 15.0;

    public const COLOR_LOW = '#4caf50';
    public const COLOR_MODERATE = '#facc15';
    public const COLOR_HIGH = '#fb8c00';
    public const COLOR_EXTREME = '#ef4444';

    public const DEFAULT_SLA_TARGET_PERCENT = 95.0;
    public const DEFAULT_RESPONSE_SLA_DAYS = 7;

    /**
     * Resolves active display score for a risk (Residual -> Inherent -> Base).
     */
    public static function resolveDisplayScore(Risk $risk): float
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

    /**
     * Maps a numerical score (1-25) to a priority bucket.
     */
    public static function resolvePriorityBucket($score): ?string
    {
        if (!is_numeric($score)) {
            return null;
        }

        $numeric = (float) $score;
        if ($numeric <= self::THRESHOLD_LOW_MAX) {
            return self::PRIORITY_LOW;
        }

        if ($numeric <= self::THRESHOLD_MODERATE_MAX) {
            return self::PRIORITY_MODERATE;
        }

        if ($numeric <= self::THRESHOLD_HIGH_MAX) {
            return self::PRIORITY_HIGH;
        }

        return self::PRIORITY_EXTREME;
    }

    /**
     * Returns human-readable label for a priority bucket.
     */
    public static function resolvePriorityLabel(?string $bucket): string
    {
        $map = [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MODERATE => 'Moderate',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_EXTREME => 'Extreme',
        ];

        $normalized = strtolower(trim((string) $bucket));

        return $map[$normalized] ?? '-';
    }

    /**
     * Evaluates whether a risk score falls into high attention territory.
     * When $strict is false, covers High and Extreme (score >= 11).
     * When $strict is true, covers Extreme only (score >= 16, legacy threshold).
     */
    public static function isHighRisk($scoreOrRisk, bool $strict = false): bool
    {
        $score = is_numeric($scoreOrRisk)
            ? (float) $scoreOrRisk
            : self::resolveDisplayScore($scoreOrRisk);

        return $strict ? $score >= 16.0 : $score > self::THRESHOLD_MODERATE_MAX;
    }


    /**
     * Standard color map for priority buckets.
     */
    public static function priorityColorMap(): array
    {
        return [
            self::PRIORITY_LOW => self::COLOR_LOW,
            self::PRIORITY_MODERATE => self::COLOR_MODERATE,
            self::PRIORITY_HIGH => self::COLOR_HIGH,
            self::PRIORITY_EXTREME => self::COLOR_EXTREME,
        ];
    }

    /**
     * Resolves hex color code for a priority bucket.
     */
    public static function resolvePriorityColor(?string $bucket): string
    {
        $map = self::priorityColorMap();
        $normalized = strtolower(trim((string) $bucket));

        return $map[$normalized] ?? '#94a3b8';
    }


    /**
     * Builds standard priority distribution summary rows.
     */
    public static function buildPrioritySummary($risks): array
    {
        $bucketCounts = [
            self::PRIORITY_LOW => 0,
            self::PRIORITY_MODERATE => 0,
            self::PRIORITY_HIGH => 0,
            self::PRIORITY_EXTREME => 0,
        ];

        foreach ($risks as $risk) {
            $bucket = self::resolvePriorityBucket(self::resolveDisplayScore($risk));
            if (is_null($bucket)) {
                continue;
            }

            $bucketCounts[$bucket] = (int) ($bucketCounts[$bucket] + 1);
        }

        return [
            ['label' => 'Low', 'count' => (int) $bucketCounts[self::PRIORITY_LOW]],
            ['label' => 'Moderate', 'count' => (int) $bucketCounts[self::PRIORITY_MODERATE]],
            ['label' => 'High', 'count' => (int) $bucketCounts[self::PRIORITY_HIGH]],
            ['label' => 'Extreme', 'count' => (int) $bucketCounts[self::PRIORITY_EXTREME]],
        ];
    }

    /**
     * Builds top risk rows ranked by display score.
     */
    public static function buildTopRiskRows($risks, int $limit = 10): array
    {
        $limit = max(1, $limit);

        return collect($risks)
            ->map(function ($risk) {
                $riskValue = self::resolveDisplayScore($risk);
                $priorityBucket = self::resolvePriorityBucket($riskValue) ?: self::PRIORITY_LOW;
                $priorityLabel = self::resolvePriorityLabel($priorityBucket);

                $residualImpact = optional($risk->residualAnalysis)->impact_score;
                $inherentImpact = optional($risk->inherentAnalysis)->impact_score;
                $baseImpact = $risk->impact_score;
                $impact = is_numeric($residualImpact)
                    ? (int) $residualImpact
                    : (is_numeric($inherentImpact) ? (int) $inherentImpact : (is_numeric($baseImpact) ? (int) $baseImpact : null));

                $residualLikelihood = optional($risk->residualAnalysis)->probability_score;
                $inherentLikelihood = optional($risk->inherentAnalysis)->probability_score;
                $baseLikelihood = $risk->probability_score;
                $likelihood = is_numeric($residualLikelihood)
                    ? (int) $residualLikelihood
                    : (is_numeric($inherentLikelihood) ? (int) $inherentLikelihood : (is_numeric($baseLikelihood) ? (int) $baseLikelihood : null));

                $firstTreatment = collect($risk->treatments ?? [])->first();
                $controlFocus = trim((string) (optional($firstTreatment)->description ?? $risk->existing_control ?? '-'));
                if ($controlFocus === '') {
                    $controlFocus = '-';
                }

                return [
                    'id' => (int) ($risk->id ?? 0),
                    'risk_id' => (string) ($risk->risk_id ?? '-'),
                    'risk_title' => (string) ($risk->risk_title ?? '-'),
                    'description' => (string) ($risk->risk_description ?? '-'),
                    'priority_bucket' => $priorityBucket,
                    'priority_label' => $priorityLabel,
                    'risk_value' => $riskValue,
                    'division' => RiskOwnerAccess::displayLabel($risk->risk_owner),
                    'owner' => (string) ($risk->risk_owner ?? '-'),
                    'category' => (string) (optional($risk->category)->name ?? '-'),
                    'likelihood' => $likelihood,
                    'impact' => $impact,
                    'control_focus' => $controlFocus,
                ];
            })
            ->sortByDesc('risk_value')
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * Builds division distribution summary rows.
     */
    public static function buildDivisionSummary($risks, int $limit = 6): array
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

    /**
     * Builds treatment timeline status counts.
     */
    public static function buildTreatmentTimelineSummary($risks): array
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
            $status = strtolower(trim((string) (optional($treatment)->status ?? '')));
            if ($status === 'closed') {
                continue;
            }

            $rawDeadline = optional($treatment)->deadline;
            if (empty($rawDeadline)) {
                $counts['no_deadline'] = (int) ($counts['no_deadline'] + 1);
                continue;
            }

            try {
                $deadlineDate = Carbon::parse($rawDeadline)->startOfDay();
            } catch (\Throwable $e) {
                $counts['no_deadline'] = (int) ($counts['no_deadline'] + 1);
                continue;
            }

            if ($deadlineDate->lt($today)) {
                $counts['overdue'] = (int) ($counts['overdue'] + 1);
            } elseif ($deadlineDate->lte($dueSoonThreshold)) {
                $counts['due_soon'] = (int) ($counts['due_soon'] + 1);
            } else {
                $counts['on_track'] = (int) ($counts['on_track'] + 1);
            }
        }

        return [
            ['label' => 'Overdue', 'count' => (int) $counts['overdue']],
            ['label' => 'Due Soon', 'count' => (int) $counts['due_soon']],
            ['label' => 'On Track', 'count' => (int) $counts['on_track']],
            ['label' => 'No Deadline', 'count' => (int) $counts['no_deadline']],
        ];
    }

    /**
     * Builds SLA metrics summary from risks and treatments.
     */
    public static function buildSlaSummary($risks, float $targetPercent = self::DEFAULT_SLA_TARGET_PERCENT): array
    {
        $allTreatments = collect($risks)->flatMap(function ($risk) {
            return collect($risk->treatments ?? [])->values();
        })->values();

        $today = Carbon::today();
        $dueSoonThreshold = (clone $today)->addDays(14);

        $dueCount = (int) $allTreatments->filter(function ($treatment) use ($dueSoonThreshold) {
            $status = strtolower(trim((string) (optional($treatment)->status ?? '')));
            if ($status === 'closed') {
                return true;
            }

            $rawDeadline = optional($treatment)->deadline;
            if (empty($rawDeadline)) {
                return false;
            }

            try {
                return Carbon::parse($rawDeadline)->startOfDay()->lte($dueSoonThreshold);
            } catch (\Throwable $e) {
                return false;
            }
        })->count();

        $compliantCount = (int) $allTreatments->filter(function ($treatment) use ($today, $dueSoonThreshold) {
            $status = strtolower(trim((string) (optional($treatment)->status ?? '')));
            if ($status === 'closed') {
                return true;
            }

            $rawDeadline = optional($treatment)->deadline;
            if (empty($rawDeadline)) {
                return false;
            }

            try {
                $deadlineDate = Carbon::parse($rawDeadline)->startOfDay();

                return $deadlineDate->gte($today) && $deadlineDate->lte($dueSoonThreshold);
            } catch (\Throwable $e) {
                return false;
            }
        })->count();

        $overdueCount = max(0, $dueCount - $compliantCount);

        $actualPercent = $dueCount > 0
            ? round(($compliantCount / $dueCount) * 100, 2)
            : 100.0;

        return [
            'target_percent' => $targetPercent,
            'actual_percent' => $actualPercent,
            'due_count' => $dueCount,
            'compliant_count' => $compliantCount,
            'overdue_count' => $overdueCount,
        ];
    }

    /**
     * Calculates benchmark scoring against national/industry references.
     */
    public static function buildBenchmarkScoring(float $overallAverage): array
    {
        $targetScore = (float) config('risk_benchmark.target_score', 8.5);
        $toleranceRange = (float) config('risk_benchmark.tolerance_range', 1.0);
        $sourceReference = (string) config('risk_benchmark.source_reference', 'OJK & ISO 31000 Risk Governance Guidelines');
        $minTarget = max(1.0, $targetScore - $toleranceRange);
        $maxTarget = $targetScore + $toleranceRange;
        $gap = round($overallAverage - $targetScore, 2);

        if ($overallAverage < $minTarget) {
            $status = 'Below Target';
            $statusClass = 'text-success';
        } elseif ($overallAverage > $maxTarget) {
            $status = 'Above Tolerance';
            $statusClass = 'text-danger';
        } else {
            $status = 'Within Tolerance';
            $statusClass = 'text-warning';
        }

        return [
            'target_score' => round($targetScore, 2),
            'current_score' => round($overallAverage, 2),
            'gap' => $gap,
            'status' => $status,
            'status_class' => $statusClass,
            'source_reference' => $sourceReference,
        ];
    }

    /**
     * Computes the operational KPI metrics dashboard.
     */
    public static function buildKpiDashboard(
        int $totalRisks,
        int $closedCount,
        float $mitigationEffectivenessPercent,
        float $riskCoveragePercent,
        int $dueCount,
        int $overdueCount,
        array $responseTimeByRiskId,
        int $responseSlaDays = self::DEFAULT_RESPONSE_SLA_DAYS
    ): array {
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

    /**
     * Computes response time (in days) from risk creation to first treatment.
     */
    public static function buildResponseTimeByRiskId($risks): array
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

    /**
     * Builds department performance rankings.
     */
    public static function buildDepartmentPerformance($risks, array $responseTimeByRiskId): array
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

            $avgResidualScore = self::averageFromCollection($items, function ($risk) {
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
                $inherentBucket = self::resolvePriorityBucket(optional($risk->inherentAnalysis)->inherent_risk_score);
                if (!is_null($inherentBucket)) {
                    $inherentPriority[$inherentBucket] = (int) ($inherentPriority[$inherentBucket] + 1);
                }

                $residualBucket = self::resolvePriorityBucket(optional($risk->residualAnalysis)->residual_risk_score);
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
            $effectivenessScore = self::averageFromCollection($treatments, function ($treatment) {
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
                'average_score' => $avgResidualScore,
                'coverage_percent' => $coveragePercent,
                'avg_response_time_days' => $avgResponseTimeDays,
                'response_within_sla_percent' => $responseWithinSlaPercent,
                'risk_reduction_percent' => $riskReductionPercent,
                'activity_count' => $activityCount,
                'due_count' => $dueCount,
                'overdue_count' => $overdueCount,
                'effectiveness_score' => $effectivenessScore,
                'effectiveness_percent' => $effectivenessPercent,
                'performance_score' => $performanceScore,
                'inherent_priority' => $inherentPriority,
                'residual_priority' => $residualPriority,
            ];
        })->values();

        $rankedRows = $rows->sortByDesc('performance_score')->values()->map(function ($row, $index) {
            $row['rank'] = $index + 1;

            return $row;
        })->all();

        $topDivision = (string) (collect($rankedRows)->first()['division'] ?? '-');
        $averageScore = $rows->isEmpty()
            ? 0.0
            : round((float) $rows->avg('average_score'), 2);
        $totalRisks = (int) $rows->sum('risk_count');

        return [
            'rows' => $rankedRows,
            'top_division' => $topDivision,
            'average_score' => $averageScore,
            'total_risks' => $totalRisks,
            'division_count' => (int) count($rankedRows),
        ];
    }

    /**
     * Builds root cause keyword and category patterns.
     */
    public static function buildRootCauseAnalysis($risks, float $overallAverage): array
    {
        $risksCollection = collect($risks)->values();
        $totalRisks = (int) $risksCollection->count();

        $categoryGroups = $risksCollection->groupBy(function ($risk) {
            return (string) (optional($risk->causeCategory)->name ?? '-');
        });

        $topCategories = $categoryGroups->map(function ($items, $categoryName) use ($totalRisks) {
            $items = collect($items)->values();
            $count = (int) $items->count();
            $percentage = $totalRisks > 0 ? round(($count / $totalRisks) * 100, 2) : 0.0;
            $avgResidualScore = self::averageFromCollection($items, function ($risk) {
                return optional($risk->residualAnalysis)->residual_risk_score;
            });

            return [
                'name' => (string) $categoryName,
                'count' => $count,
                'percentage' => $percentage,
                'average_residual_score' => $avgResidualScore,
            ];
        })->sortByDesc('count')->values()->all();

        $stopWords = [
            'dan', 'di', 'ke', 'dari', 'yang', 'untuk', 'pada', 'dengan', 'adalah', 'ini',
            'itu', 'atau', 'karena', 'oleh', 'saat', 'tidak', 'dalam', 'akan', 'juga', 'ada',
            'tersebut', 'dapat', 'secara', 'harus', 'agar', 'bisa', 'sebagai', 'lebih',
            'the', 'and', 'in', 'of', 'to', 'for', 'with', 'on', 'at', 'by', 'from', 'is',
            'are', 'was', 'were', 'an', 'a', 'or', 'as', 'be', 'it', 'that', 'this',
        ];

        $wordCounts = [];
        $wordRiskCounts = [];
        $wordResidualSums = [];

        foreach ($risksCollection as $risk) {
            $causeText = strtolower(trim((string) ($risk->cause ?? '')));
            if ($causeText === '') {
                continue;
            }

            $tokens = preg_split('/[\s,\.\/\-_:;\(\)\[\]\{\}\\\\]+/', $causeText);
            if (!is_array($tokens)) {
                continue;
            }

            $uniqueWordsInRisk = [];
            $residualScore = optional($risk->residualAnalysis)->residual_risk_score;
            $numericResidual = is_numeric($residualScore) ? (float) $residualScore : null;

            foreach ($tokens as $token) {
                $word = trim($token);
                if (strlen($word) < 4 || in_array($word, $stopWords, true)) {
                    continue;
                }

                $wordCounts[$word] = (int) (($wordCounts[$word] ?? 0) + 1);
                $uniqueWordsInRisk[$word] = true;
            }

            foreach (array_keys($uniqueWordsInRisk) as $uniqueWord) {
                $wordRiskCounts[$uniqueWord] = (int) (($wordRiskCounts[$uniqueWord] ?? 0) + 1);
                if (!is_null($numericResidual)) {
                    if (!isset($wordResidualSums[$uniqueWord])) {
                        $wordResidualSums[$uniqueWord] = ['sum' => 0.0, 'count' => 0];
                    }
                    $wordResidualSums[$uniqueWord]['sum'] += $numericResidual;
                    $wordResidualSums[$uniqueWord]['count'] += 1;
                }
            }
        }

        $topKeywords = collect($wordCounts)
            ->map(function ($frequency, $keyword) use ($wordRiskCounts, $wordResidualSums) {
                $riskCount = (int) ($wordRiskCounts[$keyword] ?? 0);
                $residualData = $wordResidualSums[$keyword] ?? null;
                $avgScore = ($residualData && $residualData['count'] > 0)
                    ? round($residualData['sum'] / $residualData['count'], 2)
                    : 0.0;

                return [
                    'keyword' => (string) $keyword,
                    'frequency' => (int) $frequency,
                    'risk_count' => $riskCount,
                    'average_residual_score' => $avgScore,
                ];
            })
            ->sortByDesc('frequency')
            ->take(12)
            ->values()
            ->all();

        $patterns = [];
        foreach (array_slice($topCategories, 0, 3) as $categoryRow) {
            $categoryName = (string) ($categoryRow['name'] ?? '-');
            $categoryPercentage = (float) ($categoryRow['percentage'] ?? 0.0);
            $categoryAvgScore = (float) ($categoryRow['average_residual_score'] ?? 0.0);

            $relatedKeywords = collect($topKeywords)
                ->filter(function ($kw) use ($categoryName) {
                    return stripos($categoryName, $kw['keyword']) !== false;
                })
                ->pluck('keyword')
                ->all();

            $patterns[] = [
                'pattern' => "Konsentrasi akar penyebab pada '{$categoryName}' ({$categoryPercentage}% dari total risiko).",
                'severity' => $categoryAvgScore > $overallAverage ? 'High Impact' : 'Standard',
                'severity_class' => $categoryAvgScore > $overallAverage ? 'badge-danger' : 'badge-secondary',
                'description' => "Rata-rata skor residual kategori ini adalah {$categoryAvgScore}, dibanding rata-rata global {$overallAverage}.",
                'related_keywords' => $relatedKeywords,
            ];
        }

        return [
            'top_categories' => $topCategories,
            'top_keywords' => $topKeywords,
            'patterns' => $patterns,
            'analyzed_risk_count' => $totalRisks,
        ];
    }

    /**
     * Builds inherent vs residual variance rows grouped by category.
     */
    public static function buildVarianceRows($risks): array
    {
        return collect($risks)->groupBy(function ($risk) {
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
                'risk_count' => (int) collect($items)->count(),
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


    /**
     * Helper to group a collection and return sorted count rows.
     */
    public static function buildGroupedCountRows($grouped): array
    {
        return collect($grouped)
            ->map(function ($items, $label) {
                return [
                    'label' => (string) $label,
                    'count' => (int) collect($items)->count(),
                ];
            })
            ->sortByDesc('count')
            ->values()
            ->all();
    }

    /**
     * Builds complete comprehensive dashboard summary structure from a collection of risks.
     */
    public static function buildFullSummary(
        $risks,
        string $trendGranularity = 'quarterly',
        array $activeTrendRows = [],
        array $trendMonthlyRows = [],
        array $trendQuarterlyRows = [],
        array $trendYearlyRows = []
    ): array {
        $risksCollection = collect($risks)->values();
        $totalRisks = (int) $risksCollection->count();
        $trendGranularity = self::normalizeTrendGranularity($trendGranularity);

        if (empty($trendMonthlyRows)) {
            $trendMonthlyRows = self::buildTrendRows($risksCollection, 'monthly');
        }
        if (empty($trendQuarterlyRows)) {
            $trendQuarterlyRows = self::buildTrendRows($risksCollection, 'quarterly');
        }
        if (empty($trendYearlyRows)) {
            $trendYearlyRows = self::buildTrendRows($risksCollection, 'yearly');
        }
        if (empty($activeTrendRows)) {
            $trendMap = [
                'monthly' => $trendMonthlyRows,
                'quarterly' => $trendQuarterlyRows,
                'yearly' => $trendYearlyRows,
            ];
            $activeTrendRows = $trendMap[$trendGranularity] ?? $trendQuarterlyRows;
        }

        $responseTimeByRiskId = self::buildResponseTimeByRiskId($risksCollection);

        $statusSummary = self::buildGroupedCountRows($risksCollection->groupBy(function ($risk) {
            $status = trim((string) ($risk->status ?? 'Unknown'));

            return $status !== '' ? $status : 'Unknown';
        }));


        $categorySummary = self::buildGroupedCountRows($risksCollection->groupBy(function ($risk) {
            $category = trim((string) (optional($risk->category)->name ?? '-'));

            return $category !== '' ? $category : '-';
        }));

        $ownerSummary = self::buildGroupedCountRows($risksCollection->groupBy(function ($risk) {
            return RiskOwnerAccess::displayLabel($risk->risk_owner);
        }));

        $statusSummaryCollection = collect($statusSummary)->keyBy('label');
        $openCount = (int) ($statusSummaryCollection->get('Open')['count'] ?? 0);
        $closedCount = (int) ($statusSummaryCollection->get('Closed')['count'] ?? 0);
        $monitoringCount = (int) ($statusSummaryCollection->get('Monitoring')['count'] ?? 0);

        $inherentAverage = self::averageFromCollection($risksCollection, function ($risk) {
            return optional($risk->inherentAnalysis)->inherent_risk_score;
        });
        $residualAverage = self::averageFromCollection($risksCollection, function ($risk) {
            return optional($risk->residualAnalysis)->residual_risk_score;
        });
        $overallAverage = self::averageFromCollection($risksCollection, function ($risk) {
            $residual = optional($risk->residualAnalysis)->residual_risk_score;
            if (is_numeric($residual)) {
                return (float) $residual;
            }

            $inherent = optional($risk->inherentAnalysis)->inherent_risk_score;

            return is_numeric($inherent) ? (float) $inherent : null;
        });

        $treatments = $risksCollection->flatMap(function ($risk) {
            return collect($risk->treatments ?? [])->map(function ($treatment) use ($risk) {
                return [
                    'risk_id' => (int) $risk->id,
                    'deadline' => $treatment->deadline,
                    'effectiveness_score' => $treatment->effectiveness_score,
                ];
            });
        })->values();

        $treatmentCount = (int) $treatments->count();
        $effectivenessAverageScore = self::averageFromCollection($treatments, function ($row) {
            return $row['effectiveness_score'] ?? null;
        });
        $mitigationEffectivenessPercent = round($effectivenessAverageScore * 100, 2);
        $coveredRiskCount = (int) $treatments->pluck('risk_id')->unique()->count();
        $riskCoveragePercent = $totalRisks > 0
            ? round(($coveredRiskCount / $totalRisks) * 100, 2)
            : 0.0;

        $sla = self::buildSlaSummary($risksCollection);
        $dueCount = (int) ($sla['due_count'] ?? 0);
        $overdueCount = (int) ($sla['overdue_count'] ?? 0);

        $varianceRows = self::buildVarianceRows($risksCollection);
        $globalVariance = round($inherentAverage - $residualAverage, 2);
        $benchmarkScoring = self::buildBenchmarkScoring($overallAverage);
        $kpiDashboard = self::buildKpiDashboard(
            $totalRisks,
            $closedCount,
            $mitigationEffectivenessPercent,
            $riskCoveragePercent,
            $dueCount,
            $overdueCount,
            $responseTimeByRiskId
        );
        $departmentPerformance = self::buildDepartmentPerformance($risksCollection, $responseTimeByRiskId);
        $rootCauseAnalysis = self::buildRootCauseAnalysis($risksCollection, $overallAverage);
        $portfolioOverview = [
            'by_priority' => self::buildPrioritySummary($risksCollection),
            'by_category' => array_slice($categorySummary, 0, 6),
            'by_division' => self::buildDivisionSummary($risksCollection, 6),
            'treatment_timeline' => self::buildTreatmentTimelineSummary($risksCollection),
            'risk_status' => $statusSummary,
            'top_risks' => self::buildTopRiskRows($risksCollection, 10),
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
            'sla' => $sla,
            'variance' => [
                'global' => $globalVariance,
                'rows' => $varianceRows,
            ],
        ];
    }

    /**
     * Builds temporal trend rows for inherent, residual, and overall risk scores.
     */
    public static function buildTrendRows($risks, string $granularity = 'monthly'): array
    {
        $granularity = self::normalizeTrendGranularity($granularity);

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
                'label' => self::buildTrendLabel($period, $granularity),
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

    public static function normalizeTrendGranularity(string $granularity): string
    {
        $normalized = strtolower(trim($granularity));
        if (!in_array($normalized, ['monthly', 'quarterly', 'yearly'], true)) {
            return 'quarterly';
        }

        return $normalized;
    }

    public static function buildTrendLabel(Carbon $period, string $granularity): string
    {
        if ($granularity === 'monthly') {
            return $period->format('M Y');
        }

        if ($granularity === 'quarterly') {
            return 'Q' . $period->quarter . ' ' . $period->format('Y');
        }

        return $period->format('Y');
    }

    private static function averageFromCollection(Collection $items, callable $resolver): float
    {
        $values = $items->map($resolver)->filter(function ($value) {
            return is_numeric($value);
        })->map(function ($value) {
            return (float) $value;
        })->values();

        if ($values->isEmpty()) {
            return 0.0;
        }

        return round((float) $values->avg(), 2);
    }
}

