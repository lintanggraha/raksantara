<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class RiskIncident extends Model
{
    public const SEVERITY_LEVELS = [
        'Low',
        'Medium',
        'High',
        'Critical',
    ];

    public const STATUS_OPTIONS = [
        'Open',
        'Investigating',
        'Mitigated',
        'Closed',
    ];

    public const MITIGATION_VERDICTS = [
        'Not Tested',
        'Failed',
        'Partially Effective',
        'Effective',
    ];

    protected $fillable = [
        'incident_code',
        'risk_id',
        'risk_owner',
        'affected_client',
        'is_new_finding',
        'loss_amount',
        'reported_by',
        'last_updated_by',
        'title',
        'incident_description',
        'occurred_at',
        'detected_at',
        'severity',
        'status',
        'mitigation_verdict',
        'business_impact',
        'immediate_action',
        'root_cause',
        'corrective_action',
        'lessons_learned',
        'risk_review_status',
        'control_review_status',
        'kri_review_status',
        'post_incident_reviewed_by',
        'post_incident_reviewed_at',
        'resolution_notes',
        'closed_at',
    ];

    /**
     * Autocomplete suggestions for the affected client / party field.
     */
    public static function affectedPartyOptions(): array
    {
        $configBanks = collect(config('audit_dashboard.bank.mappings', []))
            ->pluck('name')
            ->filter()
            ->all();

        $all = array_unique(array_merge((array) config('incidents.affected_parties', []), $configBanks));
        sort($all);

        return array_values($all);
    }

    protected $casts = [
        'occurred_at' => 'datetime',
        'detected_at' => 'datetime',
        'closed_at' => 'datetime',
        'post_incident_reviewed_at' => 'datetime',
        'loss_amount' => 'float',
        'is_new_finding' => 'boolean',
    ];

    public function risk()
    {
        return $this->belongsTo(Risk::class);
    }

    public function getEffectiveRiskOwnerAttribute(): ?string
    {
        return $this->risk ? $this->risk->risk_owner : $this->risk_owner;
    }

    public function getFormattedLossAmountAttribute(): string
    {
        return 'Rp ' . number_format((float) ($this->loss_amount ?? 0), 0, ',', '.');
    }

    public function isNewFinding(): bool
    {
        return (bool) $this->is_new_finding || is_null($this->risk_id);
    }

    public function actions()
    {
        return $this->hasMany(RiskIncidentAction::class)->orderBy('due_at');
    }

    public function postIncidentReviewer()
    {
        return $this->belongsTo(User::class, 'post_incident_reviewed_by');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    public static function generateIncidentCode(?int $year = null): string
    {
        $year = $year ?: Carbon::now()->year;
        $prefix = 'INC-' . $year . '-';
        $maxNumber = 0;
        $driver = self::query()->getConnection()->getDriverName();

        if ($driver === 'mysql') {
            $maxNumber = (int) (self::query()
                ->where('incident_code', 'like', $prefix . '%')
                ->selectRaw("MAX(CAST(SUBSTRING_INDEX(incident_code, '-', -1) AS UNSIGNED)) as max_number")
                ->value('max_number') ?? 0);

            return sprintf('INC-%d-%04d', $year, $maxNumber + 1);
        }

        foreach (self::query()->whereNotNull('incident_code')->pluck('incident_code') as $incidentCode) {
            if (preg_match('/^INC\-' . preg_quote((string) $year, '/') . '\-(\d+)$/', (string) $incidentCode, $matches) !== 1) {
                continue;
            }

            $maxNumber = max($maxNumber, (int) $matches[1]);
        }

        return sprintf('INC-%d-%04d', $year, $maxNumber + 1);
    }

    public static function createWithGeneratedIncidentCode(array $attributes, int $maxAttempts = 5): self
    {
        $occurredAtRaw = $attributes['occurred_at'] ?? null;
        $occurredAt = $occurredAtRaw instanceof Carbon
            ? $occurredAtRaw
            : Carbon::parse($occurredAtRaw ?? now());
        $year = (int) $occurredAt->year;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $attempt++;
            $attributes['incident_code'] = self::generateIncidentCode($year);

            try {
                return self::create($attributes);
            } catch (QueryException $exception) {
                if (!self::isIncidentCodeDuplicateException($exception) || $attempt >= $maxAttempts) {
                    throw $exception;
                }
            }
        }

        throw new \RuntimeException('Unable to generate unique incident code.');
    }

    private static function isIncidentCodeDuplicateException(QueryException $exception): bool
    {
        $message = strtolower((string) $exception->getMessage());

        return strpos($message, 'duplicate') !== false
            && strpos($message, 'incident_code') !== false;
    }
}
