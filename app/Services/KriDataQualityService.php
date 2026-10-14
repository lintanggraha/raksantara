<?php

namespace App\Services;

use App\Kri;
use App\KriLog;
use Carbon\CarbonInterface;

class KriDataQualityService
{
    /**
     * Evaluate KRI value and source freshness, then persist quality metadata.
     *
     * @return array{status: string, notes: string}
     */
    public function evaluateAndPersist(Kri $kri, ?CarbonInterface $checkedAt = null): array
    {
        $checkedAt = $checkedAt ?: now();
        $result = $this->evaluate($kri, $checkedAt);

        $kri->forceFill([
            'data_quality_status' => $result['status'],
            'data_quality_notes' => $result['notes'],
            'last_quality_checked_at' => $checkedAt,
        ])->save();

        return $result;
    }

    /**
     * Record a source reading, calculate risk status, and persist lineage/quality metadata.
     *
     * @param array{source_recorded_at?: CarbonInterface|string|null, source_reference?: string|null, notes?: string|null} $metadata
     */
    public function recordReading(Kri $kri, float $value, array $metadata = [], ?int $actorId = null): KriLog
    {
        $sourceRecordedAt = $this->parseSourceTimestamp($metadata['source_recorded_at'] ?? null);
        $kri->current_value = $value;
        $kri->status = $kri->calculateStatusForValue($value);
        $kri->source_updated_at = $sourceRecordedAt;
        $kri->source_reference = $metadata['source_reference'] ?? $kri->source_reference;
        $kri->last_sync_error = null;
        $kri->last_updated_by = $actorId;
        $kri->save();

        $quality = $this->evaluateAndPersist($kri);

        return KriLog::query()->create([
            'kri_id' => $kri->id,
            'recorded_value' => $value,
            'source_recorded_at' => $sourceRecordedAt,
            'source_reference' => $metadata['source_reference'] ?? null,
            'status_calculated' => $kri->status,
            'data_quality_status' => $quality['status'],
            'notes' => $metadata['notes'] ?? null,
            'recorded_by' => $actorId,
        ]);
    }

    /**
     * @return array{status: string, notes: string}
     */
    public function evaluate(Kri $kri, CarbonInterface $checkedAt): array
    {
        if (is_null($kri->current_value)) {
            return ['status' => 'missing', 'notes' => 'No current KRI reading is available.'];
        }

        if (!is_numeric($kri->current_value)) {
            return ['status' => 'invalid', 'notes' => 'Current KRI reading is not numeric.'];
        }

        $expectedRefresh = (int) ($kri->expected_refresh_minutes ?? 0);
        if ($expectedRefresh > 0) {
            if (is_null($kri->source_updated_at)) {
                return ['status' => 'stale', 'notes' => 'Expected source timestamp is missing.'];
            }

            $expiresAt = $kri->source_updated_at->copy()->addMinutes($expectedRefresh);
            if ($expiresAt->lessThan($checkedAt)) {
                return [
                    'status' => 'stale',
                    'notes' => 'Source reading exceeded expected refresh interval at ' . $expiresAt->format('Y-m-d H:i:s') . '.',
                ];
            }
        }

        return ['status' => 'valid', 'notes' => 'Value and source freshness checks passed.'];
    }

    private function parseSourceTimestamp($value): CarbonInterface
    {
        if ($value instanceof CarbonInterface) {
            return $value;
        }

        if (is_string($value) && trim($value) !== '') {
            return now()->parse($value);
        }

        return now();
    }
}
