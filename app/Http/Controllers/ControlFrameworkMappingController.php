<?php

namespace App\Http\Controllers;

use App\RiskControl;
use App\RiskControlFrameworkMapping;
use App\Support\AuditLogger;
use App\Support\InputValidation;
use App\Support\RiskAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ControlFrameworkMappingController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');

        $controlsQuery = RiskControl::query()->with(['risk:id,risk_id,risk_title,risk_owner,created_by', 'frameworkMappings.reviewer:id,name']);
        if (!$user->isSuperadmin() && !$user->isComplianceDivision()) {
            $controlsQuery->whereHas('risk', function ($riskQuery) use ($user) {
                RiskAccess::applyVisibilityScope($riskQuery, $user);
            });
        }
        $controls = $controlsQuery->orderBy('control_code')->get();

        $summary = [
            'controls' => $controls->count(),
            'mapped' => $controls->flatMap->frameworkMappings->where('mapping_status', 'mapped')->count(),
            'gaps' => $controls->flatMap->frameworkMappings->where('mapping_status', 'gap')->count(),
            'partial' => $controls->flatMap->frameworkMappings->where('mapping_status', 'partial')->count(),
        ];

        return view('control-framework-mappings.index', [
            'controls' => $controls,
            'summary' => $summary,
            'canManage' => $user->isComplianceDivision(),
            'mappingStatuses' => RiskControlFrameworkMapping::STATUS_OPTIONS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureCanManage();
        $validated = $this->validatePayload($request);

        $mapping = RiskControlFrameworkMapping::updateOrCreate(
            [
                'risk_control_id' => $validated['risk_control_id'],
                'framework_code' => strtoupper(trim((string) $validated['framework_code'])),
                'control_reference' => trim((string) $validated['control_reference']),
            ],
            [
                'requirement_title' => $validated['requirement_title'] ?? null,
                'mapping_status' => $validated['mapping_status'],
                'evidence_notes' => $validated['evidence_notes'] ?? null,
                'last_reviewed_at' => now()->toDateString(),
                'reviewed_by' => auth()->id(),
            ]
        );

        AuditLogger::logModelEvent('control.framework_mapping.saved', $mapping, null, $mapping->toArray());

        return redirect()->route('control-framework-mappings.index')->with('success', 'Control framework mapping saved.');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'risk_control_id' => ['required', 'integer', 'exists:risk_controls,id'],
            'framework_code' => ['required', 'string', 'max:80', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'control_reference' => ['required', 'string', 'max:120', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'requirement_title' => ['nullable', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'mapping_status' => ['required', 'in:' . implode(',', RiskControlFrameworkMapping::STATUS_OPTIONS)],
            'evidence_notes' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
        ], [
            'framework_code.regex' => InputValidation::safeTextMessage(),
            'control_reference.regex' => InputValidation::safeTextMessage(),
            'requirement_title.regex' => InputValidation::safeTextMessage(),
            'evidence_notes.regex' => InputValidation::safeTextMessage(),
        ]);
    }

    private function ensureCanManage(): void
    {
        abort_unless(optional(auth()->user())->isComplianceDivision(), 403, 'Only compliance may manage framework mappings.');
    }
}
