<?php

namespace App\Http\Controllers;

use App\DataPurgeRequest;
use App\DataRetentionSchedule;
use App\Support\AuditLogger;
use App\Support\InputValidation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DataRetentionController extends Controller
{
    public function index(): View
    {
        $schedules = DataRetentionSchedule::query()->withCount('purgeRequests')->orderBy('record_type')->get();
        $purgeRequests = DataPurgeRequest::query()
            ->with(['schedule:id,record_type,retention_months', 'requester:id,name', 'reviewer:id,name'])
            ->latest('requested_at')
            ->paginate(25);

        $summary = [
            'active_schedules' => $schedules->where('status', 'active')->count(),
            'review_due' => $schedules->filter(fn ($schedule) => !is_null($schedule->next_review_at) && $schedule->next_review_at->isPast())->count(),
            'pending_approval' => DataPurgeRequest::query()->where('status', 'requested')->count(),
            'executed' => DataPurgeRequest::query()->where('status', 'executed')->count(),
        ];

        return view('data-retention.index', [
            'schedules' => $schedules,
            'purgeRequests' => $purgeRequests,
            'summary' => $summary,
            'canManage' => optional(auth()->user())->isComplianceDivision(),
            'canApprove' => optional(auth()->user())->isSuperadmin(),
            'scheduleStatuses' => DataRetentionSchedule::STATUSES,
            'purgeStatuses' => DataPurgeRequest::STATUSES,
        ]);
    }

    public function storeSchedule(Request $request): RedirectResponse
    {
        $this->ensureCompliance();
        $validated = $request->validate([
            'record_type' => ['required', 'string', 'max:120', 'unique:data_retention_schedules,record_type', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'description' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'retention_months' => ['required', 'integer', 'min:1', 'max:1200'],
            'owner_name' => ['nullable', 'string', 'max:255', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'status' => ['required', 'in:' . implode(',', DataRetentionSchedule::STATUSES)],
            'next_review_at' => ['nullable', 'date'],
            'legal_basis' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
        ], $this->safeTextMessages());
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();
        $schedule = DataRetentionSchedule::create($validated);
        AuditLogger::logModelEvent('retention.schedule.created', $schedule, null, $schedule->toArray());

        return redirect()->route('data-retention.index')->with('success', 'Retention schedule saved.');
    }

    public function storePurgeRequest(Request $request): RedirectResponse
    {
        $this->ensureCompliance();
        $validated = $request->validate([
            'data_retention_schedule_id' => ['required', 'integer', 'exists:data_retention_schedules,id'],
            'scope_reference' => ['required', 'string', 'max:500', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'estimated_record_count' => ['nullable', 'integer', 'min:0'],
            'proposed_purge_at' => ['nullable', 'date'],
            'legal_hold_checked' => ['required', 'accepted'],
            'justification' => ['required', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
        ], $this->safeTextMessages());
        $validated['requested_at'] = now();
        $validated['requested_by'] = auth()->id();
        $validated['status'] = 'requested';
        $requestModel = DataPurgeRequest::create($validated);
        AuditLogger::logModelEvent('retention.purge.requested', $requestModel, null, [
            'schedule_id' => $requestModel->data_retention_schedule_id,
            'scope_reference' => $requestModel->scope_reference,
            'legal_hold_checked' => true,
        ]);

        return redirect()->route('data-retention.index')->with('success', 'Purge request submitted for independent approval.');
    }

    public function reviewPurgeRequest(Request $request, DataPurgeRequest $purgeRequest): RedirectResponse
    {
        $this->ensureSuperadmin();
        abort_unless($purgeRequest->status === 'requested', 422, 'Only requested purge records can be reviewed.');
        abort_if((int) $purgeRequest->requested_by === (int) auth()->id(), 422, 'Requester cannot approve their own purge request.');

        $validated = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'review_notes' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
        ], $this->safeTextMessages());
        $purgeRequest->update([
            'status' => $validated['decision'],
            'review_notes' => $validated['review_notes'] ?? null,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);
        AuditLogger::logModelEvent('retention.purge.reviewed', $purgeRequest, null, [
            'decision' => $purgeRequest->status,
            'reviewed_by' => auth()->id(),
        ]);

        return redirect()->route('data-retention.index')->with('success', 'Purge request reviewed.');
    }

    public function executePurgeRequest(Request $request, DataPurgeRequest $purgeRequest): RedirectResponse
    {
        $this->ensureSuperadmin();
        abort_unless($purgeRequest->status === 'approved', 422, 'Only approved purge records can be marked executed.');

        $validated = $request->validate([
            'evidence_reference' => ['required', 'string', 'max:500', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
        ], $this->safeTextMessages());
        $purgeRequest->update([
            'status' => 'executed',
            'executed_by' => auth()->id(),
            'executed_at' => now(),
            'evidence_reference' => $validated['evidence_reference'],
        ]);
        AuditLogger::logModelEvent('retention.purge.executed', $purgeRequest, null, [
            'executed_by' => auth()->id(),
            'evidence_reference' => $purgeRequest->evidence_reference,
        ]);

        return redirect()->route('data-retention.index')->with('success', 'Purge execution evidence recorded. No application records were deleted by this workflow.');
    }

    private function ensureCompliance(): void
    {
        abort_unless(optional(auth()->user())->isComplianceDivision(), 403, 'Only compliance may manage retention controls.');
    }

    private function ensureSuperadmin(): void
    {
        abort_unless(optional(auth()->user())->isSuperadmin(), 403, 'Only superadmin may approve or close purge controls.');
    }

    private function safeTextMessages(): array
    {
        return [
            'record_type.regex' => InputValidation::safeTextMessage(),
            'description.regex' => InputValidation::safeTextMessage(),
            'owner_name.regex' => InputValidation::safeTextMessage(),
            'legal_basis.regex' => InputValidation::safeTextMessage(),
            'scope_reference.regex' => InputValidation::safeTextMessage(),
            'justification.regex' => InputValidation::safeTextMessage(),
            'review_notes.regex' => InputValidation::safeTextMessage(),
            'evidence_reference.regex' => InputValidation::safeTextMessage(),
        ];
    }
}
