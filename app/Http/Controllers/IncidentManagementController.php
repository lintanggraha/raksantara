<?php

namespace App\Http\Controllers;

use App\Risk;
use App\RiskIncident;
use App\Support\InputValidation;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class IncidentManagementController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');

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
        $verdictDistribution = $this->buildVerdictDistribution($incidents);
        $severityOptions = RiskIncident::SEVERITY_LEVELS;
        $statusOptions = RiskIncident::STATUS_OPTIONS;
        $mitigationVerdictOptions = RiskIncident::MITIGATION_VERDICTS;
        $canWrite = !$user->isSuperadmin();

        return view('incidents.index', compact(
            'incidents',
            'riskOptions',
            'summary',
            'monthlyTrend',
            'verdictDistribution',
            'severityOptions',
            'statusOptions',
            'mitigationVerdictOptions',
            'canWrite'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');
        $this->ensureEditable($user);

        $validated = $this->validatePayload($request);
        $risk = $this->findAccessibleRisk((int) $validated['risk_id'], $user, true);

        $payload = $this->normalizedPayload($validated);
        $payload['risk_id'] = $risk->id;
        $payload['reported_by'] = $user->id;
        $payload['last_updated_by'] = $user->id;

        RiskIncident::createWithGeneratedIncidentCode($payload);

        return redirect()
            ->route('incidents.index')
            ->with('success', 'Incident berhasil dicatat dan dikaitkan ke risk ' . $risk->risk_id . '.');
    }

    public function update(Request $request, RiskIncident $incident)
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');
        $this->ensureEditable($user);

        $incident->loadMissing('risk:id,risk_id,risk_owner');
        $this->ensureCanWriteIncident($incident, $user);

        $validated = $this->validatePayload($request);
        $risk = $this->findAccessibleRisk((int) $validated['risk_id'], $user, true);

        $payload = $this->normalizedPayload($validated);
        $payload['risk_id'] = $risk->id;
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
        return $request->validate([
            'risk_id' => 'required|integer|exists:risks,id',
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

        return [
            'title' => trim((string) $validated['title']),
            'incident_description' => trim((string) $validated['incident_description']),
            'occurred_at' => $occurredAt,
            'detected_at' => $detectedAt,
            'severity' => trim((string) $validated['severity']),
            'status' => $status,
            'mitigation_verdict' => trim((string) $validated['mitigation_verdict']),
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
        abort_unless(
            !is_null($incident->risk) && $this->canWriteRisk($user, $incident->risk),
            403,
            'You are not allowed to modify this incident.'
        );
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

        $query->whereHas('risk', function (Builder $riskQuery) use ($allowedOwners) {
            $riskQuery->whereIn('risk_owner', $allowedOwners);
        });
    }

    private function buildSummary(Collection $incidents): array
    {
        return [
            'total' => (int) $incidents->count(),
            'active' => (int) $incidents->whereIn('status', ['Open', 'Investigating', 'Mitigated'])->count(),
            'closed' => (int) $incidents->where('status', 'Closed')->count(),
            'failed' => (int) $incidents->where('mitigation_verdict', 'Failed')->count(),
            'affected_risks' => (int) $incidents->pluck('risk_id')->unique()->count(),
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
}
