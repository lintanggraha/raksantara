<?php

namespace App\Http\Controllers;

use App\EvidenceAttachment;
use App\Risk;
use App\RiskComment;
use App\RiskTreatment;
use App\Support\AuditLogger;
use App\Support\InputValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EvidenceAttachmentController extends Controller
{
    public function storeRiskAttachment(Request $request, Risk $risk)
    {
        $this->ensureCanManage($risk);

        $validated = $this->validateAttachmentRequest($request);

        $storedPath = $validated['file']->store('evidence/risk', 'local');

        $attachment = $risk->attachments()->create([
            'uploaded_by' => auth()->id(),
            'original_name' => $validated['file']->getClientOriginalName(),
            'storage_path' => $storedPath,
            'mime_type' => $validated['file']->getClientMimeType(),
            'size' => (int) $validated['file']->getSize(),
            'notes' => $validated['notes'] ?? null,
        ]);

        AuditLogger::logModelEvent('evidence.risk.uploaded', $attachment, null, $attachment->toArray());

        return redirect()->back()->with('success', 'Evidence file uploaded for risk.');
    }

    public function storeTreatmentAttachment(Request $request, Risk $risk, RiskTreatment $treatment)
    {
        abort_unless((int) $treatment->risk_id === (int) $risk->id, 404);
        $this->ensureCanManage($risk);

        $validated = $this->validateAttachmentRequest($request);

        $storedPath = $validated['file']->store('evidence/treatment', 'local');

        $attachment = $treatment->attachments()->create([
            'uploaded_by' => auth()->id(),
            'original_name' => $validated['file']->getClientOriginalName(),
            'storage_path' => $storedPath,
            'mime_type' => $validated['file']->getClientMimeType(),
            'size' => (int) $validated['file']->getSize(),
            'notes' => $validated['notes'] ?? null,
        ]);

        AuditLogger::logModelEvent('evidence.treatment.uploaded', $attachment, null, $attachment->toArray());

        return redirect()->back()->with('success', 'Evidence file uploaded for treatment.');
    }

    public function download(EvidenceAttachment $attachment)
    {
        $risk = $this->resolveRiskFromAttachment($attachment);
        abort_unless(!is_null($risk), 404);
        $this->ensureCanView($risk);

        abort_unless(Storage::disk('local')->exists($attachment->storage_path), 404, 'Attachment file not found.');

        return Storage::disk('local')->download($attachment->storage_path, $attachment->original_name);
    }

    public function destroy(EvidenceAttachment $attachment)
    {
        $risk = $this->resolveRiskFromAttachment($attachment);
        abort_unless(!is_null($risk), 404);
        $this->ensureCanManage($risk);

        $before = $attachment->toArray();

        if (Storage::disk('local')->exists($attachment->storage_path)) {
            Storage::disk('local')->delete($attachment->storage_path);
        }

        $attachment->delete();

        AuditLogger::logModelEvent('evidence.deleted', $attachment, $before, null);

        return redirect()->back()->with('success', 'Evidence file removed.');
    }

    private function validateAttachmentRequest(Request $request): array
    {
        return $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,ppt,pptx,txt,csv',
            'notes' => ['nullable', 'string', 'max:1000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
        ], [
            'notes.regex' => InputValidation::safeTextMessage(),
        ]);
    }

    private function resolveRiskFromAttachment(EvidenceAttachment $attachment): ?Risk
    {
        $attachable = $attachment->attachable;

        if ($attachable instanceof Risk) {
            return $attachable;
        }

        if ($attachable instanceof RiskTreatment) {
            return $attachable->risk;
        }

        if ($attachable instanceof RiskComment) {
            return $attachable->risk;
        }

        return null;
    }

    private function ensureCanManage(Risk $risk): void
    {
        $user = auth()->user();
        abort_unless(
            !is_null($user) && !$user->isSuperadmin() && $user->canManageRiskOwner($risk->risk_owner),
            403,
            'You are not allowed to modify this risk evidence.'
        );
    }

    private function ensureCanView(Risk $risk): void
    {
        $user = auth()->user();
        abort_unless(!is_null($user), 403);
    }
}
