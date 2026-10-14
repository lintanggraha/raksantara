<?php

namespace App\Http\Controllers;

use App\Support\AuditLogger;
use App\Support\InputValidation;
use App\Vendor;
use App\VendorRiskAssessment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ThirdPartyRiskController extends Controller
{
    public function show(Vendor $vendor): View
    {
        $vendor->load([
            'assessments.assessor:id,name',
            'risks:id,risk_id,risk_title,risk_owner,vendor_id,status',
        ]);

        $summary = [
            'assessments' => $vendor->assessments->count(),
            'open_findings' => $vendor->assessments->whereIn('status', ['open', 'remediating'])->count(),
            'due_review' => !is_null($vendor->next_review_at) && $vendor->next_review_at->isPast(),
        ];

        return view('vendors.third-party-risk', [
            'vendor' => $vendor,
            'summary' => $summary,
            'canManage' => optional(auth()->user())->isComplianceDivision() ?? false,
            'assessmentTypes' => VendorRiskAssessment::TYPES,
            'decisions' => VendorRiskAssessment::DECISIONS,
            'statusOptions' => VendorRiskAssessment::STATUS_OPTIONS,
        ]);
    }

    public function storeAssessment(Request $request, Vendor $vendor): RedirectResponse
    {
        $this->ensureCanManage();
        $validated = $this->validateAssessment($request);

        $assessment = $vendor->assessments()->create(array_merge($validated, [
            'assessed_by' => auth()->id(),
        ]));

        $vendor->update([
            'risk_rating' => $this->ratingLabel($assessment->residual_rating),
            'last_assessed_at' => now(),
            'next_review_at' => $assessment->valid_until,
            'onboarding_status' => $assessment->decision === 'Approved' ? 'approved' : ($assessment->decision === 'Conditional' ? 'conditional' : 'rejected'),
        ]);

        AuditLogger::logModelEvent('vendor.assessment.created', $vendor, null, [
            'assessment_id' => $assessment->id,
            'assessment_type' => $assessment->assessment_type,
            'decision' => $assessment->decision,
            'residual_rating' => $assessment->residual_rating,
        ]);

        return $this->redirect($vendor)->with('success', 'Third-party risk assessment recorded.');
    }

    public function updateAssessment(Request $request, Vendor $vendor, VendorRiskAssessment $assessment): RedirectResponse
    {
        $this->ensureCanManage();
        abort_unless($assessment->vendor_id === $vendor->id, 404);
        $validated = $this->validateAssessment($request);
        $before = $assessment->toArray();

        $assessment->update($validated);
        $vendor->update([
            'risk_rating' => $this->ratingLabel($assessment->residual_rating),
            'last_assessed_at' => now(),
            'next_review_at' => $assessment->valid_until,
            'onboarding_status' => $assessment->decision === 'Approved' ? 'approved' : ($assessment->decision === 'Conditional' ? 'conditional' : 'rejected'),
        ]);

        AuditLogger::logModelEvent('vendor.assessment.updated', $vendor, $before, $assessment->fresh()->toArray());

        return $this->redirect($vendor)->with('success', 'Third-party risk assessment updated.');
    }

    private function validateAssessment(Request $request): array
    {
        return $request->validate([
            'assessment_type' => ['required', 'in:' . implode(',', VendorRiskAssessment::TYPES)],
            'assessed_at' => ['required', 'date'],
            'inherent_rating' => ['nullable', 'integer', 'between:1,5'],
            'residual_rating' => ['nullable', 'integer', 'between:1,5'],
            'decision' => ['required', 'in:' . implode(',', VendorRiskAssessment::DECISIONS)],
            'valid_until' => ['nullable', 'date', 'after_or_equal:assessed_at'],
            'findings' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'risk_treatment' => ['nullable', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
            'status' => ['required', 'in:' . implode(',', VendorRiskAssessment::STATUS_OPTIONS)],
        ], [
            'findings.regex' => InputValidation::safeTextMessage(),
            'risk_treatment.regex' => InputValidation::safeTextMessage(),
        ]);
    }

    private function ensureCanManage(): void
    {
        abort_unless(optional(auth()->user())->isComplianceDivision(), 403, 'Only compliance may manage third-party assessments.');
    }

    private function redirect(Vendor $vendor): RedirectResponse
    {
        return redirect()->route('vendors.third-party-risk.show', $vendor->id);
    }

    private function ratingLabel(?int $rating): ?string
    {
        if (is_null($rating)) {
            return null;
        }

        return $rating >= 4 ? 'High' : ($rating >= 3 ? 'Medium' : 'Low');
    }
}
