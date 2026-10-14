<?php

namespace App\Http\Controllers;

use App\BusinessService;
use App\Risk;
use App\Support\AuditLogger;
use App\Support\BusinessContinuity;
use App\Support\RiskAccess;
use App\Support\RiskOwnerAccess;
use App\Support\ServiceAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Business continuity per service: catalogue, BIA, linked risks, BCP and
 * test history (ISO 22301 cl. 8.2–8.5).
 */
class BusinessContinuityController extends Controller
{
    private const TEXT = 'nullable|string|max:5000';

    public function index(Request $request): View
    {
        $user = $request->user();
        $query = BusinessService::query()->with(['bia', 'bcp', 'latestTest'])->withCount('risks');
        ServiceAccess::applyVisibilityScope($query, $user);

        $services = $query->orderByRaw("CASE status WHEN 'active' THEN 0 WHEN 'draft' THEN 1 ELSE 2 END")
            ->orderBy('code')
            ->get();

        $active = $services->where('status', '!=', 'retired');
        $stats = [
            'services' => $active->count(),
            'tier1' => $active->filter(fn ($s) => optional($s->bia)->tier === 'Tier 1')->count(),
            'pending' => $active->filter(fn ($s) => in_array($s->continuityStatus(), ['no_bia', 'bia_pending_approval'], true))->count(),
            'attention' => $active->filter(fn ($s) => in_array($s->continuityStatus(), ['no_bcp', 'never_tested', 'test_overdue', 'last_test_failed'], true))->count(),
        ];

        if ($request->filled('tier')) {
            $services = $services->filter(fn ($s) => optional($s->bia)->tier === $request->query('tier'));
        }
        if ($request->filled('state')) {
            $services = $services->filter(fn ($s) => $s->continuityStatus() === $request->query('state'));
        }

        return view('bcm.index', [
            'services' => $services->values(),
            'stats' => $stats,
            'manageableUnits' => ServiceAccess::manageableUnits($user),
            'statusLabels' => BusinessService::continuityStatusLabels(),
        ]);
    }

    public function storeService(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'owner_unit' => ['required', 'string', Rule::in(ServiceAccess::manageableUnits($user))],
            'description' => self::TEXT,
        ]);

        $service = DB::transaction(function () use ($validated, $user) {
            $service = BusinessService::create($validated + [
                'code' => $this->nextCode(),
                'status' => 'active',
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
            AuditLogger::logModelEvent('bcm.service.created', $service, null, $service->only(['code', 'name', 'owner_unit']));

            return $service;
        });

        return redirect()->route('bcm.services.show', $service)->with('success', 'Layanan ' . $service->code . ' ditambahkan. Lanjutkan dengan mengisi BIA.');
    }

    public function show(Request $request, BusinessService $service): View
    {
        $user = $request->user();
        abort_unless(ServiceAccess::canView($user, $service), 404);

        $service->load(['bia.assessor', 'bia.approver', 'bcp.updater', 'tests.recorder', 'risks' => function ($query) {
            $query->with('residualAnalysis', 'inherentAnalysis')->orderBy('risks.risk_id');
        }]);

        $canManage = ServiceAccess::canManage($user, $service);
        $linkableRisks = collect();
        if ($canManage) {
            $riskQuery = Risk::query()
                ->whereNotIn('id', $service->risks->pluck('id'))
                ->whereRaw("LOWER(COALESCE(status, 'open')) <> 'closed'")
                ->orderByRaw('CASE WHEN risk_owner = ? THEN 0 ELSE 1 END', [$service->owner_unit])
                ->orderBy('risk_id');
            RiskAccess::applyVisibilityScope($riskQuery, $user);
            $linkableRisks = $riskQuery->limit(500)->get(['id', 'risk_id', 'risk_title', 'risk_owner']);
        }

        $latest = $service->tests->first();

        return view('bcm.show', [
            'service' => $service,
            'canManage' => $canManage,
            'canApprove' => ServiceAccess::canApprove($user, $service, $service->bia),
            'linkableRisks' => $linkableRisks,
            'ownerUnits' => $canManage ? array_values(array_unique(array_merge([$service->owner_unit], ServiceAccess::manageableUnits($user)))) : [],
            'status' => $service->continuityStatus(),
            'statusLabels' => BusinessService::continuityStatusLabels(),
            'nextTestDue' => $service->bcp && $latest ? BusinessContinuity::nextTestDue($service->bcp->testing_frequency, $latest->tested_at) : null,
        ]);
    }

    public function updateService(Request $request, BusinessService $service): RedirectResponse
    {
        $this->authorizeManage($request, $service);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'owner_unit' => ['required', 'string', Rule::in(array_merge([$service->owner_unit], ServiceAccess::manageableUnits($request->user())))],
            'description' => self::TEXT,
            'status' => ['required', Rule::in(array_keys(BusinessService::STATUSES))],
            'dependency_systems' => self::TEXT,
            'dependency_vendors' => self::TEXT,
            'dependency_sites' => self::TEXT,
            'key_personnel' => self::TEXT,
        ]);

        $before = $service->only(array_keys($validated));
        $service->update($validated + ['updated_by' => $request->user()->id]);
        AuditLogger::logModelEvent('bcm.service.updated', $service, $before, $service->only(array_keys($validated)));

        return $this->backTo($service, 'profil', 'Profil layanan disimpan.');
    }

    public function updateBia(Request $request, BusinessService $service): RedirectResponse
    {
        $this->authorizeManage($request, $service);
        $validated = $request->validate([
            'impact' => 'required|array',
            'rto_minutes' => ['required', 'integer', Rule::in(array_keys(BusinessContinuity::RTO_OPTIONS))],
            'rpo_minutes' => ['required', 'integer', Rule::in(array_keys(BusinessContinuity::RPO_OPTIONS))],
            'minimum_resources' => self::TEXT,
            'notes' => self::TEXT,
            'next_review_at' => 'required|date|after:today',
        ], [
            'next_review_at.after' => 'Tanggal review berikutnya harus setelah hari ini.',
        ]);

        $matrix = [];
        foreach (array_keys(BusinessContinuity::HORIZONS) as $minutes) {
            foreach (array_keys(BusinessContinuity::CATEGORIES) as $category) {
                $matrix[$minutes][$category] = (int) ($validated['impact'][$minutes][$category] ?? 0);
            }
        }
        $errors = BusinessContinuity::matrixErrors($matrix);

        $mtpd = BusinessContinuity::mtpdMinutes($matrix);
        $rto = (int) $validated['rto_minutes'];
        if ($mtpd !== null && $rto > $mtpd) {
            $errors['rto_minutes'] = 'RTO (' . BusinessContinuity::durationLabel($rto) . ') tidak boleh melebihi MTPD ('
                . BusinessContinuity::durationLabel($mtpd) . '). Layanan harus pulih sebelum dampaknya menjadi tidak dapat diterima.';
        }
        if (!empty($errors)) {
            throw ValidationException::withMessages($errors)->errorBag('bia');
        }

        $attributes = [
            'impact_matrix' => $matrix,
            'mtpd_minutes' => $mtpd,
            'tier' => BusinessContinuity::tierFor($mtpd),
            'rto_minutes' => $rto,
            'rpo_minutes' => (int) $validated['rpo_minutes'],
            'minimum_resources' => $validated['minimum_resources'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'next_review_at' => $validated['next_review_at'],
        ];

        $bia = $service->bia;
        $before = $bia ? $bia->only(array_keys($attributes)) : null;
        $changed = !$bia || $this->biaChanged($bia, $attributes);

        $bia = $service->bia()->updateOrCreate(['business_service_id' => $service->id], $attributes + (
            $changed
                // Any substantive change needs a fresh approval.
                ? ['assessed_by' => $request->user()->id, 'assessed_at' => now(), 'approved_by' => null, 'approved_at' => null]
                : []
        ));
        AuditLogger::logModelEvent('bcm.bia.saved', $bia, $before, $bia->only(array_keys($attributes)));

        return $this->backTo($service, 'bia', $changed
            ? 'BIA disimpan. ' . $attributes['tier'] . ', MTPD ' . BusinessContinuity::durationLabel($mtpd) . '. Minta pengelola lain di unit ini untuk menyetujui.'
            : 'Tidak ada perubahan pada BIA.');
    }

    public function approveBia(Request $request, BusinessService $service): RedirectResponse
    {
        $bia = $service->bia;
        abort_unless(ServiceAccess::canApprove($request->user(), $service, $bia), 403, 'BIA harus disetujui oleh pengelola unit selain pengisinya.');
        abort_if($bia->approved_at !== null, 422, 'BIA ini sudah disetujui.');

        $bia->update(['approved_by' => $request->user()->id, 'approved_at' => now()]);
        AuditLogger::logModelEvent('bcm.bia.approved', $bia, null, ['tier' => $bia->tier, 'rto_minutes' => $bia->rto_minutes]);

        return $this->backTo($service, 'bia', 'BIA disetujui.');
    }

    public function attachRisk(Request $request, BusinessService $service): RedirectResponse
    {
        $this->authorizeManage($request, $service);
        $validated = $request->validate(['risk_id' => 'required|integer|exists:risks,id']);

        $risk = Risk::findOrFail($validated['risk_id']);
        abort_unless(RiskAccess::canView($request->user(), $risk), 404);

        $service->risks()->syncWithoutDetaching([$risk->id => ['linked_by' => $request->user()->id]]);
        AuditLogger::logModelEvent('bcm.risk.linked', $service, null, ['risk_id' => $risk->risk_id]);

        return $this->backTo($service, 'risiko', 'Risk ' . $risk->risk_id . ' ditautkan ke layanan ini.');
    }

    public function detachRisk(Request $request, BusinessService $service, Risk $risk): RedirectResponse
    {
        $this->authorizeManage($request, $service);
        $service->risks()->detach($risk->id);
        AuditLogger::logModelEvent('bcm.risk.unlinked', $service, null, ['risk_id' => $risk->risk_id]);

        return $this->backTo($service, 'risiko', 'Tautan ke risk ' . $risk->risk_id . ' dihapus.');
    }

    /**
     * Link a risk to a service from the risk's own page.
     */
    public function attachFromRisk(Request $request, Risk $risk): RedirectResponse
    {
        abort_unless(RiskAccess::canView($request->user(), $risk), 404);
        $validated = $request->validate(['business_service_id' => 'required|integer|exists:business_services,id']);
        $service = BusinessService::findOrFail($validated['business_service_id']);
        $this->authorizeManage($request, $service);

        $service->risks()->syncWithoutDetaching([$risk->id => ['linked_by' => $request->user()->id]]);
        AuditLogger::logModelEvent('bcm.risk.linked', $service, null, ['risk_id' => $risk->risk_id]);

        return redirect()->to(route('risks.show', $risk->id) . '#services')->with('success', 'Risk ditautkan ke layanan ' . $service->name . '.');
    }

    public function updateBcp(Request $request, BusinessService $service): RedirectResponse
    {
        $this->authorizeManage($request, $service);
        abort_unless($service->bia, 422, 'Isi BIA lebih dulu: BCP disusun untuk memenuhi RTO dari BIA.');

        $validated = $request->validate([
            'activation_criteria' => 'required|string|max:5000',
            'strategy_summary' => 'required|string|max:5000',
            'recovery_procedures' => 'required|string|max:5000',
            'primary_workaround' => self::TEXT,
            'contacts' => 'required|string|max:5000',
            'testing_frequency' => ['required', Rule::in(array_keys(BusinessContinuity::TESTING_FREQUENCIES))],
        ]);

        $bcp = $service->bcp()->updateOrCreate(['business_service_id' => $service->id], $validated + ['updated_by' => $request->user()->id]);
        AuditLogger::logModelEvent('bcm.bcp.saved', $bcp, null, ['testing_frequency' => $bcp->testing_frequency]);

        return $this->backTo($service, 'bcp', 'BCP disimpan.');
    }

    public function storeTest(Request $request, BusinessService $service): RedirectResponse
    {
        $this->authorizeManage($request, $service);
        abort_unless($service->bcp, 422, 'Susun BCP lebih dulu sebelum mencatat pengujian.');

        $validated = $request->validate([
            'tested_at' => 'required|date|before_or_equal:today',
            'test_type' => ['required', Rule::in(array_keys(BusinessContinuity::TEST_TYPES))],
            'result' => ['required', Rule::in(array_keys(BusinessContinuity::TEST_RESULTS))],
            'actual_recovery_minutes' => 'nullable|integer|min:0|max:525600',
            'findings' => self::TEXT,
            'follow_up' => self::TEXT,
        ]);

        $test = $service->tests()->create($validated + ['recorded_by' => $request->user()->id]);
        AuditLogger::logModelEvent('bcm.test.recorded', $test, null, $test->only(['tested_at', 'test_type', 'result', 'actual_recovery_minutes']));

        return $this->backTo($service, 'uji', 'Hasil uji dicatat.');
    }

    // ---------------------------------------------------------------------

    private function authorizeManage(Request $request, BusinessService $service): void
    {
        abort_unless(ServiceAccess::canView($request->user(), $service), 404);
        abort_unless(ServiceAccess::canManage($request->user(), $service), 403, 'Anda tidak berwenang mengubah layanan ini.');
    }

    private function biaChanged($bia, array $attributes): bool
    {
        foreach ($attributes as $key => $value) {
            $current = $bia->{$key};
            if ($current instanceof \Carbon\CarbonInterface) {
                $current = $current->toDateString();
            }
            if ($key === 'impact_matrix') {
                $current = json_encode($current);
                $value = json_encode($value);
            }
            if ((string) $current !== (string) $value) {
                return true;
            }
        }

        return false;
    }

    private function backTo(BusinessService $service, string $section, string $message): RedirectResponse
    {
        return redirect()->to(route('bcm.services.show', $service) . '#' . $section)->with('success', $message);
    }

    private function nextCode(): string
    {
        $max = BusinessService::query()->where('code', 'like', 'SVC-%')->pluck('code')
            ->map(fn ($code) => (int) preg_replace('/\D/', '', $code))
            ->max() ?? 0;

        return sprintf('SVC-%03d', $max + 1);
    }

    public static function unitLabel(?string $unit): string
    {
        return RiskOwnerAccess::displayLabel($unit);
    }
}
