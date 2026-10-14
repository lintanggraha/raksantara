<?php

namespace Tests\Feature;

use App\EvidenceAttachment;
use App\Risk;
use App\RiskCategory;
use App\RiskTreatment;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class RiskAccessSecurityRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_compliance_user_cannot_view_foreign_risk_pages_or_lists(): void
    {
        $viewer = $this->createUser('BT - DIGITAL BANKING', false);
        $ownRisk = $this->createRiskRecord($viewer, 'BT - DIGITAL BANKING', 'Own Visible Risk');
        $foreignOwner = $this->createUser('CO - COMPLIANCE', false);
        $foreignRisk = $this->createRiskRecord($foreignOwner, 'CO - COMPLIANCE', 'Foreign Hidden Risk');

        $this->actingAs($viewer);

        $this->get(route('risks.show', $foreignRisk->id))->assertNotFound();

        $this->get(route('risks.index'))
            ->assertOk()
            ->assertViewHas('risks', function ($risks) use ($ownRisk, $foreignRisk) {
                return $risks->contains('id', $ownRisk->id) && !$risks->contains('id', $foreignRisk->id);
            });
    }

    public function test_dashboard_summary_and_trend_data_are_scoped_to_viewer_access(): void
    {
        $viewer = $this->createUser('BT - DIGITAL BANKING', false);
        $this->createRiskRecord($viewer, 'BT - DIGITAL BANKING', 'Scoped Dashboard Risk');
        $foreignOwner = $this->createUser('CO - COMPLIANCE', false);
        $this->createRiskRecord($foreignOwner, 'CO - COMPLIANCE', 'Foreign Dashboard Risk');

        $this->actingAs($viewer);

        $dashboard = $this->getJson(route('risks.dashboard-summary'));
        $dashboard->assertOk();

        $dashboardOwners = collect($dashboard->json('summary_by_owner'))->pluck('label')->all();
        $this->assertContains('DIGITAL BANKING', $dashboardOwners);
        $this->assertNotContains('COMPLIANCE', $dashboardOwners);

        $trend = $this->getJson(route('trends.data', [
            'granularity' => 'monthly',
            'days' => 365,
        ]));
        $trend->assertOk();

        $trendOwners = collect($trend->json('owners'))->pluck('owner')->all();
        $this->assertContains('DIGITAL BANKING', $trendOwners);
        $this->assertNotContains('COMPLIANCE', $trendOwners);
    }

    public function test_main_dashboard_renders_overview_sections_without_placeholder_figures(): void
    {
        $viewer = $this->createUser('CO - COMPLIANCE', false);
        $this->createRiskRecord($viewer, 'CO - COMPLIANCE', 'Executive Dashboard Risk');

        $this->actingAs($viewer);

        foreach ([route('dashboard'), route('dashboard', ['view' => 'strategic']), route('management.dashboard'), route('risks.monitoring')] as $url) {
            $response = $this->get($url);
            $response->assertOk();
            $response->assertViewIs('risks.unified-dashboard');
            $response->assertViewHas('dashboardView', 'overview');
            foreach (['summary', 'attention', 'heatmap', 'profile', 'top-risks', 'kris', 'losses', 'actions', 'assurance', 'trend'] as $section) {
                $response->assertSee('data-section="' . $section . '"', false);
            }
            // Figures that used to be hard-coded fallbacks must never be rendered.
            $response->assertDontSee('4.440.000.000');
            $response->assertDontSee('from Q3');
        }

        $analytical = $this->get(route('dashboard', ['view' => 'analytical']));
        $analytical->assertOk();
        $analytical->assertViewHas('dashboardView', 'analytical');
        $analytical->assertViewHas('heatmapMatrix');
    }

    public function test_non_compliance_user_cannot_comment_or_download_attachment_on_foreign_risk(): void
    {
        Storage::fake('local');

        $viewer = $this->createUser('BT - DIGITAL BANKING', false);
        $foreignOwner = $this->createUser('CO - COMPLIANCE', false);
        $foreignRisk = $this->createRiskRecord($foreignOwner, 'CO - COMPLIANCE', 'Foreign Discussion Risk');
        $attachment = $this->createRiskAttachment($foreignRisk, $foreignOwner->id, 'foreign-discussion.txt');

        $this->actingAs($viewer);

        $this->post(route('risks.comments.store', $foreignRisk->id), [
            'content' => 'Cross owner comment should fail',
        ])->assertForbidden();

        $this->get(route('attachments.download', $attachment->id))->assertForbidden();
    }

    public function test_same_owner_peer_can_delete_risk_attachment(): void
    {
        Storage::fake('local');

        $creator = $this->createUser('BT - DIGITAL BANKING', false);
        $peer = $this->createUser('BT - DIGITAL BANKING', false);
        $risk = $this->createRiskRecord($creator, 'BT - DIGITAL BANKING', 'Shared Attachment Risk');
        $attachment = $this->createRiskAttachment($risk, $creator->id, 'shared-owner.txt');

        $this->actingAs($peer);

        $this->from(route('risks.show', $risk->id))
            ->delete(route('attachments.destroy', $attachment->id))
            ->assertRedirect(route('risks.show', $risk->id));

        $this->assertDatabaseMissing('evidence_attachments', [
            'id' => $attachment->id,
        ]);
        Storage::disk('local')->assertMissing($attachment->storage_path);
    }

    public function test_kanban_only_shows_visible_treatments_and_blocks_foreign_updates(): void
    {
        Storage::fake('local');

        $viewer = $this->createUser('BT - DIGITAL BANKING', false);
        $ownRisk = $this->createRiskRecord($viewer, 'BT - DIGITAL BANKING', 'Kanban Own Risk');
        $foreignOwner = $this->createUser('CO - COMPLIANCE', false);
        $foreignRisk = $this->createRiskRecord($foreignOwner, 'CO - COMPLIANCE', 'Kanban Foreign Risk');

        $ownTreatment = $this->createTreatment($ownRisk, 'Own Kanban Treatment');
        $foreignTreatment = $this->createTreatment($foreignRisk, 'Foreign Kanban Treatment');

        $this->actingAs($viewer);

        $this->get(route('treatments.kanban'))
            ->assertOk()
            ->assertSee('Own Kanban Treatment')
            ->assertDontSee('Foreign Kanban Treatment');

        $this->patchJson(route('treatments.update-status', $foreignTreatment->id), [
            'status' => 'Completed',
        ])->assertForbidden();

        $this->patchJson(route('treatments.update-status', $ownTreatment->id), [
            'status' => 'Completed',
        ])->assertUnprocessable();

        $this->patchJson(route('treatments.update-status', $ownTreatment->id), [
            'status' => 'Blocked',
        ])->assertUnprocessable();

        $this->patchJson(route('treatments.update-status', $ownTreatment->id), [
            'status' => 'Blocked',
            'blocked_reason' => 'Waiting for vendor response',
        ])->assertOk();

        $this->assertDatabaseHas('risk_treatments', [
            'id' => $ownTreatment->id,
            'status' => 'Blocked',
            'blocked_reason' => 'Waiting for vendor response',
        ]);

        $this->createTreatmentAttachment($ownTreatment, $viewer->id, 'kanban-evidence.txt');

        $this->patchJson(route('treatments.update-status', $ownTreatment->id), [
            'status' => 'Completed',
        ])->assertOk();

        $this->assertDatabaseHas('risk_treatments', [
            'id' => $ownTreatment->id,
            'status' => 'Completed',
        ]);
    }

    public function test_superadmin_kri_actions_are_read_only(): void
    {
        $superadmin = $this->createUser('CO - COMPLIANCE', true);
        $owner = $this->createUser('CO - COMPLIANCE', false);
        $risk = $this->createRiskRecord($owner, 'CO - COMPLIANCE', 'Superadmin Read Only KRI Risk');

        $this->actingAs($superadmin);

        $this->post(route('kris.store', $risk->id), [
            'metric_name' => 'Unauthorized Superadmin KRI',
            'threshold_type' => 'lower_is_better',
            'threshold_warn' => 5,
            'threshold_critical' => 10,
        ])->assertForbidden();
    }

    private function createRiskRecord(User $creator, string $riskOwner, string $title): Risk
    {
        return Risk::create([
            'risk_id' => 'SEC-' . Str::upper(Str::random(8)),
            'year' => (int) now()->year,
            'risk_title' => $title,
            'risk_category_id' => $this->ensureRiskCategoryId(),
            'risk_owner' => $riskOwner,
            'status' => 'Open',
            'created_by' => $creator->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createTreatment(Risk $risk, string $description): RiskTreatment
    {
        return RiskTreatment::create([
            'risk_id' => $risk->id,
            'option' => 'Mitigation',
            'description' => $description,
            'pic' => $risk->risk_owner,
            'status' => 'Not Started',
            'deadline' => now()->addDays(7)->toDateString(),
            'effectiveness' => 'Medium',
            'effectiveness_score' => 0.5,
        ]);
    }

    private function createRiskAttachment(Risk $risk, int $uploaderId, string $filename): EvidenceAttachment
    {
        $storagePath = 'evidence/risk/' . Str::lower(Str::random(8)) . '-' . $filename;
        Storage::disk('local')->put($storagePath, 'attachment');

        return EvidenceAttachment::create([
            'attachable_type' => Risk::class,
            'attachable_id' => $risk->id,
            'uploaded_by' => $uploaderId,
            'original_name' => $filename,
            'storage_path' => $storagePath,
            'mime_type' => 'text/plain',
            'size' => 10,
            'notes' => null,
        ]);
    }

    private function createTreatmentAttachment(RiskTreatment $treatment, int $uploaderId, string $filename): EvidenceAttachment
    {
        $storagePath = 'evidence/treatment/' . Str::lower(Str::random(8)) . '-' . $filename;
        Storage::disk('local')->put($storagePath, 'attachment');

        return EvidenceAttachment::create([
            'attachable_type' => RiskTreatment::class,
            'attachable_id' => $treatment->id,
            'uploaded_by' => $uploaderId,
            'original_name' => $filename,
            'storage_path' => $storagePath,
            'mime_type' => 'text/plain',
            'size' => 10,
            'notes' => null,
        ]);
    }

    private function ensureRiskCategoryId(): int
    {
        $existingId = RiskCategory::query()->value('id');
        if (!is_null($existingId)) {
            return (int) $existingId;
        }

        return (int) RiskCategory::query()->insertGetId([
            'name' => 'Security Regression Category ' . Str::random(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createUser(string $division, bool $isSuperadmin): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Risk Access User ' . $suffix,
            'username' => 'risk_access_' . $suffix,
            'division' => $division,
            'pending_division' => null,
            'division_verified_at' => now(),
            'is_superadmin' => $isSuperadmin,
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }
}
