<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * A risk appetite statement. It applies to every risk in its category (or to
 * all categories when risk_category_id is null) unless a risk overrides it
 * with an explicit risk_appetite_id. Resolution order: override, category,
 * default (see effectiveFor() and RiskAppetiteSql::effectiveIdExpression()).
 */
class RiskAppetite extends Model
{
    public const THRESHOLD_METRIC_QUALITATIVE = 'qualitative_only';
    public const THRESHOLD_METRIC_RESIDUAL_SCORE = 'residual_risk_score';
    public const THRESHOLD_METRIC_RESIDUAL_LOSS = 'residual_potential_loss';

    protected $fillable = [
        'name',
        'risk_category_id',
        'threshold_metric',
        'appetite_statement',
        'appetite_limit_value',
        'tolerance_limit_value',
        'unit',
        'is_active',
        'approval_reference',
        'approved_at',
        'next_review_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'appetite_limit_value' => 'float',
        'tolerance_limit_value' => 'float',
        'is_active' => 'boolean',
        'approved_at' => 'date',
        'next_review_at' => 'date',
    ];

    public static function thresholdMetricOptions(): array
    {
        return [
            self::THRESHOLD_METRIC_QUALITATIVE => 'Kualitatif (tanpa batas angka)',
            self::THRESHOLD_METRIC_RESIDUAL_SCORE => 'Skor risiko residual',
            self::THRESHOLD_METRIC_RESIDUAL_LOSS => 'Potensi kerugian residual (Rp)',
        ];
    }

    public static function thresholdMetricValues(): array
    {
        return array_keys(self::thresholdMetricOptions());
    }

    /**
     * Risk management (second line, the Compliance division here) maintains
     * the statements; Board approval is recorded on each statement.
     */
    public static function canManage(?User $user): bool
    {
        return !is_null($user) && ($user->isSuperadmin() || $user->isComplianceDivision());
    }

    /**
     * The appetite that applies to a risk: its override, else the active
     * statement for its category, else the active default statement.
     */
    public static function effectiveFor(Risk $risk): ?self
    {
        if ($risk->risk_appetite_id) {
            return $risk->relationLoaded('riskAppetite') ? $risk->riskAppetite : $risk->riskAppetite()->first();
        }

        if ($risk->risk_category_id) {
            $byCategory = self::query()->where('is_active', true)->where('risk_category_id', $risk->risk_category_id)->orderBy('id')->first();
            if ($byCategory) {
                return $byCategory;
            }
        }

        return self::query()->where('is_active', true)->whereNull('risk_category_id')->orderBy('id')->first();
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

    public function isApproved(): bool
    {
        return !empty($this->approval_reference) && !is_null($this->approved_at);
    }

    public function isReviewOverdue(): bool
    {
        return !is_null($this->next_review_at) && $this->next_review_at->isPast();
    }

    public function scopeLabel(): string
    {
        return $this->category ? $this->category->name : 'Semua kategori (default)';
    }

    public function getThresholdMetricLabelAttribute(): string
    {
        return self::thresholdMetricOptions()[$this->effectiveThresholdMetric()]
            ?? self::thresholdMetricOptions()[self::THRESHOLD_METRIC_QUALITATIVE];
    }

    public function category()
    {
        return $this->belongsTo(RiskCategory::class, 'risk_category_id');
    }

    /**
     * Risks that override their category appetite with this statement.
     */
    public function risks()
    {
        return $this->hasMany(Risk::class, 'risk_appetite_id', 'id');
    }
}
