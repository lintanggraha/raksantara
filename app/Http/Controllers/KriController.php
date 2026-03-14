<?php

namespace App\Http\Controllers;

use App\Kri;
use App\KriLog;
use App\Risk;
use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class KriController extends Controller
{
    /** @var array<int>|null */
    private ?array $visibleRiskIdsCache = null;
    public function index(Request $request)
    {
        $user = Auth::user();
        $statusFilter = $request->input('status');
        $riskIdFilter = $request->input('risk_id');

        $query = Kri::query()->with(['risk', 'updater']);
        $this->applyVisibilityScope($query, $user);

        if (in_array($statusFilter, ['Green', 'Amber', 'Red'], true)) {
            $query->where('status', $statusFilter);
        }

        if (!empty($riskIdFilter) && ctype_digit((string) $riskIdFilter)) {
            $query->where('risk_id', (int) $riskIdFilter);
        }

        $summaryQuery = Kri::query();
        $this->applyVisibilityScope($summaryQuery, $user);

        $summary = [
            'total' => (clone $summaryQuery)->count(),
            'green' => (clone $summaryQuery)->where('status', 'Green')->count(),
            'amber' => (clone $summaryQuery)->where('status', 'Amber')->count(),
            'red' => (clone $summaryQuery)->where('status', 'Red')->count(),
        ];

        $kris = $query
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->appends($request->query());

        $riskQuery = Risk::query()->select(['id', 'risk_id', 'risk_title', 'risk_owner']);
        $this->applyRiskVisibilityScope($riskQuery, $user);

        $availableRisks = $riskQuery
            ->orderByDesc('id')
            ->limit(500)
            ->get();

        $canManage = !is_null($user);

        return view('kris.index', compact(
            'kris',
            'summary',
            'availableRisks',
            'canManage',
            'statusFilter',
            'riskIdFilter'
        ));
    }

    public function store(Request $request, Risk $risk)
    {
        $this->ensureCanManageRisk($risk);

        $validated = $request->validate([
            'metric_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'threshold_type' => 'required|in:lower_is_better,higher_is_better',
            'threshold_warn' => 'nullable|numeric',
            'threshold_critical' => 'nullable|numeric',
        ]);

        $this->validateThresholdConsistency($validated);

        $kri = new Kri($validated);
        $kri->risk_id = $risk->id;
        $kri->last_updated_by = Auth::id();
        $kri->save();

        if (class_exists(AuditLogger::class)) {
            AuditLogger::logModelEvent('kri.created', $kri, null, $kri->toArray());
        }

        return back()->with('success', 'Key Risk Indicator added successfully.');
    }

    public function update(Request $request, Kri $kri)
    {
        $kri->loadMissing('risk');
        if (!$kri->risk) {
            abort(404, 'Parent risk for this KRI was not found.');
        }

        $this->ensureCanManageRisk($kri->risk);

        $validated = $request->validate([
            'metric_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'threshold_type' => 'required|in:lower_is_better,higher_is_better',
            'threshold_warn' => 'nullable|numeric',
            'threshold_critical' => 'nullable|numeric',
        ]);

        $this->validateThresholdConsistency($validated);

        $before = $kri->toArray();

        $kri->fill($validated);
        $kri->last_updated_by = Auth::id();

        if ($kri->current_value !== null) {
            $kri->status = $kri->calculateStatusForValue($kri->current_value);
        }

        $kri->save();

        if (class_exists(AuditLogger::class)) {
            AuditLogger::logModelEvent('kri.updated', $kri, $before, $kri->fresh()->toArray());
        }

        return back()->with('success', 'Key Risk Indicator updated successfully.');
    }

    public function destroy(Kri $kri)
    {
        $kri->loadMissing('risk');
        if (!$kri->risk) {
            abort(404, 'Parent risk for this KRI was not found.');
        }

        $this->ensureCanManageRisk($kri->risk);

        $before = $kri->toArray();
        $kri->delete();

        if (class_exists(AuditLogger::class)) {
            AuditLogger::logModelEvent('kri.deleted', $kri, $before, null);
        }

        return back()->with('success', 'Key Risk Indicator removed.');
    }

    public function logValue(Request $request, Kri $kri)
    {
        $kri->loadMissing('risk');
        if (!$kri->risk) {
            abort(404, 'Parent risk for this KRI was not found.');
        }

        $this->ensureCanManageRisk($kri->risk);

        $request->validate([
            'recorded_value' => 'required|numeric',
            'notes' => 'nullable|string'
        ]);

        $value = $request->input('recorded_value');
        $status = $kri->calculateStatusForValue($value);

        $beforeKri = $kri->toArray();

        $log = new KriLog([
            'kri_id' => $kri->id,
            'recorded_value' => $value,
            'status_calculated' => $status,
            'notes' => $request->input('notes'),
            'recorded_by' => Auth::id(),
        ]);
        $log->save();

        $kri->current_value = $value;
        $kri->status = $status;
        $kri->last_updated_by = Auth::id();
        $kri->save();

        if (class_exists(AuditLogger::class)) {
            AuditLogger::logModelEvent('kri.value_logged', $kri, $beforeKri, $kri->fresh()->toArray());
        }

        return back()->with('success', 'New KRI reading logged safely. Status: ' . $status);
    }

    /**
     * Scope KRI query by user's risk-owner visibility.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $user
     * @return void
     */
    private function applyVisibilityScope(Builder $query, $user): void
    {
        if (is_null($user)) {
            $query->whereRaw('1 = 0');
            return;
        }

        if ($user->isSuperadmin()) {
            return;
        }

        $visibleRiskIds = $this->resolveVisibleRiskIds($user);
        if (empty($visibleRiskIds)) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->whereIn('risk_id', $visibleRiskIds);
    }

    /**
     * Scope risk query by user's visibility.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  mixed  $user
     * @return void
     */
    private function applyRiskVisibilityScope(Builder $query, $user): void
    {
        if (is_null($user)) {
            $query->whereRaw('1 = 0');
            return;
        }

        if ($user->isSuperadmin()) {
            return;
        }

        $visibleRiskIds = $this->resolveVisibleRiskIds($user);
        if (empty($visibleRiskIds)) {
            $query->whereRaw('1 = 0');
            return;
        }

        $query->whereIn('id', $visibleRiskIds);
    }

    /**
     * Resolve visible risk IDs for the current user using owner/creator checks.
     *
     * @param mixed $user
     * @return array<int>
     */
    private function resolveVisibleRiskIds($user): array
    {
        if (is_null($user)) {
            return [];
        }

        if (!is_null($this->visibleRiskIdsCache)) {
            return $this->visibleRiskIdsCache;
        }

        $userId = (int) $user->id;

        $ids = Risk::query()
            ->select(['id', 'risk_owner', 'created_by'])
            ->get()
            ->filter(function (Risk $risk) use ($user, $userId) {
                $isCreator = (int) ($risk->created_by ?? 0) > 0
                    && (int) $risk->created_by === $userId;

                return $isCreator || $user->canManageRiskOwner($risk->risk_owner);
            })
            ->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->values()
            ->all();

        $this->visibleRiskIdsCache = $ids;

        return $ids;
    }

    /**
     * Ensure actor can modify a risk and its KRI.
     *
     * @param  \App\Risk  $risk
     * @return void
     */
    private function ensureCanManageRisk(Risk $risk): void
    {
        $user = Auth::user();
        $isCreator = !is_null($user)
            && (int) ($risk->created_by ?? 0) > 0
            && (int) $risk->created_by === (int) $user->id;
        $allowOwnerManage = !is_null($user) && $user->canManageRiskOwner($risk->risk_owner);

        abort_unless(
            !is_null($user) && ($user->isSuperadmin() || $isCreator || $allowOwnerManage),
            403,
            'You are not allowed to modify this KRI.'
        );
    }

    /**
     * Validate warn/critical threshold pair consistency.
     *
     * @param  array<string, mixed>  $payload
     * @return void
     */
    private function validateThresholdConsistency(array $payload): void
    {
        $warn = array_key_exists('threshold_warn', $payload) && $payload['threshold_warn'] !== null
            ? (float) $payload['threshold_warn']
            : null;
        $critical = array_key_exists('threshold_critical', $payload) && $payload['threshold_critical'] !== null
            ? (float) $payload['threshold_critical']
            : null;

        if (is_null($warn) || is_null($critical)) {
            return;
        }

        $type = (string) ($payload['threshold_type'] ?? 'lower_is_better');

        if ($type === 'lower_is_better' && $critical < $warn) {
            throw ValidationException::withMessages([
                'threshold_critical' => 'Critical threshold must be greater than or equal to warning threshold for "lower is better" metrics.',
            ]);
        }

        if ($type === 'higher_is_better' && $critical > $warn) {
            throw ValidationException::withMessages([
                'threshold_critical' => 'Critical threshold must be less than or equal to warning threshold for "higher is better" metrics.',
            ]);
        }
    }
}


