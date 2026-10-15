<?php

namespace App\Http\Controllers;

use App\RiskStatusChangeRequest;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiskStatusApprovalController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:pending,approved,rejected,all',
        ]);

        $status = (string) $request->input('status', 'pending');

        $query = RiskStatusChangeRequest::with(['risk', 'requester', 'reviewer'])
            ->orderByDesc('created_at');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $requests = $query->paginate(20)->appends(['status' => $status]);

        return view('risk-status-approvals.index', compact('requests', 'status'));
    }

    public function approve(Request $request, RiskStatusChangeRequest $statusRequest)
    {
        $validated = $request->validate([
            'review_note' => 'nullable|string|max:2000',
        ]);

        abort_unless($statusRequest->status === 'pending', 422, 'Request already reviewed.');
        $this->ensureNotOwnRequest($statusRequest);

        DB::transaction(function () use ($statusRequest, $validated) {
            $risk = $statusRequest->risk()->lockForUpdate()->firstOrFail();
            $riskBefore = $risk->toArray();

            $statusRequest->update([
                'status' => 'approved',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'review_note' => $validated['review_note'] ?? null,
            ]);

            $risk->update([
                'status' => $statusRequest->to_status,
            ]);

            AuditLogger::logModelEvent(
                'risk.status_change.approved',
                $statusRequest,
                null,
                $statusRequest->toArray()
            );

            AuditLogger::logModelEvent(
                'risk.status.changed_by_approval',
                $risk,
                $riskBefore,
                $risk->fresh()->toArray()
            );
        });

        return redirect()->back()->with('success', 'Status request approved and risk status updated.');
    }

    public function reject(Request $request, RiskStatusChangeRequest $statusRequest)
    {
        $validated = $request->validate([
            'review_note' => 'required|string|max:2000',
        ]);

        abort_unless($statusRequest->status === 'pending', 422, 'Request already reviewed.');
        $this->ensureNotOwnRequest($statusRequest);

        $statusRequest->update([
            'status' => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_note' => $validated['review_note'],
        ]);

        AuditLogger::logModelEvent(
            'risk.status_change.rejected',
            $statusRequest,
            null,
            $statusRequest->toArray()
        );

        return redirect()->back()->with('success', 'Status request rejected.');
    }

    /**
     * Maker-checker: nobody reviews a status change they asked for themselves.
     */
    private function ensureNotOwnRequest(RiskStatusChangeRequest $statusRequest): void
    {
        abort_if(
            (int) $statusRequest->requested_by === (int) auth()->id(),
            403,
            'Permintaan perubahan status Anda sendiri harus ditinjau oleh orang lain.'
        );
    }
}

