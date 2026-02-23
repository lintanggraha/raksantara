<?php

namespace Tests\Feature;

use App\Risk;
use App\RiskCategory;
use App\RiskComment;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class RiskDiscussionWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_can_create_comment_with_mentions_and_attachments(): void
    {
        Storage::fake('local');

        $author = $this->createUser('CO - COMPLIANCE', false);
        $mentionedUser = $this->createUser('BT - DIGITAL BANKING', false);
        $risk = $this->createRiskRecord($author, 'CO - COMPLIANCE');

        $this->actingAs($author);

        $response = $this
            ->from(route('risks.show', $risk->id))
            ->post(route('risks.comments.store', $risk->id), [
                'content' => 'Please review this risk @' . $mentionedUser->username,
                'attachments' => [
                    UploadedFile::fake()->create('discussion-note.txt', 12, 'text/plain'),
                ],
            ]);

        $response->assertRedirect(route('risks.show', $risk->id));
        $response->assertSessionHas('success');

        $comment = RiskComment::query()->where('risk_id', $risk->id)->first();
        $this->assertNotNull($comment);
        $this->assertDatabaseHas('risk_comment_mentions', [
            'risk_comment_id' => $comment->id,
            'mentioned_user_id' => $mentionedUser->id,
        ]);

        $attachment = DB::table('evidence_attachments')
            ->where('attachable_type', RiskComment::class)
            ->where('attachable_id', $comment->id)
            ->first();

        $this->assertNotNull($attachment);
        Storage::disk('local')->assertExists($attachment->storage_path);

        $this->get(route('risks.show', $risk->id))
            ->assertOk()
            ->assertSee('Discussion Thread')
            ->assertSee('@' . $mentionedUser->username);
    }

    public function test_superadmin_cannot_post_comments(): void
    {
        $superadmin = $this->createUser('CO - COMPLIANCE', true);
        $owner = $this->createUser('CO - COMPLIANCE', false);
        $risk = $this->createRiskRecord($owner, 'CO - COMPLIANCE');

        $this->actingAs($superadmin);

        $this->post(route('risks.comments.store', $risk->id), [
            'content' => 'Superadmin discussion message',
        ])->assertForbidden();
    }

    private function createRiskRecord(User $creator, string $riskOwner): Risk
    {
        return Risk::create([
            'risk_id' => 'TEST-' . Str::upper(Str::random(8)),
            'year' => (int) now()->year,
            'risk_title' => 'Discussion Risk ' . Str::random(6),
            'risk_category_id' => $this->ensureRiskCategoryId(),
            'risk_owner' => $riskOwner,
            'status' => 'Open',
            'created_by' => $creator->id,
        ]);
    }

    private function ensureRiskCategoryId(): int
    {
        $existingId = RiskCategory::query()->value('id');
        if (!is_null($existingId)) {
            return (int) $existingId;
        }

        return (int) RiskCategory::query()->insertGetId([
            'name' => 'Discussion Test Category ' . Str::random(5),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createUser(string $division, bool $isSuperadmin): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Discussion User ' . $suffix,
            'username' => 'discussion_' . $suffix,
            'division' => $division,
            'pending_division' => null,
            'division_verified_at' => now(),
            'is_superadmin' => $isSuperadmin,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }
}
