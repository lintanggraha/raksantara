<?php

namespace App\Services;

use App\Kri;
use App\KriLog;
use App\Risk;

class KriAutoSyncService
{
    public const AUTO_METRIC_NAME = 'Auto: Residual Risk Score';
    private const AUTO_DESCRIPTION = 'Auto-generated KRI from imported Risk Register scores (Residual preferred, Inherent fallback).';
    private const WARN_THRESHOLD = 12.00;
    private const CRITICAL_THRESHOLD = 16.00;

    /**
     * @param  array<int, int|string>  $riskIds
     * @return array{processed: int, created: int, updated: int, logged: int, skipped: int}
     */
    public function syncByRiskIds(array $riskIds, ?int $actorId = null): array
    {
        $riskIds = collect($riskIds)
            ->map(function ($id) {
                return (int) $id;
            })
            ->filter(function (int $id) {
                return $id > 0;
            })
            ->unique()
            ->values()
            ->all();

        if (empty($riskIds)) {
            return [
                'processed' => 0,
                'created' => 0,
                'updated' => 0,
                'logged' => 0,
                'skipped' => 0,
            ];
        }

        $stats = [
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'logged' => 0,
            'skipped' => 0,
        ];

        $risks = Risk::query()
            ->with([
                'inherentAnalysis:id,risk_id,inherent_risk_score',
                'residualAnalysis:id,risk_id,residual_risk_score',
            ])
            ->whereIn('id', $riskIds)
            ->get();

        foreach ($risks as $risk) {
            $stats['processed']++;

            $currentValue = $this->resolveCurrentValue($risk);
            if (is_null($currentValue)) {
                $stats['skipped']++;
                continue;
            }

            $kri = Kri::query()->firstOrNew([
                'risk_id' => $risk->id,
                'metric_name' => self::AUTO_METRIC_NAME,
            ]);

            $isNew = !$kri->exists;
            $previousValue = is_null($kri->current_value) ? null : (float) $kri->current_value;
            $previousStatus = (string) ($kri->status ?? '');

            $kri->description = self::AUTO_DESCRIPTION;
            $kri->threshold_type = 'lower_is_better';
            $kri->threshold_warn = self::WARN_THRESHOLD;
            $kri->threshold_critical = self::CRITICAL_THRESHOLD;
            $kri->current_value = $currentValue;
            $kri->status = $kri->calculateStatusForValue($currentValue);
            $kri->last_updated_by = $actorId;
            $kri->save();

            if ($isNew) {
                $stats['created']++;
            } else {
                $stats['updated']++;
            }

            $shouldLog = $isNew
                || is_null($previousValue)
                || $previousValue !== $currentValue
                || $previousStatus !== $kri->status;

            if ($shouldLog) {
                KriLog::query()->create([
                    'kri_id' => $kri->id,
                    'recorded_value' => $currentValue,
                    'status_calculated' => $kri->status,
                    'notes' => 'Auto sync from Risk Register import',
                    'recorded_by' => $actorId,
                ]);

                $stats['logged']++;
            }
        }

        return $stats;
    }

    private function resolveCurrentValue(Risk $risk): ?float
    {
        $candidates = [
            optional($risk->residualAnalysis)->residual_risk_score,
            optional($risk->inherentAnalysis)->inherent_risk_score,
            $risk->risk_score,
        ];

        foreach ($candidates as $candidate) {
            if (!is_numeric($candidate)) {
                continue;
            }

            return (float) number_format((float) $candidate, 2, '.', '');
        }

        return null;
    }
}

