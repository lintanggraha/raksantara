<?php

namespace App\Http\Controllers;

use App\Risk;
use App\RiskMaturityAssessment;
use App\RiskScenario;
use App\RiskScenarioImpact;
use App\Support\AuditLogger;
use App\Support\InputValidation;
use App\Support\RiskAccess;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StrategicRiskReviewController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');

        $riskQuery = Risk::query()->select(['id', 'risk_id', 'risk_title', 'risk_owner', 'risk_score']);
        RiskAccess::applyVisibilityScope($riskQuery, $user);
        $risks = $riskQuery->orderBy('risk_id')->get();

        $scenarios = RiskScenario::query()->with(['impacts.risk:id,risk_id,risk_title'])->latest()->get();
        $maturityAssessments = RiskMaturityAssessment::query()->latest('assessment_period')->orderBy('domain')->get();

        $summary = [
            'scenarios' => $scenarios->count(),
            'approved_scenarios' => $scenarios->where('status', 'approved')->count(),
            'scenario_impacts' => $scenarios->flatMap->impacts->count(),
            'maturity_gaps' => $maturityAssessments->filter(fn ($item) => $item->current_score < $item->target_score)->count(),
        ];

        return view('strategic-risk-reviews.index', [
            'scenarios' => $scenarios,
            'maturityAssessments' => $maturityAssessments,
            'risks' => $risks,
            'summary' => $summary,
            'canManage' => $user->isComplianceDivision(),
            'scenarioTypes' => RiskScenario::TYPES,
            'scenarioStatuses' => RiskScenario::STATUS_OPTIONS,
            'maturityStatuses' => RiskMaturityAssessment::STATUS_OPTIONS,
        ]);
    }

    public function storeScenario(Request $request): RedirectResponse
    {
        $this->ensureCanManage();
        $validated = $this->validateScenario($request);
        $validated['scenario_code'] = $this->scenarioCode($validated['scenario_type']);
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $scenario = RiskScenario::create($validated);
        AuditLogger::logModelEvent('scenario.created', $scenario, null, $scenario->toArray());

        return redirect()->route('strategic-risk-reviews.index')->with('success', 'Risk scenario created.');
    }

    public function storeScenarioImpact(Request $request, RiskScenario $scenario): RedirectResponse
    {
        $this->ensureCanManage();
        $validated = $request->validate([
            'risk_id' => ['required', 'integer', 'exists:risks,id'],
            'scenario_probability' => ['nullable', 'integer', 'between:1,5'],
            'scenario_impact' => ['nullable', 'integer', 'between:1,5'],
            'assumptions' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'mitigation_response' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
        ], [
            'assumptions.regex' => InputValidation::safeTextMessage(),
            'mitigation_response.regex' => InputValidation::safeTextMessage(),
        ]);

        $score = !is_null($validated['scenario_probability']) && !is_null($validated['scenario_impact'])
            ? (float) ($validated['scenario_probability'] * $validated['scenario_impact'])
            : null;

        $impact = RiskScenarioImpact::updateOrCreate(
            ['risk_scenario_id' => $scenario->id, 'risk_id' => $validated['risk_id']],
            array_merge($validated, ['scenario_score' => $score])
        );

        AuditLogger::logModelEvent('scenario.impact.saved', $scenario, null, [
            'impact_id' => $impact->id,
            'risk_id' => $impact->risk_id,
            'scenario_score' => $impact->scenario_score,
        ]);

        return redirect()->route('strategic-risk-reviews.index')->with('success', 'Scenario impact saved.');
    }

    public function storeMaturityAssessment(Request $request): RedirectResponse
    {
        $this->ensureCanManage();
        $validated = $this->validateMaturity($request);
        $validated['assessed_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $assessment = RiskMaturityAssessment::updateOrCreate(
            ['assessment_period' => $validated['assessment_period'], 'domain' => $validated['domain']],
            $validated
        );

        AuditLogger::logModelEvent('maturity.assessment.saved', $assessment, null, $assessment->toArray());

        return redirect()->route('strategic-risk-reviews.index')->with('success', 'Risk maturity assessment saved.');
    }

    private function validateScenario(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'scenario_type' => ['required', 'in:' . implode(',', RiskScenario::TYPES)],
            'status' => ['required', 'in:' . implode(',', RiskScenario::STATUS_OPTIONS)],
            'time_horizon' => ['nullable', 'string', 'max:80', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'likelihood' => ['nullable', 'integer', 'between:1,5'],
            'impact' => ['nullable', 'integer', 'between:1,5'],
            'trigger_event' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'narrative' => ['required', 'string', 'max:10000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'response_strategy' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'owner_name' => ['nullable', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
        ], [
            'title.regex' => InputValidation::safeTextMessage(),
            'time_horizon.regex' => InputValidation::safeTextMessage(),
            'trigger_event.regex' => InputValidation::safeTextMessage(),
            'narrative.regex' => InputValidation::safeTextMessage(),
            'response_strategy.regex' => InputValidation::safeTextMessage(),
            'owner_name.regex' => InputValidation::safeTextMessage(),
        ]);
    }

    private function validateMaturity(Request $request): array
    {
        return $request->validate([
            'assessment_period' => ['required', 'string', 'max:40', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'domain' => ['required', 'string', 'max:120', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'current_score' => ['required', 'integer', 'between:1,5'],
            'target_score' => ['required', 'integer', 'between:1,5', 'gte:current_score'],
            'evidence' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'improvement_plan' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'owner_name' => ['nullable', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'target_date' => ['nullable', 'date'],
            'status' => ['required', 'in:' . implode(',', RiskMaturityAssessment::STATUS_OPTIONS)],
        ], [
            'assessment_period.regex' => InputValidation::safeTextMessage(),
            'domain.regex' => InputValidation::safeTextMessage(),
            'evidence.regex' => InputValidation::safeTextMessage(),
            'improvement_plan.regex' => InputValidation::safeTextMessage(),
            'owner_name.regex' => InputValidation::safeTextMessage(),
        ]);
    }

    private function scenarioCode(string $type): string
    {
        return 'SCN-' . strtoupper(Str::slug($type, '-')) . '-' . now()->format('Y') . '-' . Str::upper(Str::random(5));
    }

    private function ensureCanManage(): void
    {
        abort_unless(optional(auth()->user())->isComplianceDivision(), 403, 'Only compliance may manage strategic risk reviews.');
    }
}
