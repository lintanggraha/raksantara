<?php

namespace App\Http\Controllers;

use App\ImpactCategory;
use App\ImpactScale;
use App\ProbabilityScale;
use App\RadarGroup;
use App\RiskAppetite;
use App\RiskCategory;
use App\RiskCauseCategory;
use App\RiskOwnerParameter;
use App\RiskTreatmentTemplate;
use App\Support\InputValidation;
use App\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ParameterManagementController extends Controller
{
    public function index(Request $request)
    {
        $allowedTabs = $this->parameterTabs();
        $requestedTab = (string) $request->query('tab', 'risk-category');
        $activeTab = in_array($requestedTab, $allowedTabs, true) ? $requestedTab : 'risk-category';

        $riskCategories = RiskCategory::query()->orderBy('name')->get();
        $riskOwners = RiskOwnerParameter::query()->orderBy('sort_order')->orderBy('name')->get();
        $riskAppetiteCount = RiskAppetite::query()->count();
        $causeCategories = RiskCauseCategory::query()->orderBy('name')->get();
        $vendorCount = Vendor::query()->count();
        $probabilityScales = ProbabilityScale::query()
            ->orderBy('sort_order')
            ->orderBy('score')
            ->get();
        $impactCategories = ImpactCategory::query()->with(['scales' => function ($query) {
            $query->orderBy('score')->orderBy('id');
        }])->orderBy('name')->get();
        $playbookCount = RiskTreatmentTemplate::query()->count();
        $radarGroups = RadarGroup::query()
            ->with('quantProfile')
            ->orderBy('code')
            ->orderBy('description')
            ->get();

        return view('parameters.index', compact(
            'riskCategories',
            'riskOwners',
            'riskAppetiteCount',
            'causeCategories',
            'vendorCount',
            'probabilityScales',
            'impactCategories',
            'playbookCount',
            'radarGroups',
            'activeTab'
        ));
    }

    public function storeRiskCategory(Request $request): RedirectResponse
    {
        RiskCategory::query()->create($request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX, 'unique:risk_categories,name'],
        ], [
            'name.regex' => InputValidation::safeTextMessage(),
        ]));

        return $this->redirectSuccess('Risk category added.');
    }

    public function updateRiskCategory(Request $request, RiskCategory $riskCategory): RedirectResponse
    {
        $riskCategory->update($request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:' . InputValidation::SAFE_TEXT_REGEX,
                Rule::unique('risk_categories', 'name')->ignore($riskCategory->id),
            ],
        ], [
            'name.regex' => InputValidation::safeTextMessage(),
        ]));

        return $this->redirectSuccess('Risk category updated.');
    }

    public function storeRiskOwner(Request $request): RedirectResponse
    {
        RiskOwnerParameter::query()->create($request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX, 'unique:risk_owner_parameters,name'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.regex' => InputValidation::safeTextMessage(),
        ]) + [
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->redirectSuccess('Risk owner parameter added.');
    }

    public function updateRiskOwner(Request $request, RiskOwnerParameter $riskOwner): RedirectResponse
    {
        $validated = $request->validate([
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);

        $riskOwner->update([
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        return $this->redirectSuccess('Risk owner parameter updated.');
    }

    public function storeRiskCauseCategory(Request $request): RedirectResponse
    {
        RiskCauseCategory::query()->create($request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX, 'unique:risk_cause_categories,name'],
        ], [
            'name.regex' => InputValidation::safeTextMessage(),
        ]));

        return $this->redirectSuccess('Cause category added.');
    }

    public function updateRiskCauseCategory(Request $request, RiskCauseCategory $riskCauseCategory): RedirectResponse
    {
        $riskCauseCategory->update($request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:' . InputValidation::SAFE_TEXT_REGEX,
                Rule::unique('risk_cause_categories', 'name')->ignore($riskCauseCategory->id),
            ],
        ], [
            'name.regex' => InputValidation::safeTextMessage(),
        ]));

        return $this->redirectSuccess('Cause category updated.');
    }

    public function storeProbabilityScale(Request $request): RedirectResponse
    {
        ProbabilityScale::query()->create($request->validate([
            'score' => ['required', 'integer', 'between:1,5', 'unique:probability_scales,score'],
            'description' => ['required', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'description.regex' => InputValidation::safeTextMessage(),
        ]) + [
            'sort_order' => (int) $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return $this->redirectSuccess('Probability description added.');
    }

    public function updateProbabilityScale(Request $request, ProbabilityScale $probabilityScale): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'description.regex' => InputValidation::safeTextMessage(),
        ]);

        $probabilityScale->update([
            'description' => $validated['description'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]);

        return $this->redirectSuccess('Probability description updated.');
    }

    public function storeImpactCategory(Request $request): RedirectResponse
    {
        ImpactCategory::query()->create($request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX, 'unique:impact_categories,name'],
        ], [
            'name.regex' => InputValidation::safeTextMessage(),
        ]));

        return $this->redirectSuccess('Impact category added.');
    }

    public function updateImpactCategory(Request $request, ImpactCategory $impactCategory): RedirectResponse
    {
        $impactCategory->update($request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:' . InputValidation::SAFE_TEXT_REGEX,
                Rule::unique('impact_categories', 'name')->ignore($impactCategory->id),
            ],
        ], [
            'name.regex' => InputValidation::safeTextMessage(),
        ]));

        return $this->redirectSuccess('Impact category updated.');
    }

    public function storeImpactScale(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'impact_category_id' => ['required', 'integer', 'exists:impact_categories,id'],
            'description' => ['required', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'score' => ['required', 'integer', 'between:1,5'],
        ], [
            'description.regex' => InputValidation::safeTextMessage(),
        ]);

        $this->ensureUniqueImpactScale($validated['impact_category_id'], $validated['score']);

        ImpactScale::query()->create($validated);

        return $this->redirectSuccess('Impact description added.');
    }

    public function updateImpactScale(Request $request, ImpactScale $impactScale): RedirectResponse
    {
        $validated = $request->validate([
            'impact_category_id' => ['required', 'integer', 'exists:impact_categories,id'],
            'description' => ['required', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'score' => ['required', 'integer', 'between:1,5'],
        ], [
            'description.regex' => InputValidation::safeTextMessage(),
        ]);

        $this->ensureUniqueImpactScale($validated['impact_category_id'], $validated['score'], $impactScale->id);

        $impactScale->update($validated);

        return $this->redirectSuccess('Impact description updated.');
    }

    public function storeRadarGroup(Request $request): RedirectResponse
    {
        RadarGroup::query()->create($request->validate([
            'code' => ['required', 'string', 'max:10', 'regex:/^[A-Za-z0-9\\/]+$/', 'unique:radar_groups,code'],
            'description' => ['required', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
        ], [
            'code.regex' => 'Radar code hanya boleh berisi huruf, angka, dan slash (/).',
            'description.regex' => InputValidation::safeTextMessage(),
        ]));

        return $this->redirectSuccess('Group radar description added.');
    }

    public function updateRadarGroup(Request $request, RadarGroup $radarGroup): RedirectResponse
    {
        $radarGroup->update($request->validate([
            'code' => [
                'required',
                'string',
                'max:10',
                'regex:/^[A-Za-z0-9\\/]+$/',
                Rule::unique('radar_groups', 'code')->ignore($radarGroup->id),
            ],
            'description' => ['required', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
        ], [
            'code.regex' => 'Radar code hanya boleh berisi huruf, angka, dan slash (/).',
            'description.regex' => InputValidation::safeTextMessage(),
        ]));

        return $this->redirectSuccess('Group radar description updated.');
    }

    private function ensureUniqueImpactScale(int $impactCategoryId, int $score, ?int $ignoreId = null): void
    {
        $query = ImpactScale::query()
            ->where('impact_category_id', $impactCategoryId)
            ->where('score', $score);

        if (!is_null($ignoreId)) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'score' => 'Score untuk impact category tersebut sudah ada.',
            ]);
        }
    }

    private function redirectSuccess(string $message): RedirectResponse
    {
        $activeTab = (string) request()->input('tab');
        $routeParameters = in_array($activeTab, $this->parameterTabs(), true)
            ? ['tab' => $activeTab]
            : [];

        return redirect()
            ->route('parameters.index', $routeParameters)
            ->with('success', $message);
    }

    private function parameterTabs(): array
    {
        return [
            'risk-category',
            'risk-owner',
            'corporate-risk-appetite',
            'cause-category',
            'third-party-vendor',
            'probability-description',
            'impact-category',
            'impact-description',
            'mitigation-playbook',
            'group-radar-description',
        ];
    }
}
