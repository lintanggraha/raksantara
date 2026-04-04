<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RiskAppetite extends Model
{
    public const THRESHOLD_METRIC_QUALITATIVE = 'qualitative_only';
    public const THRESHOLD_METRIC_RESIDUAL_SCORE = 'residual_risk_score';
    public const THRESHOLD_METRIC_RESIDUAL_LOSS = 'residual_potential_loss';

    protected $fillable = [
        'name',
        'risk_category',
        'threshold_metric',
        'appetite_statement',
        'appetite_limit_value',
        'tolerance_limit_value',
        'unit',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'appetite_limit_value' => 'float',
        'tolerance_limit_value' => 'float',
        'is_active' => 'boolean',
    ];

    public static function thresholdMetricOptions(): array
    {
        return [
            self::THRESHOLD_METRIC_QUALITATIVE => 'Qualitative Only',
            self::THRESHOLD_METRIC_RESIDUAL_SCORE => 'Residual Risk Score',
            self::THRESHOLD_METRIC_RESIDUAL_LOSS => 'Residual Potential Loss',
        ];
    }

    public static function thresholdMetricValues(): array
    {
        return array_keys(self::thresholdMetricOptions());
    }

    public function effectiveThresholdMetric(): string
    {
        $metric = (string) ($this->attributes['threshold_metric'] ?? '');

        return in_array($metric, self::thresholdMetricValues(), true)
            ? $metric
            : self::THRESHOLD_METRIC_QUALITATIVE;
    }

    public function isQuantitative(): bool
    {
        return in_array($this->effectiveThresholdMetric(), [
            self::THRESHOLD_METRIC_RESIDUAL_SCORE,
            self::THRESHOLD_METRIC_RESIDUAL_LOSS,
        ], true);
    }

    public function getThresholdMetricLabelAttribute(): string
    {
        return self::thresholdMetricOptions()[$this->effectiveThresholdMetric()]
            ?? self::thresholdMetricOptions()[self::THRESHOLD_METRIC_QUALITATIVE];
    }

    public function risks()
    {
        return $this->hasMany(Risk::class, 'risk_appetite_id', 'id');
    }
}
