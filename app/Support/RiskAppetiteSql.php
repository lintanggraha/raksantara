<?php

namespace App\Support;

use App\RiskAppetite;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * SQL port of RiskAppetiteEvaluator::evaluate() so appetite status can be
 * counted and filtered in the database instead of risk by risk in PHP.
 *
 * The expression expects these aliases on the query:
 *   risks (r), risk_residual_analyses AS rr, risk_appetites AS ra,
 *   risk_quant_profiles AS qp (see joinSources()).
 *
 * Result values: breach, warning, within, qualitative, unconfigured.
 */
class RiskAppetiteSql
{
    public const BREACH = 'breach';
    public const WARNING = 'warning';
    public const WITHIN = 'within';
    public const QUALITATIVE = 'qualitative';
    public const UNCONFIGURED = 'unconfigured';

    public static function joinSources(Builder $query): Builder
    {
        return $query
            ->leftJoin('risk_residual_analyses as rr', 'rr.risk_id', '=', 'risks.id')
            ->leftJoin('risk_appetites as ra', function ($join) {
                $join->on('ra.id', '=', DB::raw(self::effectiveIdExpression()));
            })
            ->leftJoin('risk_quant_profiles as qp', 'qp.radar_group_id', '=', 'rr.radar_group_id');
    }

    /**
     * Id of the appetite that applies to `risks`: the risk's override, else the
     * active appetite for its category, else the active default. Mirrors
     * RiskAppetite::effectiveFor().
     */
    public static function effectiveIdExpression(): string
    {
        return 'COALESCE(risks.risk_appetite_id,'
            . ' (SELECT MIN(rac.id) FROM risk_appetites rac WHERE rac.is_active = 1 AND rac.risk_category_id = risks.risk_category_id),'
            . ' (SELECT MIN(rad.id) FROM risk_appetites rad WHERE rad.is_active = 1 AND rad.risk_category_id IS NULL))';
    }

    public static function statusExpression(): string
    {
        $score = RiskAppetite::THRESHOLD_METRIC_RESIDUAL_SCORE;
        $loss = RiskAppetite::THRESHOLD_METRIC_RESIDUAL_LOSS;
        $current = "(CASE WHEN ra.threshold_metric = '{$score}' THEN COALESCE(rr.residual_risk_score, 0) ELSE COALESCE(rr.potential_loss, 0) END)";

        return "CASE"
            // Linked appetite that is qualitative (or has an unknown metric): no automated comparison.
            . " WHEN ra.id IS NOT NULL AND (ra.threshold_metric IS NULL OR ra.threshold_metric NOT IN ('{$score}', '{$loss}')) THEN '" . self::QUALITATIVE . "'"
            // Quantitative linked appetite but no residual analysis yet.
            . " WHEN ra.id IS NOT NULL AND rr.id IS NULL THEN '" . self::UNCONFIGURED . "'"
            // Quantitative linked appetite with at least one threshold.
            . " WHEN ra.id IS NOT NULL AND (ra.appetite_limit_value IS NOT NULL OR ra.tolerance_limit_value IS NOT NULL) THEN"
            . "   CASE"
            . "     WHEN {$current} > COALESCE(ra.tolerance_limit_value, ra.appetite_limit_value) THEN '" . self::BREACH . "'"
            . "     WHEN ra.appetite_limit_value IS NOT NULL AND {$current} > ra.appetite_limit_value THEN '" . self::WARNING . "'"
            . "     ELSE '" . self::WITHIN . "'"
            . "   END"
            // Fallback: radar quant profile thresholds.
            . " WHEN qp.risk_appetite_score IS NULL AND qp.risk_appetite_loss IS NULL THEN '" . self::UNCONFIGURED . "'"
            . " WHEN rr.id IS NULL THEN '" . self::UNCONFIGURED . "'"
            . " WHEN (qp.risk_appetite_score IS NOT NULL AND COALESCE(rr.residual_risk_score, 0) > qp.risk_appetite_score)"
            . "   OR (qp.risk_appetite_loss IS NOT NULL AND COALESCE(rr.potential_loss, 0) > qp.risk_appetite_loss) THEN '" . self::BREACH . "'"
            . " ELSE '" . self::WITHIN . "'"
            . " END";
    }

    /**
     * Maps a RiskAppetiteEvaluator status to the SQL vocabulary.
     */
    public static function fromEvaluatorStatus(string $status): string
    {
        return match ($status) {
            'breach' => self::BREACH,
            'warning' => self::WARNING,
            'within' => self::WITHIN,
            'qualitative_only' => self::QUALITATIVE,
            default => self::UNCONFIGURED,
        };
    }
}
