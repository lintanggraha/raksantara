<?php

namespace App\Http\Controllers;

use App\Risk;
use App\RiskAppetite;
use App\RiskCategory;
use App\Support\AuditLogger;
use App\Support\RiskAccess;
use App\Support\RiskAppetiteSql;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Risk appetite statements per risk category (COSO ERM 2017 principle 7;
 * FSB Principles for an Effective Risk Appetite Framework). Maintained by the
 * risk management function; Board approval is recorded on each statement.
 */
class RiskAppetiteController extends Controller
{
    public function index(Request $request)
    {
        $appetites = RiskAppetite::query()
            ->with('category')
            ->withCount('risks')
            ->orderByDesc('is_active')
            ->orderByRaw('CASE WHEN risk_category_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('name')
            ->get();

        // Coverage of the viewer's active risks, aggregated in the database.
        $rows = RiskAppetiteSql::joinSources($this->visibleRisks($request))
            ->selectRaw('ra.id as appetite_id, (' . RiskAppetiteSql::statusExpression() . ') as appetite_status, COUNT(*) as total')
            ->groupBy('appetite_id', 'appetite_status')
            ->get();

        $perAppetite = [];
        $totals = ['risks' => 0, 'covered' => 0, 'breach' => 0, 'warning' => 0, 'within' => 0];
        foreach ($rows as $row) {
            $count = (int) $row->total;
            $totals['risks'] += $count;
            if ($row->appetite_id === null) {
                continue;
            }
            $totals['covered'] += $count;
            $perAppetite[$row->appetite_id] ??= ['covered' => 0, 'breach' => 0, 'warning' => 0];
            $perAppetite[$row->appetite_id]['covered'] += $count;
            if (isset($totals[$row->appetite_status])) {
                $totals[$row->appetite_status] += $count;
            }
            if (in_array($row->appetite_status, ['breach', 'warning'], true)) {
                $perAppetite[$row->appetite_id][$row->appetite_status] += $count;
            }
        }

        $coveredCategoryIds = $appetites->where('is_active', true)->pluck('risk_category_id');
        $hasDefault = $coveredCategoryIds->contains(null);
        $uncoveredCategories = $hasDefault
            ? collect()
            : RiskCategory::query()
                ->whereNotIn('id', $coveredCategoryIds->filter())
                ->whereHas('risks')
                ->orderBy('name')
                ->pluck('name');

        return view('risk-appetites.index', [
            'appetites' => $appetites,
            'perAppetite' => $perAppetite,
            'totals' => $totals,
            'hasDefault' => $hasDefault,
            'uncoveredCategories' => $uncoveredCategories,
            'canManage' => RiskAppetite::canManage($request->user()),
        ]);
    }

    public function show(Request $request, RiskAppetite $riskAppetite)
    {
        $riskAppetite->load('category');

        $risks = RiskAppetiteSql::joinSources($this->visibleRisks($request))
            ->whereRaw('(' . RiskAppetiteSql::effectiveIdExpression() . ') = ?', [$riskAppetite->id])
            ->select([
                'risks.id', 'risks.risk_id', 'risks.risk_title', 'risks.risk_owner', 'risks.risk_appetite_id',
                'rr.residual_risk_score', 'rr.potential_loss',
            ])
            ->selectRaw('(' . RiskAppetiteSql::statusExpression() . ') as appetite_status')
            ->orderByRaw("CASE (" . RiskAppetiteSql::statusExpression() . ") WHEN 'breach' THEN 0 WHEN 'warning' THEN 1 ELSE 2 END")
            ->orderByDesc('rr.residual_risk_score')
            ->limit(500)
            ->get();

        return view('risk-appetites.show', [
            'riskAppetite' => $riskAppetite,
            'risks' => $risks,
            'canManage' => RiskAppetite::canManage($request->user()),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorizeManage($request);

        return view('risk-appetites.create', $this->formData(new RiskAppetite(['is_active' => true, 'threshold_metric' => RiskAppetite::THRESHOLD_METRIC_RESIDUAL_SCORE])));
    }

    public function store(Request $request)
    {
        $this->authorizeManage($request);
        $validated = $this->validatePayload($request);

        $appetite = RiskAppetite::create($validated + [
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);
        AuditLogger::logModelEvent('risk_appetite.created', $appetite, null, $appetite->only(array_keys($validated)));

        return redirect()->route('risk-appetites.show', $appetite)->with('success', 'Risk appetite "' . $appetite->name . '" disimpan.');
    }

    public function edit(Request $request, RiskAppetite $riskAppetite)
    {
        $this->authorizeManage($request);

        return view('risk-appetites.edit', $this->formData($riskAppetite) + [
            'overrideCount' => $riskAppetite->risks()->count(),
        ]);
    }

    public function update(Request $request, RiskAppetite $riskAppetite)
    {
        $this->authorizeManage($request);
        $validated = $this->validatePayload($request, $riskAppetite);

        $before = $riskAppetite->only(array_keys($validated));
        $riskAppetite->update($validated + ['updated_by' => $request->user()->id]);
        AuditLogger::logModelEvent('risk_appetite.updated', $riskAppetite, $before, $riskAppetite->only(array_keys($validated)));

        return redirect()->route('risk-appetites.show', $riskAppetite)->with('success', 'Risk appetite diperbarui.');
    }

    public function destroy(Request $request, RiskAppetite $riskAppetite)
    {
        $this->authorizeManage($request);

        if ($riskAppetite->risks()->exists()) {
            return redirect()
                ->back()
                ->with('error', 'Appetite ini masih dipakai sebagai override oleh beberapa risk. Nonaktifkan saja, atau ubah override di risk tersebut lebih dulu.');
        }

        AuditLogger::logModelEvent('risk_appetite.deleted', $riskAppetite, $riskAppetite->toArray(), null);
        $riskAppetite->delete();

        return redirect()->route('risk-appetites.index')->with('success', 'Risk appetite dihapus.');
    }

    // ---------------------------------------------------------------------

    private function visibleRisks(Request $request): Builder
    {
        $query = Risk::query()->whereRaw("LOWER(COALESCE(risks.status, 'open')) <> 'closed'");
        RiskAccess::applyVisibilityScope($query, $request->user(), 'risks.risk_owner', 'risks.created_by');

        return $query;
    }

    private function authorizeManage(Request $request): void
    {
        abort_unless(RiskAppetite::canManage($request->user()), 403, 'Risk appetite dikelola oleh fungsi manajemen risiko (Compliance) atau superadmin.');
    }

    private function formData(RiskAppetite $appetite): array
    {
        return [
            'riskAppetite' => $appetite,
            'metricOptions' => RiskAppetite::thresholdMetricOptions(),
            'categories' => RiskCategory::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    private function validatePayload(Request $request, ?RiskAppetite $current = null): array
    {
        $metric = (string) $request->input('threshold_metric', RiskAppetite::THRESHOLD_METRIC_QUALITATIVE);
        $isQuantitative = in_array($metric, [RiskAppetite::THRESHOLD_METRIC_RESIDUAL_SCORE, RiskAppetite::THRESHOLD_METRIC_RESIDUAL_LOSS], true);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'risk_category_id' => 'nullable|integer|exists:risk_categories,id',
            'threshold_metric' => ['required', 'string', Rule::in(RiskAppetite::thresholdMetricValues())],
            'appetite_statement' => 'required|string|max:5000',
            'appetite_limit_value' => ['nullable', 'numeric', 'min:0', Rule::requiredIf($isQuantitative)],
            'tolerance_limit_value' => ['nullable', 'numeric', 'min:0', 'gte:appetite_limit_value', Rule::requiredIf($isQuantitative)],
            'unit' => ['nullable', 'string', 'max:100', Rule::requiredIf($metric === RiskAppetite::THRESHOLD_METRIC_RESIDUAL_LOSS)],
            'is_active' => 'boolean',
            'approval_reference' => 'nullable|string|max:255|required_with:approved_at',
            'approved_at' => 'nullable|date|before_or_equal:today|required_with:approval_reference',
            'next_review_at' => 'nullable|date|after:today',
        ], [
            'tolerance_limit_value.gte' => 'Batas toleransi harus sama dengan atau lebih besar dari target appetite.',
            'approval_reference.required_with' => 'Isi nomor keputusan pengesahan bersama tanggalnya.',
            'approved_at.required_with' => 'Isi tanggal pengesahan bersama nomor keputusannya.',
            'next_review_at.after' => 'Tanggal review berikutnya harus setelah hari ini.',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['risk_category_id'] = $validated['risk_category_id'] ?? null;

        if ($metric === RiskAppetite::THRESHOLD_METRIC_QUALITATIVE) {
            $validated['appetite_limit_value'] = null;
            $validated['tolerance_limit_value'] = null;
            $validated['unit'] = null;
        } elseif ($metric === RiskAppetite::THRESHOLD_METRIC_RESIDUAL_SCORE) {
            $validated['unit'] = 'Skor';
        }

        // One active statement per category (and one active default).
        if ($validated['is_active']) {
            $clash = RiskAppetite::query()
                ->where('is_active', true)
                ->when($current, fn ($q) => $q->whereKeyNot($current->id))
                ->when(
                    $validated['risk_category_id'] === null,
                    fn ($q) => $q->whereNull('risk_category_id'),
                    fn ($q) => $q->where('risk_category_id', $validated['risk_category_id'])
                )
                ->first();
            if ($clash) {
                throw ValidationException::withMessages([
                    'risk_category_id' => 'Sudah ada appetite aktif untuk cakupan ini ("' . $clash->name . '"). Nonaktifkan yang lama atau pilih kategori lain.',
                ]);
            }
        }

        return $validated;
    }
}
