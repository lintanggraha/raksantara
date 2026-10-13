<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Support\RiskAccess;
use App\RiskTreatment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RiskTreatmentKanbanController extends Controller
{
    /**
     * Display the Kanban Board.
     */
    public function index()
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');

        $query = RiskTreatment::with(['risk', 'template'])->withCount('attachments');
        if ($user->hasRoleAssignments() || (!$user->isSuperadmin() && !$user->isComplianceDivision())) {
            $query->whereHas('risk', function (Builder $riskQuery) use ($user) {
                RiskAccess::applyVisibilityScope($riskQuery, $user);
            });
        }

        $treatments = $query
            ->orderByRaw("CASE status
                WHEN 'Blocked' THEN 1
                WHEN 'In Progress' THEN 2
                WHEN 'Not Started' THEN 3
                WHEN 'Completed' THEN 4
                ELSE 5
            END")
            ->orderBy('deadline')
            ->get()
            ->each(function (RiskTreatment $treatment) {
                $statusUpdatedAt = $treatment->status_updated_at ?? $treatment->created_at;
                $treatment->aging_days = is_null($statusUpdatedAt)
                    ? 0
                    : $statusUpdatedAt->copy()->startOfDay()->diffInDays(Carbon::today());
                $treatment->sla_state = $this->resolveSlaState($treatment);
            });

        // Group the treatments by status for the Kanban columns
        $kanbanData = [
            'Not Started' => $treatments->where('status', 'Not Started'),
            'In Progress' => $treatments->where('status', 'In Progress'),
            'Blocked' => $treatments->where('status', 'Blocked'),
            'Completed' => $treatments->where('status', 'Completed'),
        ];

        $canManage = !$user->isSuperadmin()
            && (!$user->hasRoleAssignments() || $user->hasPermission('risks.manage'));

        return view('treatments.kanban', compact('kanbanData', 'canManage'));
    }

    /**
     * Update the status of a specific treatment via AJAX drag-and-drop.
     */
    public function updateStatus(Request $request, RiskTreatment $treatment)
    {
        $user = auth()->user();
        abort_if(is_null($user), 403, 'Unauthorized.');

        $treatment->loadMissing('risk');
        abort_unless(
            !is_null($treatment->risk) && RiskAccess::canManage($user, $treatment->risk),
            403,
            'You are not allowed to update this treatment.'
        );

        $validated = $request->validate([
            'status' => 'required|string|in:Not Started,In Progress,Blocked,Completed',
            'blocked_reason' => 'nullable|string|max:1000',
            'next_action_at' => 'nullable|date',
        ]);

        if ($validated['status'] === 'Blocked' && trim((string) ($validated['blocked_reason'] ?? '')) === '') {
            throw ValidationException::withMessages([
                'blocked_reason' => 'Blocked reason is required when moving an action into Blocked.',
            ]);
        }

        if ($validated['status'] === 'Completed' && (int) $treatment->attachments()->count() === 0) {
            throw ValidationException::withMessages([
                'status' => 'Upload at least one evidence attachment before moving this action to Completed.',
            ]);
        }

        $payload = [
            'status' => $validated['status'],
        ];

        if ((string) $treatment->status !== (string) $validated['status']) {
            $payload['status_updated_at'] = now();
        }

        if ($request->has('next_action_at')) {
            $payload['next_action_at'] = $validated['next_action_at'] ?? null;
        }

        if ($validated['status'] === 'Blocked') {
            $payload['blocked_reason'] = trim((string) ($validated['blocked_reason'] ?? ''));
        } elseif ($request->has('blocked_reason')) {
            $payload['blocked_reason'] = null;
        }

        $treatment->update($payload);

        return response()->json([
            'success' => true,
            'message' => 'Treatment status updated to ' . $validated['status'],
            'treatment' => [
                'status' => $treatment->status,
                'blocked_reason' => $treatment->blocked_reason,
                'next_action_at' => optional($treatment->next_action_at)->toDateString(),
            ],
        ]);
    }

    private function resolveSlaState(RiskTreatment $treatment): string
    {
        if ((string) $treatment->status === 'Completed') {
            return 'Completed';
        }

        if (is_null($treatment->deadline)) {
            return 'No Deadline';
        }

        $today = Carbon::today();
        $deadline = $treatment->deadline->copy()->startOfDay();

        if ($deadline->lt($today)) {
            return 'Overdue';
        }

        $daysUntilDeadline = $today->diffInDays($deadline, false);

        if ($daysUntilDeadline <= 3) {
            return 'Due Soon';
        }

        return 'On Track';
    }
}
