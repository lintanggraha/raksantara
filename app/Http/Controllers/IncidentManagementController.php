<?php

namespace App\Http\Controllers;

use App\Risk;
use App\RiskIncident;
use App\Support\InputValidation;
use App\Support\RiskOwnerAccess;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class IncidentManagementController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');

        $selectedYear = $request->filled('year') ? (int) $request->input('year') : (int) now()->year;
        $selectedClient = $request->input('affected_client');

        $riskOptionsQuery = Risk::query()
            ->select('id', 'risk_id', 'risk_title', 'risk_owner', 'status', 'year');
        $this->applyRiskScopeForUser($riskOptionsQuery, $user);

        $incidentsQuery = RiskIncident::query()
            ->with([
                'risk:id,risk_id,risk_title,risk_owner,status,year',
                'reporter:id,name,username',
                'updater:id,name,username',
            ]);
        $this->applyIncidentScopeForUser($incidentsQuery, $user);

        if ($request->filled('affected_client')) {
            $incidentsQuery->where('affected_client', $request->input('affected_client'));
        }

        $riskOptions = $riskOptionsQuery
            ->orderByDesc('year')
            ->orderBy('risk_id')
            ->get();

        $incidents = $incidentsQuery
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->get();

        $summary = $this->buildSummary($incidents);
        $monthlyTrend = $this->buildMonthlyIncidentTrend($incidents, 6);
        $monthlyLossTrend = $this->buildMonthlyLossTrend($incidents, 6);
        $verdictDistribution = $this->buildVerdictDistribution($incidents);
        $lossRecapByYear = $this->buildLossRecapByYear($incidents);
        $monthlyLossRecapForYear = $this->buildMonthlyLossRecapForYear($incidents, $selectedYear);

        $availableYears = collect($lossRecapByYear)->pluck('year')->unique()->sortDesc()->values()->all();
        if (!in_array(now()->year, $availableYears, true)) {
            array_unshift($availableYears, now()->year);
        }

        $riskOwnerOptions = $this->resolveRiskOwnerOptions($user);
        $clientOptions = RiskIncident::affectedPartyOptions();
        $severityOptions = RiskIncident::SEVERITY_LEVELS;
        $statusOptions = RiskIncident::STATUS_OPTIONS;
        $mitigationVerdictOptions = RiskIncident::MITIGATION_VERDICTS;
        $canWrite = !$user->isSuperadmin();

        return view('incidents.index', compact(
            'incidents',
            'riskOptions',
            'riskOwnerOptions',
            'clientOptions',
            'selectedClient',
            'summary',
            'monthlyTrend',
            'monthlyLossTrend',
            'verdictDistribution',
            'lossRecapByYear',
            'monthlyLossRecapForYear',
            'selectedYear',
            'availableYears',
            'severityOptions',
            'statusOptions',
            'mitigationVerdictOptions',
            'canWrite'
        ));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');
        $this->ensureEditable($user);

        $riskOptionsQuery = Risk::query()
            ->select('id', 'risk_id', 'risk_title', 'risk_owner', 'status', 'year');
        $this->applyRiskScopeForUser($riskOptionsQuery, $user);

        $riskOptions = $riskOptionsQuery
            ->orderByDesc('year')
            ->orderBy('risk_id')
            ->get();

        $riskOwnerOptions = $this->resolveRiskOwnerOptions($user);
        $clientOptions = RiskIncident::affectedPartyOptions();
        $severityOptions = RiskIncident::SEVERITY_LEVELS;
        $statusOptions = RiskIncident::STATUS_OPTIONS;
        $mitigationVerdictOptions = RiskIncident::MITIGATION_VERDICTS;
        $selectedRiskId = $request->input('risk_id');

        return view('incidents.create', compact(
            'riskOptions',
            'riskOwnerOptions',
            'clientOptions',
            'severityOptions',
            'statusOptions',
            'mitigationVerdictOptions',
            'selectedRiskId'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');
        $this->ensureEditable($user);

        $validated = $this->validatePayload($request);
        $risk = null;
        if (!empty($validated['risk_id'])) {
            $risk = $this->findAccessibleRisk((int) $validated['risk_id'], $user, true);
        }

        $payload = $this->normalizedPayload($validated);
        $payload['risk_id'] = $risk ? $risk->id : null;
        $payload['risk_owner'] = $risk ? $risk->risk_owner : ($validated['risk_owner'] ?? $this->resolveDefaultRiskOwner($user));
        $payload['is_new_finding'] = is_null($payload['risk_id']);
        $payload['reported_by'] = $user->id;
        $payload['last_updated_by'] = $user->id;

        $incident = RiskIncident::createWithGeneratedIncidentCode($payload);

        $message = $risk
            ? 'Incident ' . $incident->incident_code . ' berhasil dicatat dan dikaitkan ke risk ' . $risk->risk_id . '.'
            : 'Incident ' . $incident->incident_code . ' berhasil dicatat sebagai temuan baru (belum terdaftar di risk register).';

        return redirect()
            ->route('incidents.index')
            ->with('success', $message);
    }

    public function edit(RiskIncident $incident)
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');
        $this->ensureEditable($user);

        $incident->loadMissing([
            'risk:id,risk_id,risk_title,risk_owner,status,year',
            'reporter:id,name,username',
            'updater:id,name,username',
        ]);
        $this->ensureCanWriteIncident($incident, $user);

        $riskOptionsQuery = Risk::query()
            ->select('id', 'risk_id', 'risk_title', 'risk_owner', 'status', 'year');
        $this->applyRiskScopeForUser($riskOptionsQuery, $user);

        $riskOptions = $riskOptionsQuery
            ->orderByDesc('year')
            ->orderBy('risk_id')
            ->get();

        $riskOwnerOptions = $this->resolveRiskOwnerOptions($user);
        $clientOptions = RiskIncident::affectedPartyOptions();
        $severityOptions = RiskIncident::SEVERITY_LEVELS;
        $statusOptions = RiskIncident::STATUS_OPTIONS;
        $mitigationVerdictOptions = RiskIncident::MITIGATION_VERDICTS;

        return view('incidents.edit', compact(
            'incident',
            'riskOptions',
            'riskOwnerOptions',
            'clientOptions',
            'severityOptions',
            'statusOptions',
            'mitigationVerdictOptions'
        ));
    }

    public function update(Request $request, RiskIncident $incident)
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');
        $this->ensureEditable($user);

        $incident->loadMissing('risk:id,risk_id,risk_owner');
        $this->ensureCanWriteIncident($incident, $user);

        $validated = $this->validatePayload($request);
        $risk = null;
        if (!empty($validated['risk_id'])) {
            $risk = $this->findAccessibleRisk((int) $validated['risk_id'], $user, true);
        }

        $payload = $this->normalizedPayload($validated);
        $payload['risk_id'] = $risk ? $risk->id : null;
        $payload['risk_owner'] = $risk ? $risk->risk_owner : ($validated['risk_owner'] ?? $incident->risk_owner ?? $this->resolveDefaultRiskOwner($user));
        $payload['is_new_finding'] = is_null($payload['risk_id']);
        $payload['last_updated_by'] = $user->id;

        $incident->fill($payload);
        $incident->save();

        return redirect()
            ->route('incidents.index')
            ->with('success', 'Incident ' . $incident->incident_code . ' berhasil diperbarui.');
    }

    public function destroy(RiskIncident $incident)
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');
        $this->ensureEditable($user);

        $incident->loadMissing('risk:id,risk_id,risk_owner');
        $this->ensureCanWriteIncident($incident, $user);

        $incidentCode = (string) $incident->incident_code;
        $incident->delete();

        return redirect()
            ->route('incidents.index')
            ->with('success', 'Incident ' . $incidentCode . ' berhasil dihapus.');
    }

    private function validatePayload(Request $request): array
    {
        if ($request->has('loss_amount')) {
            $rawLoss = $request->input('loss_amount');
            $normalizedLoss = InputValidation::normalizeGroupedInteger($rawLoss);
            if (is_null($normalizedLoss)) {
                $normalizedLoss = InputValidation::normalizeLocalizedDecimal($rawLoss);
            }
            $request->merge(['loss_amount' => $normalizedLoss]);
        }

        return $request->validate([
            'risk_id' => 'nullable|integer|exists:risks,id',
            'risk_owner' => 'nullable|string|max:100',
            'affected_client' => ['nullable', 'string', 'max:150', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'loss_amount' => ['nullable', 'numeric', 'min:0'],
            'title' => ['required', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'incident_description' => ['required', 'string', 'max:10000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'occurred_at' => 'required|date',
            'detected_at' => 'nullable|date|after_or_equal:occurred_at',
            'severity' => 'required|in:' . implode(',', RiskIncident::SEVERITY_LEVELS),
            'status' => 'required|in:' . implode(',', RiskIncident::STATUS_OPTIONS),
            'mitigation_verdict' => 'required|in:' . implode(',', RiskIncident::MITIGATION_VERDICTS),
            'business_impact' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'immediate_action' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'root_cause' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'corrective_action' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'resolution_notes' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'closed_at' => 'nullable|date|after_or_equal:occurred_at',
        ], [
            'title.regex' => InputValidation::safeTextMessage(),
            'incident_description.regex' => InputValidation::safeTextMessage(),
            'business_impact.regex' => InputValidation::safeTextMessage(),
            'immediate_action.regex' => InputValidation::safeTextMessage(),
            'root_cause.regex' => InputValidation::safeTextMessage(),
            'corrective_action.regex' => InputValidation::safeTextMessage(),
            'resolution_notes.regex' => InputValidation::safeTextMessage(),
            'affected_client.regex' => InputValidation::safeTextMessage(),
            'loss_amount.numeric' => 'Nominal kerugian harus berupa angka yang valid.',
            'loss_amount.min' => 'Nominal kerugian tidak boleh negatif.',
        ]);
    }

    private function normalizedPayload(array $validated): array
    {
        $status = trim((string) ($validated['status'] ?? 'Open'));
        $occurredAt = Carbon::parse($validated['occurred_at'])->toDateTimeString();
        $detectedAt = array_key_exists('detected_at', $validated) && !is_null($validated['detected_at'])
            ? Carbon::parse($validated['detected_at'])->toDateTimeString()
            : null;

        $closedAt = null;
        if ($status === 'Closed') {
            $closedAt = !empty($validated['closed_at'])
                ? Carbon::parse($validated['closed_at'])->toDateTimeString()
                : now()->toDateTimeString();
        }

        $lossAmount = 0.0;
        if (array_key_exists('loss_amount', $validated) && !is_null($validated['loss_amount']) && $validated['loss_amount'] !== '') {
            $lossAmount = max(0.0, (float) $validated['loss_amount']);
        }

        return [
            'title' => trim((string) $validated['title']),
            'incident_description' => trim((string) $validated['incident_description']),
            'occurred_at' => $occurredAt,
            'detected_at' => $detectedAt,
            'severity' => trim((string) $validated['severity']),
            'status' => $status,
            'mitigation_verdict' => trim((string) $validated['mitigation_verdict']),
            'affected_client' => $this->nullableTrim($validated['affected_client'] ?? null),
            'loss_amount' => $lossAmount,
            'business_impact' => $this->nullableTrim($validated['business_impact'] ?? null),
            'immediate_action' => $this->nullableTrim($validated['immediate_action'] ?? null),
            'root_cause' => $this->nullableTrim($validated['root_cause'] ?? null),
            'corrective_action' => $this->nullableTrim($validated['corrective_action'] ?? null),
            'resolution_notes' => $this->nullableTrim($validated['resolution_notes'] ?? null),
            'closed_at' => $closedAt,
        ];
    }

    private function nullableTrim($value): ?string
    {
        if (is_null($value)) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function ensureEditable(User $user): void
    {
        abort_if($user->isSuperadmin(), 403, 'Superadmin is read-only for incident dashboard.');
    }

    private function ensureCanWriteIncident(RiskIncident $incident, User $user): void
    {
        if ($user->isSuperadmin()) {
            abort(403, 'Superadmin is read-only for incident dashboard.');
        }

        if ($user->isComplianceDivision()) {
            return;
        }

        if (!is_null($incident->risk)) {
            abort_unless($this->canWriteRisk($user, $incident->risk), 403, 'You are not allowed to modify this incident.');

            return;
        }

        $effectiveOwner = $incident->risk_owner ?: null;
        $canManageOwner = !empty($effectiveOwner) && $user->canManageRiskOwner($effectiveOwner);
        $isReporter = (int) $incident->reported_by === (int) $user->id;

        abort_unless($canManageOwner || $isReporter, 403, 'You are not allowed to modify this incident.');
    }

    private function canWriteRisk(User $user, Risk $risk): bool
    {
        if ($user->isSuperadmin()) {
            return false;
        }

        if ($user->isComplianceDivision()) {
            return true;
        }

        return $user->canManageRiskOwner($risk->risk_owner);
    }

    private function findAccessibleRisk(int $riskId, User $user, bool $forWrite): Risk
    {
        $query = Risk::query()
            ->select('id', 'risk_id', 'risk_title', 'risk_owner', 'status', 'year')
            ->whereKey($riskId);

        $this->applyRiskScopeForUser($query, $user);

        $risk = $query->firstOrFail();

        if ($forWrite && !$this->canWriteRisk($user, $risk)) {
            abort(403, 'You are not allowed to create incident for this risk.');
        }

        return $risk;
    }

    private function applyRiskScopeForUser(Builder $query, User $user): void
    {
        if ($user->isSuperadmin() || $user->isComplianceDivision()) {
            return;
        }

        $allowedOwners = $user->allowedRiskOwners();
        if (empty($allowedOwners)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->whereIn('risk_owner', $allowedOwners);
    }

    private function applyIncidentScopeForUser(Builder $query, User $user): void
    {
        if ($user->isSuperadmin() || $user->isComplianceDivision()) {
            return;
        }

        $allowedOwners = $user->allowedRiskOwners();
        if (empty($allowedOwners)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function (Builder $sub) use ($allowedOwners, $user) {
            $sub->whereHas('risk', function (Builder $riskQuery) use ($allowedOwners) {
                $riskQuery->whereIn('risk_owner', $allowedOwners);
            })->orWhere(function (Builder $noRiskQuery) use ($allowedOwners, $user) {
                $noRiskQuery->whereNull('risk_id')
                    ->where(function (Builder $inner) use ($allowedOwners, $user) {
                        $inner->whereIn('risk_owner', $allowedOwners)
                            ->orWhere('reported_by', $user->id);
                    });
            });
        });
    }

    private function resolveDefaultRiskOwner(User $user): ?string
    {
        $allowed = $user->allowedRiskOwners();
        if (!empty($allowed)) {
            return $allowed[0];
        }

        return $user->division ?: null;
    }

    private function resolveRiskOwnerOptions(User $user): array
    {
        if ($user->isSuperadmin() || $user->isComplianceDivision()) {
            return RiskOwnerAccess::options();
        }

        $owners = $user->allowedRiskOwners();
        if (empty($owners) && !empty($user->division)) {
            $owners = [$user->division];
        }

        return $owners;
    }

    private function buildSummary(Collection $incidents): array
    {
        $currentYear = (int) now()->year;
        $currentMonthKey = now()->format('Y-m');

        $totalLossAllTime = (float) $incidents->sum('loss_amount');
        $incidentsWithLoss = $incidents->filter(fn ($inc) => (float) ($inc->loss_amount ?? 0) > 0);

        $thisYearIncidents = $incidents->filter(function ($inc) use ($currentYear) {
            return $inc->occurred_at instanceof Carbon && (int) $inc->occurred_at->year === $currentYear;
        });

        $thisMonthIncidents = $incidents->filter(function ($inc) use ($currentMonthKey) {
            return $inc->occurred_at instanceof Carbon && $inc->occurred_at->format('Y-m') === $currentMonthKey;
        });

        $unlinkedCount = (int) $incidents->filter(fn ($inc) => is_null($inc->risk_id) || $inc->is_new_finding)->count();

        return [
            'total' => (int) $incidents->count(),
            'active' => (int) $incidents->whereIn('status', ['Open', 'Investigating', 'Mitigated'])->count(),
            'closed' => (int) $incidents->where('status', 'Closed')->count(),
            'failed' => (int) $incidents->where('mitigation_verdict', 'Failed')->count(),
            'affected_risks' => (int) $incidents->whereNotNull('risk_id')->pluck('risk_id')->unique()->count(),
            'unlinked_findings' => $unlinkedCount,
            'total_loss_all_time' => $totalLossAllTime,
            'total_loss_this_year' => (float) $thisYearIncidents->sum('loss_amount'),
            'total_loss_this_month' => (float) $thisMonthIncidents->sum('loss_amount'),
            'incidents_with_loss_count' => (int) $incidentsWithLoss->count(),
            'avg_loss_per_incident' => $incidentsWithLoss->isNotEmpty()
                ? $totalLossAllTime / $incidentsWithLoss->count()
                : 0.0,
        ];
    }

    private function buildMonthlyIncidentTrend(Collection $incidents, int $months): array
    {
        $months = max(1, min(24, $months));
        $start = now()->startOfMonth()->subMonths($months - 1);
        $labels = [];
        $countsByKey = [];

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');
            $labels[$key] = $month->format('M Y');
            $countsByKey[$key] = 0;
        }

        foreach ($incidents as $incident) {
            if (!($incident->occurred_at instanceof Carbon)) {
                continue;
            }

            $key = $incident->occurred_at->format('Y-m');
            if (!array_key_exists($key, $countsByKey)) {
                continue;
            }

            $countsByKey[$key]++;
        }

        return [
            'labels' => array_values($labels),
            'values' => array_values($countsByKey),
        ];
    }

    private function buildMonthlyLossTrend(Collection $incidents, int $months): array
    {
        $months = max(1, min(24, $months));
        $start = now()->startOfMonth()->subMonths($months - 1);
        $labels = [];
        $lossByKey = [];

        for ($i = 0; $i < $months; $i++) {
            $month = $start->copy()->addMonths($i);
            $key = $month->format('Y-m');
            $labels[$key] = $month->format('M Y');
            $lossByKey[$key] = 0.0;
        }

        foreach ($incidents as $incident) {
            if (!($incident->occurred_at instanceof Carbon)) {
                continue;
            }

            $key = $incident->occurred_at->format('Y-m');
            if (array_key_exists($key, $lossByKey)) {
                $lossByKey[$key] += (float) ($incident->loss_amount ?? 0);
            }
        }

        return [
            'labels' => array_values($labels),
            'values' => array_values($lossByKey),
        ];
    }

    private function buildVerdictDistribution(Collection $incidents): array
    {
        $labels = [];
        $values = [];

        foreach (RiskIncident::MITIGATION_VERDICTS as $verdict) {
            $labels[] = $verdict;
            $values[] = (int) $incidents->where('mitigation_verdict', $verdict)->count();
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    private function buildLossRecapByYear(Collection $incidents): array
    {
        $years = [];

        foreach ($incidents as $incident) {
            if (!($incident->occurred_at instanceof Carbon)) {
                continue;
            }

            $year = (int) $incident->occurred_at->year;
            if (!isset($years[$year])) {
                $years[$year] = [
                    'year' => $year,
                    'total_incidents' => 0,
                    'loss_incidents' => 0,
                    'total_loss' => 0.0,
                ];
            }

            $years[$year]['total_incidents']++;
            $loss = (float) ($incident->loss_amount ?? 0);
            if ($loss > 0) {
                $years[$year]['loss_incidents']++;
                $years[$year]['total_loss'] += $loss;
            }
        }

        $currYear = (int) now()->year;
        if (!isset($years[$currYear])) {
            $years[$currYear] = [
                'year' => $currYear,
                'total_incidents' => 0,
                'loss_incidents' => 0,
                'total_loss' => 0.0,
            ];
        }

        krsort($years);

        foreach ($years as &$item) {
            $item['avg_loss'] = $item['loss_incidents'] > 0
                ? $item['total_loss'] / $item['loss_incidents']
                : 0.0;
        }

        return array_values($years);
    }

    private function buildMonthlyLossRecapForYear(Collection $incidents, int $targetYear): array
    {
        $months = [];
        $indonesianMonths = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = [
                'month' => $m,
                'month_name' => $indonesianMonths[$m],
                'incident_count' => 0,
                'loss_incidents' => 0,
                'total_loss' => 0.0,
                'avg_loss' => 0.0,
            ];
        }

        foreach ($incidents as $incident) {
            if (!($incident->occurred_at instanceof Carbon)) {
                continue;
            }

            if ((int) $incident->occurred_at->year !== $targetYear) {
                continue;
            }

            $m = (int) $incident->occurred_at->month;
            if (isset($months[$m])) {
                $months[$m]['incident_count']++;
                $loss = (float) ($incident->loss_amount ?? 0);
                if ($loss > 0) {
                    $months[$m]['loss_incidents']++;
                    $months[$m]['total_loss'] += $loss;
                }
            }
        }

        foreach ($months as &$item) {
            $item['avg_loss'] = $item['loss_incidents'] > 0
                ? $item['total_loss'] / $item['loss_incidents']
                : 0.0;
        }

        return array_values($months);
    }
}
