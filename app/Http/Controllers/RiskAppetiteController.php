<?php

namespace App\Http\Controllers;

use App\RiskAppetite;
use App\Risk;
use App\Support\RiskAccess;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RiskAppetiteController extends Controller
{
    public function __construct()
    {
        $this->middleware('superadmin')->except(['index', 'show']);
    }

    public function index()
    {
        $appetites = RiskAppetite::orderBy('risk_category')->orderBy('name')->get();
        return view('risk-appetites.index', compact('appetites'));
    }

    public function show(RiskAppetite $riskAppetite)
    {
        $linkedRisksQuery = Risk::query()
            ->where('risk_appetite_id', $riskAppetite->id)
            ->with(['residualAnalysis', 'vendor'])
            ->orderByDesc('id');

        RiskAccess::applyVisibilityScope($linkedRisksQuery, auth()->user());

        $linkedRisks = $linkedRisksQuery->get();

        return view('risk-appetites.show', compact('riskAppetite', 'linkedRisks'));
    }

    public function create()
    {
        $metricOptions = RiskAppetite::thresholdMetricOptions();

        return view('risk-appetites.create', compact('metricOptions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);

        $validated['created_by'] = auth()->id();
        RiskAppetite::create($validated);

        return redirect()->route('risk-appetites.index')
                         ->with('success', 'Corporate Risk Appetite entry created successfully.');
    }

    public function edit(RiskAppetite $riskAppetite)
    {
        $metricOptions = RiskAppetite::thresholdMetricOptions();
        $linkedRiskCount = $riskAppetite->risks()->count();

        return view('risk-appetites.edit', compact('riskAppetite', 'metricOptions', 'linkedRiskCount'));
    }

    public function update(Request $request, RiskAppetite $riskAppetite)
    {
        $validated = $this->validatePayload($request);

        $riskAppetite->update($validated);

        return redirect()->route('risk-appetites.index')
                         ->with('success', 'Risk Appetite updated successfully.');
    }

    public function destroy(RiskAppetite $riskAppetite)
    {
        if ($riskAppetite->risks()->exists()) {
            return redirect()
                ->back()
                ->with('error', 'Risk Appetite cannot be deleted while it is still linked to risks. Deactivate it instead.');
        }

        $riskAppetite->delete();
        return redirect()->route('risk-appetites.index')
                         ->with('success', 'Risk Appetite deleted successfully.');
    }

    private function validatePayload(Request $request): array
    {
        $thresholdMetric = (string) $request->input(
            'threshold_metric',
            RiskAppetite::THRESHOLD_METRIC_QUALITATIVE
        );

        $isQuantitativeMetric = in_array($thresholdMetric, [
            RiskAppetite::THRESHOLD_METRIC_RESIDUAL_SCORE,
            RiskAppetite::THRESHOLD_METRIC_RESIDUAL_LOSS,
        ], true);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'risk_category' => 'nullable|string|max:255',
            'threshold_metric' => ['nullable', 'string', Rule::in(RiskAppetite::thresholdMetricValues())],
            'appetite_statement' => 'required|string',
            'appetite_limit_value' => [
                'nullable',
                'numeric',
                'min:0',
                Rule::requiredIf($isQuantitativeMetric),
            ],
            'tolerance_limit_value' => [
                'nullable',
                'numeric',
                'min:0',
                'gte:appetite_limit_value',
                Rule::requiredIf($isQuantitativeMetric),
            ],
            'unit' => [
                'nullable',
                'string',
                'max:100',
                Rule::requiredIf($thresholdMetric === RiskAppetite::THRESHOLD_METRIC_RESIDUAL_LOSS),
            ],
            'is_active' => 'boolean',
        ], [
            'tolerance_limit_value.gte' => 'Maximum tolerance limit must be greater than or equal to the appetite target.',
        ]);

        $validated['threshold_metric'] = in_array($thresholdMetric, RiskAppetite::thresholdMetricValues(), true)
            ? $thresholdMetric
            : RiskAppetite::THRESHOLD_METRIC_QUALITATIVE;

        if (!$request->has('is_active')) {
            $validated['is_active'] = false;
        }

        if ($validated['threshold_metric'] === RiskAppetite::THRESHOLD_METRIC_QUALITATIVE) {
            $validated['appetite_limit_value'] = null;
            $validated['tolerance_limit_value'] = null;
            $validated['unit'] = null;
        } elseif ($validated['threshold_metric'] === RiskAppetite::THRESHOLD_METRIC_RESIDUAL_SCORE
            && trim((string) ($validated['unit'] ?? '')) === '') {
            $validated['unit'] = 'Score';
        }

        return $validated;
    }
}
