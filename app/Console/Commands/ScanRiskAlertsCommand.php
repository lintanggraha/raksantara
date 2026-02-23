<?php

namespace App\Console\Commands;

use App\Risk;
use App\RiskAlert;
use App\RiskTreatment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ScanRiskAlertsCommand extends Command
{
    protected $signature = 'risk:scan-alerts {--days=3 : Reminder window before deadline}';

    protected $description = 'Generate deadline reminders/escalations and KRI threshold alerts';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $today = Carbon::today();
        $windowEnd = $today->copy()->addDays($days);

        $createdOrUpdated = 0;
        $resolved = 0;
        $escalated = 0;

        $treatments = RiskTreatment::with('risk')
            ->whereNotNull('deadline')
            ->get();

        foreach ($treatments as $treatment) {
            if (!$treatment->risk) {
                continue;
            }

            $deadline = Carbon::parse($treatment->deadline)->startOfDay();

            if ($deadline->lt($today)) {
                $daysOverdue = $deadline->diffInDays($today);
                $alert = RiskAlert::updateOrCreate(
                    [
                        'risk_treatment_id' => $treatment->id,
                        'alert_type' => 'treatment_deadline_overdue',
                        'status' => RiskAlert::STATUS_OPEN,
                    ],
                    [
                        'risk_id' => $treatment->risk_id,
                        'severity' => 'critical',
                        'due_date' => $deadline->toDateString(),
                        'message' => sprintf(
                            'Treatment deadline overdue %d day(s) for risk %s.',
                            $daysOverdue,
                            (string) ($treatment->risk->risk_id ?: ('ID-' . $treatment->risk->id))
                        ),
                        'alerted_at' => now(),
                        'meta' => [
                            'deadline' => $deadline->toDateString(),
                            'days_overdue' => $daysOverdue,
                        ],
                    ]
                );
                $createdOrUpdated++;

                $dueSoonResolved = RiskAlert::query()
                    ->where('risk_treatment_id', $treatment->id)
                    ->where('alert_type', 'treatment_deadline_due_soon')
                    ->where('status', RiskAlert::STATUS_OPEN)
                    ->update([
                        'status' => RiskAlert::STATUS_RESOLVED,
                        'resolved_at' => now(),
                    ]);
                $resolved += $dueSoonResolved;

                if ($daysOverdue >= 7 && is_null($alert->escalated_at)) {
                    $alert->update([
                        'escalated_at' => now(),
                        'severity' => 'critical',
                    ]);
                    $escalated++;
                }

                continue;
            }

            if ($deadline->lte($windowEnd)) {
                $daysLeft = $today->diffInDays($deadline);
                RiskAlert::updateOrCreate(
                    [
                        'risk_treatment_id' => $treatment->id,
                        'alert_type' => 'treatment_deadline_due_soon',
                        'status' => RiskAlert::STATUS_OPEN,
                    ],
                    [
                        'risk_id' => $treatment->risk_id,
                        'severity' => 'warning',
                        'due_date' => $deadline->toDateString(),
                        'message' => sprintf(
                            'Treatment deadline in %d day(s) for risk %s.',
                            $daysLeft,
                            (string) ($treatment->risk->risk_id ?: ('ID-' . $treatment->risk->id))
                        ),
                        'alerted_at' => now(),
                        'meta' => [
                            'deadline' => $deadline->toDateString(),
                            'days_left' => $daysLeft,
                        ],
                    ]
                );
                $createdOrUpdated++;

                continue;
            }

            $resolved += RiskAlert::query()
                ->where('risk_treatment_id', $treatment->id)
                ->whereIn('alert_type', ['treatment_deadline_due_soon', 'treatment_deadline_overdue'])
                ->where('status', RiskAlert::STATUS_OPEN)
                ->update([
                    'status' => RiskAlert::STATUS_RESOLVED,
                    'resolved_at' => now(),
                ]);
        }

        $risks = Risk::with(['residualAnalysis.radarGroup.quantProfile'])->get();
        foreach ($risks as $risk) {
            $residual = $risk->residualAnalysis;
            $residualScore = (int) (optional($residual)->residual_risk_score ?? 0);
            $residualLoss = (int) (optional($residual)->potential_loss ?? 0);
            $quantProfile = optional(optional($residual)->radarGroup)->quantProfile;
            $thresholdScore = (int) (optional($quantProfile)->risk_appetite_score ?? 15);
            $thresholdLoss = optional($quantProfile)->risk_appetite_loss;

            $isBreachByScore = $residualScore > 0 && $residualScore > $thresholdScore;
            $isBreachByLoss = !is_null($thresholdLoss) && $residualLoss > (int) $thresholdLoss;
            $isBreach = $isBreachByScore || $isBreachByLoss;

            if ($isBreach) {
                RiskAlert::updateOrCreate(
                    [
                        'risk_id' => $risk->id,
                        'risk_treatment_id' => null,
                        'alert_type' => 'kri_threshold_breach',
                        'status' => RiskAlert::STATUS_OPEN,
                    ],
                    [
                        'severity' => 'critical',
                        'message' => sprintf(
                            'Risk %s exceeded appetite threshold (score=%d, threshold=%d).',
                            (string) ($risk->risk_id ?: ('ID-' . $risk->id)),
                            $residualScore,
                            $thresholdScore
                        ),
                        'alerted_at' => now(),
                        'meta' => [
                            'residual_score' => $residualScore,
                            'threshold_score' => $thresholdScore,
                            'residual_loss' => $residualLoss,
                            'threshold_loss' => $thresholdLoss,
                        ],
                    ]
                );
                $createdOrUpdated++;
            } else {
                $resolved += RiskAlert::query()
                    ->where('risk_id', $risk->id)
                    ->whereNull('risk_treatment_id')
                    ->where('alert_type', 'kri_threshold_breach')
                    ->where('status', RiskAlert::STATUS_OPEN)
                    ->update([
                        'status' => RiskAlert::STATUS_RESOLVED,
                        'resolved_at' => now(),
                    ]);
            }
        }

        $this->info(sprintf(
            'Scan complete. created_or_updated=%d resolved=%d escalated=%d',
            $createdOrUpdated,
            $resolved,
            $escalated
        ));

        return self::SUCCESS;
    }
}

