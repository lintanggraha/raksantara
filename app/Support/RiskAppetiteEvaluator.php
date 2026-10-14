<?php

namespace App\Support;

use App\Risk;
use App\RiskAppetite;
use App\RiskQuantProfile;

class RiskAppetiteEvaluator
{
    public static function evaluate(Risk $risk): array
    {
        $risk->loadMissing(['riskAppetite', 'residualAnalysis.radarGroup.quantProfile']);

        $residual = $risk->residualAnalysis;
        $hasResidual = !is_null($residual);
        $residualScore = (int) (optional($residual)->residual_risk_score ?? 0);
        $residualLoss = (int) (optional($residual)->potential_loss ?? 0);

        // Override on the risk, else its category's appetite, else the default.
        $appetite = RiskAppetite::effectiveFor($risk);
        if ($appetite instanceof RiskAppetite) {
            $appetiteEvaluation = self::evaluateLinkedAppetite($appetite, $hasResidual, $residualScore, $residualLoss);
            if ($appetiteEvaluation['source'] !== 'fallback_required') {
                return $appetiteEvaluation;
            }
        }

        return self::evaluateFallbackQuantProfile(
            optional(optional($residual)->radarGroup)->quantProfile,
            $hasResidual,
            $residualScore,
            $residualLoss
        );
    }

    private static function evaluateLinkedAppetite(
        RiskAppetite $appetite,
        bool $hasResidual,
        int $residualScore,
        int $residualLoss
    ): array {
        $metric = $appetite->effectiveThresholdMetric();
        if ($metric === RiskAppetite::THRESHOLD_METRIC_QUALITATIVE) {
            return [
                'configured' => false,
                'source' => 'corporate_risk_appetite',
                'source_label' => 'Corporate Risk Appetite',
                'metric' => null,
                'metric_label' => $appetite->threshold_metric_label,
                'unit' => null,
                'current_value' => null,
                'warning_threshold' => null,
                'breach_threshold' => null,
                'status' => 'qualitative_only',
                'status_label' => 'Qualitative Only',
                'badge_class' => 'bg-secondary-lt',
                'message' => 'Linked appetite is qualitative only, so automated threshold comparison is not applied.',
            ];
        }

        if (!$appetite->isQuantitative()) {
            return [
                'configured' => false,
                'source' => 'fallback_required',
                'source_label' => 'Fallback Required',
                'metric' => null,
                'metric_label' => null,
                'unit' => null,
                'current_value' => null,
                'warning_threshold' => null,
                'breach_threshold' => null,
                'status' => 'unconfigured',
                'status_label' => 'Unconfigured',
                'badge_class' => 'bg-secondary-lt',
                'message' => 'Linked appetite is not ready for quantitative threshold evaluation.',
            ];
        }

        if (!$hasResidual) {
            return [
                'configured' => true,
                'source' => 'corporate_risk_appetite',
                'source_label' => 'Corporate Risk Appetite',
                'metric' => $metric,
                'metric_label' => self::metricLabel($metric),
                'unit' => self::unitForMetric($metric, $appetite->unit),
                'current_value' => null,
                'warning_threshold' => $appetite->appetite_limit_value,
                'breach_threshold' => $appetite->tolerance_limit_value,
                'status' => 'unconfigured',
                'status_label' => 'Awaiting Residual Analysis',
                'badge_class' => 'bg-secondary-lt',
                'message' => 'Residual analysis is not available yet, so appetite threshold comparison cannot run.',
            ];
        }

        $currentValue = $metric === RiskAppetite::THRESHOLD_METRIC_RESIDUAL_SCORE
            ? $residualScore
            : $residualLoss;

        $warningThreshold = !is_null($appetite->appetite_limit_value)
            ? (float) $appetite->appetite_limit_value
            : null;
        $breachThreshold = !is_null($appetite->tolerance_limit_value)
            ? (float) $appetite->tolerance_limit_value
            : $warningThreshold;

        if (is_null($warningThreshold) && is_null($breachThreshold)) {
            return [
                'configured' => false,
                'source' => 'fallback_required',
                'source_label' => 'Fallback Required',
                'metric' => null,
                'metric_label' => null,
                'unit' => null,
                'current_value' => null,
                'warning_threshold' => null,
                'breach_threshold' => null,
                'status' => 'unconfigured',
                'status_label' => 'Unconfigured',
                'badge_class' => 'bg-secondary-lt',
                'message' => 'Linked appetite does not have usable quantitative thresholds yet.',
            ];
        }

        $status = 'within';
        $statusLabel = 'Within Appetite';
        $badgeClass = 'bg-success-lt';
        $message = sprintf(
            'Current %s is within the linked corporate appetite.',
            self::metricLabel($metric)
        );

        if (!is_null($breachThreshold) && $currentValue > $breachThreshold) {
            $status = 'breach';
            $statusLabel = 'Tolerance Breached';
            $badgeClass = 'bg-danger-lt';
            $message = sprintf(
                'Current %s breached the linked corporate tolerance limit.',
                self::metricLabel($metric)
            );
        } elseif (!is_null($warningThreshold) && $currentValue > $warningThreshold) {
            $status = 'warning';
            $statusLabel = 'Above Appetite Target';
            $badgeClass = 'bg-warning-lt';
            $message = sprintf(
                'Current %s is above the linked appetite target but still within the maximum tolerance.',
                self::metricLabel($metric)
            );
        }

        return [
            'configured' => true,
            'source' => 'corporate_risk_appetite',
            'source_label' => 'Corporate Risk Appetite',
            'metric' => $metric,
            'metric_label' => self::metricLabel($metric),
            'unit' => self::unitForMetric($metric, $appetite->unit),
            'current_value' => $currentValue,
            'warning_threshold' => $warningThreshold,
            'breach_threshold' => $breachThreshold,
            'status' => $status,
            'status_label' => $statusLabel,
            'badge_class' => $badgeClass,
            'message' => $message,
        ];
    }

    private static function evaluateFallbackQuantProfile(
        ?RiskQuantProfile $quantProfile,
        bool $hasResidual,
        int $residualScore,
        int $residualLoss
    ): array {
        $thresholdScore = !is_null(optional($quantProfile)->risk_appetite_score)
            ? (float) optional($quantProfile)->risk_appetite_score
            : null;
        $thresholdLoss = !is_null(optional($quantProfile)->risk_appetite_loss)
            ? (float) optional($quantProfile)->risk_appetite_loss
            : null;

        if (is_null($thresholdScore) && is_null($thresholdLoss)) {
            return [
                'configured' => false,
                'source' => 'none',
                'source_label' => 'No Threshold Source',
                'metric' => null,
                'metric_label' => null,
                'unit' => null,
                'current_value' => null,
                'warning_threshold' => null,
                'breach_threshold' => null,
                'status' => 'unconfigured',
                'status_label' => 'Unconfigured',
                'badge_class' => 'bg-secondary-lt',
                'message' => 'No linked corporate appetite or fallback radar quant profile threshold is configured for this risk.',
            ];
        }

        if (!$hasResidual) {
            return [
                'configured' => true,
                'source' => 'radar_quant_profile',
                'source_label' => 'Radar Quant Profile Fallback',
                'metric' => !is_null($thresholdScore)
                    ? RiskAppetite::THRESHOLD_METRIC_RESIDUAL_SCORE
                    : RiskAppetite::THRESHOLD_METRIC_RESIDUAL_LOSS,
                'metric_label' => !is_null($thresholdScore)
                    ? self::metricLabel(RiskAppetite::THRESHOLD_METRIC_RESIDUAL_SCORE)
                    : self::metricLabel(RiskAppetite::THRESHOLD_METRIC_RESIDUAL_LOSS),
                'unit' => !is_null($thresholdScore) ? 'Score' : null,
                'current_value' => null,
                'warning_threshold' => null,
                'breach_threshold' => !is_null($thresholdScore) ? $thresholdScore : $thresholdLoss,
                'status' => 'unconfigured',
                'status_label' => 'Awaiting Residual Analysis',
                'badge_class' => 'bg-secondary-lt',
                'message' => 'Fallback threshold exists, but residual analysis is not available yet.',
            ];
        }

        $scoreBreached = !is_null($thresholdScore) && $residualScore > $thresholdScore;
        $lossBreached = !is_null($thresholdLoss) && $residualLoss > $thresholdLoss;

        $metric = RiskAppetite::THRESHOLD_METRIC_RESIDUAL_SCORE;
        $currentValue = $residualScore;
        $breachThreshold = $thresholdScore;
        $unit = 'Score';

        if ($lossBreached || (is_null($thresholdScore) && !is_null($thresholdLoss))) {
            $metric = RiskAppetite::THRESHOLD_METRIC_RESIDUAL_LOSS;
            $currentValue = $residualLoss;
            $breachThreshold = $thresholdLoss;
            $unit = null;
        }

        $status = ($scoreBreached || $lossBreached) ? 'breach' : 'within';
        $statusLabel = ($scoreBreached || $lossBreached) ? 'Fallback Threshold Breached' : 'Within Fallback Threshold';
        $badgeClass = ($scoreBreached || $lossBreached) ? 'bg-danger-lt' : 'bg-success-lt';
        $message = ($scoreBreached || $lossBreached)
            ? sprintf(
                'Current %s exceeded the fallback radar quant profile threshold.',
                self::metricLabel($metric)
            )
            : sprintf(
                'Current %s is still within the fallback radar quant profile threshold.',
                self::metricLabel($metric)
            );

        return [
            'configured' => true,
            'source' => 'radar_quant_profile',
            'source_label' => 'Radar Quant Profile Fallback',
            'metric' => $metric,
            'metric_label' => self::metricLabel($metric),
            'unit' => $unit,
            'current_value' => $currentValue,
            'warning_threshold' => null,
            'breach_threshold' => $breachThreshold,
            'status' => $status,
            'status_label' => $statusLabel,
            'badge_class' => $badgeClass,
            'message' => $message,
        ];
    }

    private static function metricLabel(?string $metric): ?string
    {
        return match ($metric) {
            RiskAppetite::THRESHOLD_METRIC_RESIDUAL_SCORE => 'Residual Risk Score',
            RiskAppetite::THRESHOLD_METRIC_RESIDUAL_LOSS => 'Residual Potential Loss',
            default => null,
        };
    }

    private static function unitForMetric(string $metric, ?string $unit): ?string
    {
        if ($metric === RiskAppetite::THRESHOLD_METRIC_RESIDUAL_SCORE) {
            return 'Score';
        }

        $trimmedUnit = trim((string) $unit);

        return $trimmedUnit !== '' ? $trimmedUnit : null;
    }
}
