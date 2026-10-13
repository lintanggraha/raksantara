<?php

namespace App\Http\Controllers;

use App\Risk;
use App\RiskAcceptance;
use App\RiskControl;
use App\RiskControlTest;
use App\Support\AuditLogger;
use App\Support\InputValidation;
use App\Support\RiskAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RiskGovernanceController extends Controller
{
    public function show(int $riskId): View
    {
        $risk = $this->loadRisk($riskId);
        $user = auth()->user();

        return view('risks.governance', [
            'risk' => $risk,
            'controls' => $risk->controls->sortBy(function (RiskControl $control) {
                return $control->next_test_at?->format('Y-m-d') ?? '9999-12-31';
            })->values(),
            'acceptances' => $risk->acceptances,
            'canManageControls' => RiskAccess::canManage($user, $risk, 'controls.manage'),
            'canRequestAcceptance' => RiskAccess::canManage($user, $risk, 'risk-acceptance.request'),
            'canDecide' => !is_null($user) && $user->hasPermission('risk-acceptance.decide'),
        ]);
    }

    public function storeControl(Request $request, int $riskId): RedirectResponse
    {
        $risk = $this->loadRisk($riskId);
        $this->ensureCanManage($risk, 'controls.manage');

        $validated = $request->validate([
            'control_code' => ['required', 'string', 'max:80', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'name' => ['required', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'description' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'owner_name' => ['nullable', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'review_frequency' => ['required', 'in:Monthly,Quarterly,Semi Annual,Annual'],
            'status' => ['required', 'in:active,inactive'],
            'next_test_at' => ['nullable', 'date'],
        ], $this->safeTextMessages(['control_code', 'name', 'description', 'owner_name']));

        $control = $risk->controls()->create(array_merge($validated, [
            'control_code' => strtoupper(trim($validated['control_code'])),
            'created_by' => auth()->id(),
        ]));

        AuditLogger::logModelEvent('risk.control.created', $risk, null, [
            'control_id' => $control->id,
            'control_code' => $control->control_code,
            'name' => $control->name,
        ]);

        return $this->redirectToGovernance($risk)->with('success', 'Control assurance entry created.');
    }

    public function storeControlTest(Request $request, int $riskId, int $controlId): RedirectResponse
    {
        $risk = $this->loadRisk($riskId);
        $this->ensureCanManage($risk, 'controls.manage');
        $control = $this->loadControl($risk, $controlId);

        $validated = $request->validate([
            'tested_at' => ['required', 'date'],
            'result' => ['required', 'in:effective,partially_effective,ineffective'],
            'findings' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'remediation_plan' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'remediation_due_at' => ['nullable', 'date'],
            'remediation_status' => ['nullable', 'in:not_required,open,in_progress,closed'],
            'next_test_at' => ['nullable', 'date'],
        ], $this->safeTextMessages(['findings', 'remediation_plan']));

        $remediationStatus = $validated['result'] === 'effective'
            ? 'not_required'
            : ($validated['remediation_status'] ?? 'open');

        $test = $control->tests()->create(array_merge($validated, [
            'tested_by' => auth()->id(),
            'remediation_status' => $remediationStatus,
        ]));

        $control->update([
            'design_effectiveness' => $validated['result'],
            'last_tested_at' => $validated['tested_at'],
            'next_test_at' => $validated['next_test_at'] ?? $control->next_test_at,
        ]);

        AuditLogger::logModelEvent('risk.control.tested', $risk, null, [
            'control_id' => $control->id,
            'test_id' => $test->id,
            'result' => $test->result,
            'remediation_status' => $test->remediation_status,
        ]);

        return $this->redirectToGovernance($risk)->with('success', 'Control test recorded.');
    }

    public function requestAcceptance(Request $request, int $riskId): RedirectResponse
    {
        $risk = $this->loadRisk($riskId);
        $this->ensureCanManage($risk, 'risk-acceptance.request');

        $validated = $request->validate([
            'rationale' => ['required', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'compensating_controls' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'accepted_until' => ['required', 'date', 'after:today'],
        ], $this->safeTextMessages(['rationale', 'compensating_controls']));

        $hasOpenAcceptance = $risk->acceptances()
            ->whereIn('status', ['requested', 'approved'])
            ->whereDate('accepted_until', '>=', today())
            ->exists();

        if ($hasOpenAcceptance) {
            return $this->redirectToGovernance($risk)
                ->withErrors(['acceptance' => 'This risk already has an active or pending acceptance. Close or expire it before creating another request.']);
        }

        $acceptance = $risk->acceptances()->create(array_merge($validated, [
            'requested_by' => auth()->id(),
            'status' => 'requested',
        ]));

        AuditLogger::logModelEvent('risk.acceptance.requested', $risk, null, [
            'acceptance_id' => $acceptance->id,
            'accepted_until' => $acceptance->accepted_until?->format('Y-m-d'),
        ]);

        return $this->redirectToGovernance($risk)->with('success', 'Risk acceptance request submitted for review.');
    }

    public function decideAcceptance(Request $request, int $riskId, int $acceptanceId): RedirectResponse
    {
        $risk = $this->loadRisk($riskId);
        $user = auth()->user();
        abort_unless(!is_null($user) && $user->hasPermission('risk-acceptance.decide'), 403, 'Permission required to decide a risk acceptance.');

        $acceptance = $risk->acceptances()->whereKey($acceptanceId)->firstOrFail();
        abort_unless($acceptance->isOpen(), 422, 'This risk acceptance has already been decided.');

        $validated = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'decision_notes' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
        ], $this->safeTextMessages(['decision_notes']));

        $acceptance->update([
            'status' => $validated['decision'],
            'reviewed_by' => $user->id,
            'decision_notes' => $validated['decision_notes'] ?? null,
            'decided_at' => now(),
        ]);

        AuditLogger::logModelEvent('risk.acceptance.' . $validated['decision'], $risk, null, [
            'acceptance_id' => $acceptance->id,
            'accepted_until' => $acceptance->accepted_until?->format('Y-m-d'),
        ]);

        return $this->redirectToGovernance($risk)->with('success', 'Risk acceptance ' . $validated['decision'] . '.');
    }

    private function loadRisk(int $riskId): Risk
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');

        $query = Risk::query()->with([
            'category',
            'controls.tests.tester:id,name',
            'acceptances.requester:id,name',
            'acceptances.reviewer:id,name',
        ]);
        RiskAccess::applyVisibilityScope($query, $user);

        return $query->findOrFail($riskId);
    }

    private function loadControl(Risk $risk, int $controlId): RiskControl
    {
        return $risk->controls()->whereKey($controlId)->firstOrFail();
    }

    private function ensureCanManage(Risk $risk, string $permission): void
    {
        abort_unless(RiskAccess::canManage(auth()->user(), $risk, $permission), 403, 'You are not allowed to perform this governance action.');
    }

    private function redirectToGovernance(Risk $risk): RedirectResponse
    {
        return redirect()->route('risks.governance.show', $risk->id);
    }

    private function safeTextMessages(array $fields): array
    {
        $messages = [];
        foreach ($fields as $field) {
            $messages[$field . '.regex'] = InputValidation::safeTextMessage();
        }

        return $messages;
    }
}
