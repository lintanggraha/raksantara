<?php

namespace App\Http\Controllers;

use App\Risk;
use App\Support\RiskAccess;
use App\Support\RiskOwnerAccess;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class OwnerWorkloadController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');

        $query = Risk::query()->with([
            'inherentAnalysis:id,risk_id,inherent_risk_score',
            'residualAnalysis:id,risk_id,residual_risk_score',
            'treatments:id,risk_id,status,deadline,next_action_at',
            'kris:id,risk_id,status',
            'statusChangeRequests' => function ($statusQuery) {
                $statusQuery
                    ->select(['id', 'risk_id', 'status'])
                    ->where('status', 'pending');
            },
        ]);

        RiskAccess::applyVisibilityScope($query, $user);

        $risks = $query->get();
        $today = Carbon::today();

        $rows = $risks
            ->groupBy('risk_owner')
            ->map(function ($ownerRisks, $riskOwner) use ($today) {
                $treatments = $ownerRisks->flatMap(function ($risk) {
                    return $risk->treatments;
                });
                $kris = $ownerRisks->flatMap(function ($risk) {
                    return $risk->kris;
                });
                $pendingApprovals = (int) $ownerRisks->sum(function ($risk) {
                    return $risk->statusChangeRequests->count();
                });

                $openTreatments = $treatments->filter(function ($treatment) {
                    return (string) $treatment->status !== 'Completed';
                });
                $overdueTreatments = $openTreatments->filter(function ($treatment) use ($today) {
                    return !is_null($treatment->deadline) && $treatment->deadline->copy()->startOfDay()->lt($today);
                });
                $dueSoonTreatments = $openTreatments->filter(function ($treatment) use ($today) {
                    if (is_null($treatment->next_action_at)) {
                        return false;
                    }

                    $daysUntilNextAction = $today->diffInDays($treatment->next_action_at->copy()->startOfDay(), false);

                    return $daysUntilNextAction >= 0 && $daysUntilNextAction <= 7;
                });
                $blockedTreatments = $treatments->where('status', 'Blocked');

                $highRiskCount = (int) $ownerRisks->filter(function ($risk) {
                    $residualScore = optional($risk->residualAnalysis)->residual_risk_score;
                    $inherentScore = optional($risk->inherentAnalysis)->inherent_risk_score;
                    $score = is_null($residualScore) ? $inherentScore : $residualScore;

                    return !is_null($score) && (float) $score >= 15;
                })->count();

                $kriRedCount = (int) $kris->where('status', 'Red')->count();
                $workloadScore = (int) (
                    ($openTreatments->count() * 2)
                    + ($overdueTreatments->count() * 4)
                    + ($blockedTreatments->count() * 3)
                    + ($pendingApprovals * 2)
                    + ($kriRedCount * 4)
                    + ($highRiskCount * 3)
                    + ($dueSoonTreatments->count() * 1)
                );

                return [
                    'owner' => (string) $riskOwner,
                    'owner_label' => RiskOwnerAccess::displayLabel((string) $riskOwner),
                    'risk_count' => (int) $ownerRisks->count(),
                    'high_risk_count' => $highRiskCount,
                    'open_treatments' => (int) $openTreatments->count(),
                    'overdue_treatments' => (int) $overdueTreatments->count(),
                    'blocked_treatments' => (int) $blockedTreatments->count(),
                    'due_soon_treatments' => (int) $dueSoonTreatments->count(),
                    'pending_approvals' => $pendingApprovals,
                    'kri_red_count' => $kriRedCount,
                    'workload_score' => $workloadScore,
                    'attention_level' => $this->resolveAttentionLevel($workloadScore),
                ];
            })
            ->sortByDesc('workload_score')
            ->values();

        $summary = [
            'owners' => (int) $rows->count(),
            'open_treatments' => (int) $rows->sum('open_treatments'),
            'overdue_treatments' => (int) $rows->sum('overdue_treatments'),
            'blocked_treatments' => (int) $rows->sum('blocked_treatments'),
            'pending_approvals' => (int) $rows->sum('pending_approvals'),
            'kri_red_count' => (int) $rows->sum('kri_red_count'),
            'highest_owner' => (string) ($rows->first()['owner_label'] ?? '-'),
            'highest_score' => (int) ($rows->first()['workload_score'] ?? 0),
        ];

        return view('owners.workload', compact('rows', 'summary'));
    }

    private function resolveAttentionLevel(int $score): string
    {
        if ($score >= 20) {
            return 'Critical Focus';
        }

        if ($score >= 12) {
            return 'High Attention';
        }

        if ($score >= 6) {
            return 'Watch';
        }

        return 'Stable';
    }
}
