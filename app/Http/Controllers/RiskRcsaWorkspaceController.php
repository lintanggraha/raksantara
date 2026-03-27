<?php

namespace App\Http\Controllers;

use App\Risk;
use App\RiskRcsaWorkspace;
use App\Support\InputValidation;
use App\Support\RiskAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RiskRcsaWorkspaceController extends Controller
{
    public function show(int $riskId): View
    {
        $risk = $this->loadRisk($riskId);

        return view('risks.rcsa', [
            'risk' => $risk,
            'workspace' => $risk->rcsaWorkspace,
            'canManage' => RiskAccess::canManage(auth()->user(), $risk),
        ]);
    }

    public function update(Request $request, int $riskId): RedirectResponse
    {
        $risk = $this->loadRisk($riskId);
        abort_unless(RiskAccess::canManage(auth()->user(), $risk), 403, 'You are not allowed to update this RCSA workspace.');

        $textRule255 = 'regex:' . InputValidation::SAFE_TEXT_REGEX;
        $textRule5000 = 'regex:' . InputValidation::SAFE_TEXT_REGEX;

        $validated = $request->validate([
            'process_owner' => ['nullable', 'string', 'max:255', $textRule255],
            'objective' => ['nullable', 'string', 'max:5000', $textRule5000],
            'scope' => ['nullable', 'string', 'max:5000', $textRule5000],
            'key_controls' => ['nullable', 'string', 'max:5000', $textRule5000],
            'control_gaps' => ['nullable', 'string', 'max:5000', $textRule5000],
            'likelihood_rationale' => ['nullable', 'string', 'max:5000', $textRule5000],
            'impact_rationale' => ['nullable', 'string', 'max:5000', $textRule5000],
            'review_frequency' => ['nullable', 'string', 'in:Monthly,Quarterly,Semi Annual,Annual'],
            'next_review_at' => ['nullable', 'date'],
            'sign_off_name' => ['nullable', 'string', 'max:255', $textRule255],
            'sign_off_role' => ['nullable', 'string', 'max:255', $textRule255],
            'signed_off_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000', $textRule5000],
        ], [
            'process_owner.regex' => InputValidation::safeTextMessage(),
            'objective.regex' => InputValidation::safeTextMessage(),
            'scope.regex' => InputValidation::safeTextMessage(),
            'key_controls.regex' => InputValidation::safeTextMessage(),
            'control_gaps.regex' => InputValidation::safeTextMessage(),
            'likelihood_rationale.regex' => InputValidation::safeTextMessage(),
            'impact_rationale.regex' => InputValidation::safeTextMessage(),
            'sign_off_name.regex' => InputValidation::safeTextMessage(),
            'sign_off_role.regex' => InputValidation::safeTextMessage(),
            'notes.regex' => InputValidation::safeTextMessage(),
        ]);

        RiskRcsaWorkspace::updateOrCreate(
            ['risk_id' => $risk->id],
            array_merge($validated, ['updated_by' => auth()->id()])
        );

        return redirect()->route('risks.rcsa.show', $risk->id)->with('success', 'RCSA workspace updated.');
    }

    private function loadRisk(int $riskId): Risk
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');

        $query = Risk::query()->with(['rcsaWorkspace.updater:id,name', 'category']);
        RiskAccess::applyVisibilityScope($query, $user);

        return $query->findOrFail($riskId);
    }
}
