<?php

namespace App\Http\Controllers;

use App\RiskIncident;
use App\RiskIncidentAction;
use App\Support\AuditLogger;
use App\Support\InputValidation;
use App\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class IncidentLearningController extends Controller
{
    public function show(int $incidentId): View
    {
        $incident = $this->loadIncident($incidentId);
        $user = auth()->user();

        return view('incidents.learning', [
            'incident' => $incident,
            'canManage' => $this->canManage($incident, $user),
        ]);
    }

    public function storeAction(Request $request, int $incidentId): RedirectResponse
    {
        $incident = $this->loadIncident($incidentId);
        $this->ensureCanManage($incident);

        $validated = $request->validate([
            'action_type' => ['required', 'in:' . implode(',', RiskIncidentAction::ACTION_TYPES)],
            'title' => ['required', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'description' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'owner_name' => ['required', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'due_at' => ['nullable', 'date'],
        ], $this->messages(['title', 'description', 'owner_name']));

        $action = $incident->actions()->create(array_merge($validated, ['status' => 'open']));

        AuditLogger::logModelEvent('incident.action.created', $incident, null, [
            'action_id' => $action->id,
            'action_type' => $action->action_type,
            'title' => $action->title,
            'due_at' => optional($action->due_at)->format('Y-m-d'),
        ]);

        return $this->redirect($incident)->with('success', 'CAPA action registered.');
    }

    public function updateAction(Request $request, int $incidentId, int $actionId): RedirectResponse
    {
        $incident = $this->loadIncident($incidentId);
        $this->ensureCanManage($incident);
        $action = $incident->actions()->whereKey($actionId)->firstOrFail();

        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', RiskIncidentAction::STATUS_OPTIONS)],
            'completion_notes' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
        ], $this->messages(['completion_notes']));

        $status = $validated['status'];
        $payload = [
            'status' => $status,
            'completion_notes' => $validated['completion_notes'] ?? null,
        ];

        if (in_array($status, ['completed', 'verified'], true)) {
            $payload['completed_by'] = auth()->id();
            $payload['completed_at'] = now();
        }

        if ($status === 'verified') {
            abort_unless(auth()->user()->isComplianceDivision(), 403, 'Only compliance may verify CAPA actions.');
            $payload['verified_by'] = auth()->id();
            $payload['verified_at'] = now();
        }

        $action->update($payload);

        AuditLogger::logModelEvent('incident.action.' . $status, $incident, null, [
            'action_id' => $action->id,
            'title' => $action->title,
        ]);

        return $this->redirect($incident)->with('success', 'CAPA action status updated.');
    }

    public function completeReview(Request $request, int $incidentId): RedirectResponse
    {
        $incident = $this->loadIncident($incidentId);
        $this->ensureCanManage($incident);

        $validated = $request->validate([
            'lessons_learned' => ['required', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'risk_review_status' => ['required', 'in:reviewed,not_required'],
            'control_review_status' => ['required', 'in:reviewed,not_required'],
            'kri_review_status' => ['required', 'in:reviewed,not_required'],
        ], $this->messages(['lessons_learned']));

        $hasOpenActions = $incident->actions()->whereNotIn('status', ['completed', 'verified'])->exists();
        if ($hasOpenActions) {
            return $this->redirect($incident)->withErrors([
                'review' => 'Complete all CAPA actions before closing the post-incident learning review.',
            ]);
        }

        $incident->update(array_merge($validated, [
            'post_incident_reviewed_by' => auth()->id(),
            'post_incident_reviewed_at' => now(),
            'last_updated_by' => auth()->id(),
        ]));

        AuditLogger::logModelEvent('incident.learning.reviewed', $incident, null, [
            'risk_review_status' => $incident->risk_review_status,
            'control_review_status' => $incident->control_review_status,
            'kri_review_status' => $incident->kri_review_status,
        ]);

        return $this->redirect($incident)->with('success', 'Post-incident learning review completed.');
    }

    private function loadIncident(int $incidentId): RiskIncident
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');

        $query = RiskIncident::query()->with([
            'risk:id,risk_id,risk_title,risk_owner',
            'actions.completer:id,name',
            'actions.verifier:id,name',
            'postIncidentReviewer:id,name',
        ]);

        if (!$user->isSuperadmin() && !$user->isComplianceDivision()) {
            $allowedOwners = $user->allowedRiskOwners();
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

        return $query->findOrFail($incidentId);
    }

    private function canManage(RiskIncident $incident, ?User $user): bool
    {
        if (is_null($user) || $user->isSuperadmin()) {
            return false;
        }

        if ($user->isComplianceDivision()) {
            return true;
        }

        if (!is_null($incident->risk)) {
            return $user->canManageRiskOwner($incident->risk->risk_owner);
        }

        $effectiveOwner = $incident->risk_owner ?: null;

        return (!empty($effectiveOwner) && $user->canManageRiskOwner($effectiveOwner))
            || ((int) $incident->reported_by === (int) $user->id);
    }

    private function ensureCanManage(RiskIncident $incident): void
    {
        abort_unless($this->canManage($incident, auth()->user()), 403, 'You are not allowed to manage post-incident learning for this incident.');
    }

    private function redirect(RiskIncident $incident): RedirectResponse
    {
        return redirect()->route('incidents.learning.show', $incident->id);
    }

    private function messages(array $fields): array
    {
        $messages = [];
        foreach ($fields as $field) {
            $messages[$field . '.regex'] = InputValidation::safeTextMessage();
        }

        return $messages;
    }
}
