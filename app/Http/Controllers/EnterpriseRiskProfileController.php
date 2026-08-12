<?php

namespace App\Http\Controllers;

use App\Risk;
use App\RiskCommitteeDecision;
use App\Support\AuditLogger;
use App\Support\InputValidation;
use App\Support\RiskAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EnterpriseRiskProfileController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');

        $riskQuery = Risk::query()->with(['category', 'residualAnalysis']);
        RiskAccess::applyVisibilityScope($riskQuery, $user);
        $risks = $riskQuery->get();

        $topRisks = $risks
            ->sortByDesc(function (Risk $risk) {
                return (float) (optional($risk->residualAnalysis)->residual_risk_score ?? $risk->risk_score ?? 0);
            })
            ->take(10)
            ->values();

        $concentrationByOwner = $risks
            ->groupBy('risk_owner')
            ->map(fn ($items, $owner) => ['owner' => $owner ?: '-', 'count' => $items->count(), 'high' => $this->highRiskCount($items)])
            ->sortByDesc('count')
            ->values();

        $concentrationByCategory = $risks
            ->groupBy(fn (Risk $risk) => optional($risk->category)->name ?: '-')
            ->map(fn ($items, $category) => ['category' => $category, 'count' => $items->count(), 'high' => $this->highRiskCount($items)])
            ->sortByDesc('count')
            ->values();

        $decisions = RiskCommitteeDecision::query()
            ->with(['risk:id,risk_id,risk_title', 'creator:id,name'])
            ->latest('meeting_date')
            ->latest('id')
            ->paginate(20);

        $summary = [
            'total_risks' => $risks->count(),
            'high_risks' => $this->highRiskCount($risks),
            'open_decisions' => RiskCommitteeDecision::query()->whereIn('status', ['open', 'in_progress'])->count(),
            'overdue_decisions' => RiskCommitteeDecision::query()->whereIn('status', ['open', 'in_progress'])->whereDate('due_at', '<', today())->count(),
        ];

        return view('enterprise-risk-profile.index', [
            'summary' => $summary,
            'topRisks' => $topRisks,
            'concentrationByOwner' => $concentrationByOwner,
            'concentrationByCategory' => $concentrationByCategory,
            'decisions' => $decisions,
            'risks' => $risks->sortBy('risk_id')->values(),
            'canManage' => $user->isComplianceDivision(),
            'statusOptions' => RiskCommitteeDecision::STATUS_OPTIONS,
        ]);
    }

    public function storeDecision(Request $request): RedirectResponse
    {
        $this->ensureCanManage();
        $validated = $this->validateDecision($request);

        $decision = RiskCommitteeDecision::create(array_merge($validated, [
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]));

        AuditLogger::logModelEvent('committee.decision.created', $decision, null, $decision->toArray());

        return redirect()->route('enterprise-risk-profile.index')->with('success', 'Committee decision registered.');
    }

    public function updateDecision(Request $request, RiskCommitteeDecision $decision): RedirectResponse
    {
        $this->ensureCanManage();
        $validated = $this->validateDecision($request);
        $before = $decision->toArray();

        $decision->update(array_merge($validated, ['updated_by' => auth()->id()]));

        AuditLogger::logModelEvent('committee.decision.updated', $decision, $before, $decision->fresh()->toArray());

        return redirect()->route('enterprise-risk-profile.index')->with('success', 'Committee decision updated.');
    }

    private function validateDecision(Request $request): array
    {
        return $request->validate([
            'meeting_date' => ['required', 'date'],
            'meeting_reference' => ['nullable', 'string', 'max:120', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'risk_id' => ['nullable', 'integer', 'exists:risks,id'],
            'title' => ['required', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'decision' => ['required', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'owner_name' => ['required', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'due_at' => ['nullable', 'date'],
            'status' => ['required', 'in:' . implode(',', RiskCommitteeDecision::STATUS_OPTIONS)],
            'closure_notes' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
        ], [
            'meeting_reference.regex' => InputValidation::safeTextMessage(),
            'title.regex' => InputValidation::safeTextMessage(),
            'decision.regex' => InputValidation::safeTextMessage(),
            'owner_name.regex' => InputValidation::safeTextMessage(),
            'closure_notes.regex' => InputValidation::safeTextMessage(),
        ]);
    }

    private function ensureCanManage(): void
    {
        $user = auth()->user();
        abort_unless(!is_null($user) && $user->isComplianceDivision(), 403, 'Only compliance may manage committee decisions.');
    }

    private function highRiskCount($risks): int
    {
        return (int) $risks->filter(function (Risk $risk) {
            $score = (float) (optional($risk->residualAnalysis)->residual_risk_score ?? $risk->risk_score ?? 0);

            return $score >= 16;
        })->count();
    }
}
