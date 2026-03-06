<?php

namespace App\Http\Controllers;

use App\Risk;
use App\RiskComment;
use App\Support\AuditLogger;
use App\Support\InputValidation;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class RiskCommentController extends Controller
{
    public function store(Request $request, Risk $risk)
    {
        $this->ensureCanComment();
        $validated = $this->validateCommentRequest($request, true);

        DB::transaction(function () use ($validated, $risk, $request): void {
            $comment = RiskComment::create([
                'risk_id' => $risk->id,
                'user_id' => auth()->id(),
                'content' => $validated['content'],
            ]);

            $this->syncCommentMentions($comment, $validated['content']);
            $this->storeUploadedAttachments($comment, $request->file('attachments', []));

            AuditLogger::logModelEvent(
                'risk.comment.created',
                $comment,
                null,
                $this->commentAuditPayload($comment)
            );
        });

        return redirect()->back()->with('success', 'Comment posted.');
    }

    public function update(Request $request, Risk $risk, RiskComment $comment)
    {
        $this->ensureRiskCommentMatch($risk, $comment);
        $this->ensureCanModifyComment($comment);

        $validated = $this->validateCommentRequest($request, false);
        $before = $this->commentAuditPayload($comment);

        $comment->update([
            'content' => $validated['content'],
            'edited_at' => now(),
        ]);

        $this->syncCommentMentions($comment, $validated['content']);

        AuditLogger::logModelEvent(
            'risk.comment.updated',
            $comment,
            $before,
            $this->commentAuditPayload($comment)
        );

        return redirect()->back()->with('success', 'Comment updated.');
    }

    public function destroy(Risk $risk, RiskComment $comment)
    {
        $this->ensureRiskCommentMatch($risk, $comment);
        $this->ensureCanModifyComment($comment);

        $before = $this->commentAuditPayload($comment);
        $this->deleteAttachmentCollection($comment);
        $comment->delete();

        AuditLogger::logModelEvent(
            'risk.comment.deleted',
            $comment,
            $before,
            null
        );

        return redirect()->back()->with('success', 'Comment deleted.');
    }

    public function storeAttachment(Request $request, Risk $risk, RiskComment $comment)
    {
        $this->ensureRiskCommentMatch($risk, $comment);
        $this->ensureCanModifyComment($comment);

        $validated = $request->validate([
            'file' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,ppt,pptx,txt,csv',
            'notes' => ['nullable', 'string', 'max:1000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
        ], [
            'notes.regex' => InputValidation::safeTextMessage(),
        ]);

        $storedPath = $validated['file']->store('evidence/comment', 'local');
        $attachment = $comment->attachments()->create([
            'uploaded_by' => auth()->id(),
            'original_name' => $validated['file']->getClientOriginalName(),
            'storage_path' => $storedPath,
            'mime_type' => $validated['file']->getClientMimeType(),
            'size' => (int) $validated['file']->getSize(),
            'notes' => $validated['notes'] ?? null,
        ]);

        AuditLogger::logModelEvent('evidence.comment.uploaded', $attachment, null, $attachment->toArray());

        return redirect()->back()->with('success', 'Attachment shared in comment.');
    }

    private function validateCommentRequest(Request $request, bool $withAttachments): array
    {
        $rules = [
            'content' => ['required', 'string', 'max:5000', 'regex:' . InputValidation::SAFE_TEXT_REGEX],
        ];

        if ($withAttachments) {
            $rules['attachments'] = 'nullable|array|max:5';
            $rules['attachments.*'] = 'file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,ppt,pptx,txt,csv';
        }

        return $request->validate($rules, [
            'content.regex' => InputValidation::safeTextMessage(),
        ]);
    }

    private function storeUploadedAttachments(RiskComment $comment, array $files): void
    {
        foreach ($files as $file) {
            if (is_null($file)) {
                continue;
            }

            $storedPath = $file->store('evidence/comment', 'local');
            $attachment = $comment->attachments()->create([
                'uploaded_by' => auth()->id(),
                'original_name' => $file->getClientOriginalName(),
                'storage_path' => $storedPath,
                'mime_type' => $file->getClientMimeType(),
                'size' => (int) $file->getSize(),
                'notes' => null,
            ]);

            AuditLogger::logModelEvent('evidence.comment.uploaded', $attachment, null, $attachment->toArray());
        }
    }

    private function syncCommentMentions(RiskComment $comment, string $content): void
    {
        $mentionedUsernames = $this->extractMentionUsernames($content);
        if (empty($mentionedUsernames)) {
            $comment->mentions()->sync([]);

            return;
        }

        $mentionedUserIds = User::query()
            ->whereIn('username', $mentionedUsernames)
            ->pluck('id')
            ->all();

        $comment->mentions()->sync($mentionedUserIds);
    }

    private function extractMentionUsernames(string $content): array
    {
        preg_match_all('/(?<![A-Za-z0-9_.-])@([A-Za-z0-9_.-]{1,255})/', $content, $matches);

        return array_values(array_unique(array_filter($matches[1] ?? [])));
    }

    private function commentAuditPayload(RiskComment $comment): array
    {
        $comment->loadMissing(['mentions:id,username', 'attachments:id,original_name']);

        $payload = $comment->toArray();
        $payload['mentioned_usernames'] = $comment->mentions->pluck('username')->values()->all();
        $payload['attachment_names'] = $comment->attachments->pluck('original_name')->values()->all();

        return $payload;
    }

    private function deleteAttachmentCollection(RiskComment $comment): void
    {
        $comment->loadMissing('attachments');

        foreach ($comment->attachments as $attachment) {
            $before = $attachment->toArray();

            if (!empty($attachment->storage_path) && Storage::disk('local')->exists($attachment->storage_path)) {
                Storage::disk('local')->delete($attachment->storage_path);
            }

            $attachment->delete();
            AuditLogger::logModelEvent('evidence.deleted.by_comment_remove', $attachment, $before, null);
        }
    }

    private function ensureRiskCommentMatch(Risk $risk, RiskComment $comment): void
    {
        abort_unless((int) $comment->risk_id === (int) $risk->id, 404);
    }

    private function ensureCanComment(): void
    {
        $user = auth()->user();
        abort_unless(
            !is_null($user) && !$user->isSuperadmin(),
            403,
            'You are not allowed to comment on this risk.'
        );
    }

    private function ensureCanModifyComment(RiskComment $comment): void
    {
        $user = auth()->user();
        abort_unless(
            !is_null($user) && !$user->isSuperadmin() && (int) $comment->user_id === (int) $user->id,
            403,
            'You are not allowed to modify this comment.'
        );
    }
}
