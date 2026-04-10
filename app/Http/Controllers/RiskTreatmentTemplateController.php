<?php

namespace App\Http\Controllers;

use App\RiskTreatmentTemplate;
use App\Support\InputValidation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RiskTreatmentTemplateController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');

        $templates = RiskTreatmentTemplate::query()
            ->visibleTo($user)
            ->with('creator:id,name')
            ->orderBy('risk_owner')
            ->orderBy('name')
            ->get();

        $allowedRiskOwners = $user->allowedRiskOwners();
        $canManage = $user->isDivisionVerified();

        return view('treatment-templates.index', compact('templates', 'allowedRiskOwners', 'canManage'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureTemplateWriteAllowed();

        $validated = $request->validate($this->rules(auth()->user()->allowedRiskOwners()), $this->messages(), $this->attributes());
        RiskTreatmentTemplate::create(array_merge($validated, [
            'created_by' => auth()->id(),
            'is_active' => $request->boolean('is_active', true),
        ]));

        return redirect()->route('treatment-templates.index')->with('success', 'Mitigation playbook saved.');
    }

    public function update(Request $request, RiskTreatmentTemplate $template): RedirectResponse
    {
        $this->ensureTemplateCanManage($template);

        $validated = $request->validate($this->rules(auth()->user()->allowedRiskOwners()), $this->messages(), $this->attributes());
        $template->update(array_merge($validated, [
            'is_active' => $request->boolean('is_active'),
        ]));

        return redirect()->route('treatment-templates.index')->with('success', 'Mitigation playbook updated.');
    }

    public function destroy(RiskTreatmentTemplate $template): RedirectResponse
    {
        $this->ensureTemplateCanManage($template);
        $template->delete();

        return redirect()->route('treatment-templates.index')->with('success', 'Mitigation playbook deleted.');
    }

    private function rules(array $allowedRiskOwners): array
    {
        $textRule255 = 'regex:' . InputValidation::SAFE_TEXT_REGEX;
        $textRule5000 = 'regex:' . InputValidation::SAFE_TEXT_REGEX;

        return [
            'name' => ['required', 'string', 'max:255', $textRule255],
            'risk_owner' => ['required', 'string', 'max:255', Rule::in($allowedRiskOwners)],
            'option' => ['required', 'string', 'in:Avoidance,Mitigation,Transfer,Acceptance'],
            'description' => ['required', 'string', 'max:5000', $textRule5000],
            'default_pic' => ['nullable', 'string', 'max:255', $textRule255],
            'default_effectiveness' => ['required', 'string', 'in:Low,Medium,High'],
            'target_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'next_action_offset_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'checklist' => ['nullable', 'string', 'max:5000', $textRule5000],
        ];
    }

    private function messages(): array
    {
        return [
            'name.regex' => InputValidation::safeTextMessage(),
            'description.regex' => InputValidation::safeTextMessage(),
            'default_pic.regex' => InputValidation::safeTextMessage(),
            'checklist.regex' => InputValidation::safeTextMessage(),
        ];
    }

    private function attributes(): array
    {
        return [
            'name' => 'Playbook Name',
            'risk_owner' => 'Risk Owner',
            'default_pic' => 'Default PIC',
            'target_days' => 'Target Deadline (Days)',
            'next_action_offset_days' => 'Next Action Offset (Days)',
        ];
    }

    private function ensureTemplateWriteAllowed(): void
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');
        abort_if(!$user->isDivisionVerified(), 403, 'Your account is read-only for playbook management.');
    }

    private function ensureTemplateCanManage(RiskTreatmentTemplate $template): void
    {
        $this->ensureTemplateWriteAllowed();
        abort_unless($template->canManageBy(auth()->user()), 403, 'You are not allowed to manage this playbook.');
    }
}
