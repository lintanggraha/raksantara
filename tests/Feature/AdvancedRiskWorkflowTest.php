<?php

namespace Tests\Feature;

use App\RadarGroup;
use App\Risk;
use App\RiskCategory;
use App\RiskQuantProfile;
use App\RiskResidualAnalysis;
use App\RiskTreatment;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdvancedRiskWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_non_superadmin_status_change_to_monitoring_creates_pending_request(): void
    {
        $user = $this->createUser('CO - COMPLIANCE', false);
        $risk = $this->createRiskRecord($user, 'Open', 'CO - COMPLIANCE');

        $this->actingAs($user);

        $response = $this->put(route('risks.update', $risk->id), [
            'risk_title' => 'Updated Risk Title',
            'risk_category_id' => $risk->risk_category_id,
            'risk_owner' => $risk->risk_owner,
            'status' => 'Monitoring',
            'status_change_reason' => 'Need to move into monitoring status.',
        ]);

        $response->assertRedirect(route('risks.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('risks', [
            'id' => $risk->id,
            'status' => 'Open',
        ]);

        $this->assertDatabaseHas('risk_status_change_requests', [
            'risk_id' => $risk->id,
            'requested_by' => $user->id,
            'from_status' => 'Open',
            'to_status' => 'Monitoring',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'risk.status_change.requested',
            'auditable_type' => 'App\\RiskStatusChangeRequest',
        ]);
    }

    public function test_superadmin_cannot_edit_risk_data(): void
    {
        $superadmin = $this->createUser('CO - COMPLIANCE', true);
        $risk = $this->createRiskRecord($superadmin, 'Open', 'BT - DIGITAL BANKING');

        $this->actingAs($superadmin);

        $response = $this->put(route('risks.update', $risk->id), [
            'risk_title' => 'Updated by Superadmin',
            'risk_category_id' => $risk->risk_category_id,
            'risk_owner' => $risk->risk_owner,
            'status' => 'Closed',
        ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('risks', [
            'id' => $risk->id,
            'status' => 'Open',
        ]);

        $this->assertDatabaseMissing('risk_status_change_requests', [
            'risk_id' => $risk->id,
            'to_status' => 'Closed',
            'status' => 'pending',
        ]);
    }

    public function test_superadmin_cannot_export_reports(): void
    {
        $superadmin = $this->createUser('CO - COMPLIANCE', true);
        $this->actingAs($superadmin);

        $this->get(route('reports.export', [
            'scope' => 'risks',
            'format' => 'pdf',
        ]))->assertForbidden();
    }

    public function test_compliance_user_can_manage_risks_across_all_owners(): void
    {
        $actor = $this->createUser('CO - COMPLIANCE', false);
        $otherUser = $this->createUser('BT - DIGITAL BANKING', false);
        $crossOwnerRisk = $this->createRiskRecord($otherUser, 'Open', 'BT - DIGITAL BANKING');

        $this->actingAs($actor);

        $createResponse = $this->post(route('risks.store'), [
            'risk_title' => 'Cross Owner Create',
            'risk_description' => 'Created by approved non-superadmin user.',
            'risk_category_id' => $this->ensureRiskCategoryId(),
            'risk_owner' => 'BT - DIGITAL BANKING',
        ]);

        $createResponse->assertRedirect(route('risks.index'));
        $createResponse->assertSessionHas('success');
        $this->assertDatabaseHas('risks', [
            'risk_title' => 'Cross Owner Create',
            'risk_owner' => 'BT - DIGITAL BANKING',
            'created_by' => $actor->id,
        ]);

        $updateResponse = $this->put(route('risks.update', $crossOwnerRisk->id), [
            'risk_title' => 'Cross Owner Updated',
            'risk_category_id' => $crossOwnerRisk->risk_category_id,
            'risk_owner' => $crossOwnerRisk->risk_owner,
            'status' => 'Open',
        ]);

        $updateResponse->assertRedirect(route('risks.index'));
        $updateResponse->assertSessionHas('success');
        $this->assertDatabaseHas('risks', [
            'id' => $crossOwnerRisk->id,
            'risk_title' => 'Cross Owner Updated',
        ]);

        $deleteResponse = $this->delete(route('risks.destroy', $crossOwnerRisk->id));
        $deleteResponse->assertRedirect(route('risks.index'));
        $deleteResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('risks', [
            'id' => $crossOwnerRisk->id,
        ]);
    }

    public function test_non_compliance_user_can_only_manage_own_risk_owner(): void
    {
        $actor = $this->createUser('BT - DIGITAL BANKING', false);
        $otherUser = $this->createUser('CO - COMPLIANCE', false);
        $crossOwnerRisk = $this->createRiskRecord($otherUser, 'Open', 'CO - COMPLIANCE');
        $ownRisk = $this->createRiskRecord($actor, 'Open', 'BT - DIGITAL BANKING');

        $this->actingAs($actor);

        $crossOwnerCreate = $this
            ->from(route('risks.create'))
            ->post(route('risks.store'), [
                'risk_title' => 'Cross Owner Create Should Fail',
                'risk_description' => 'This should be rejected.',
                'risk_category_id' => $this->ensureRiskCategoryId(),
                'risk_owner' => 'CO - COMPLIANCE',
            ]);

        $crossOwnerCreate->assertRedirect(route('risks.create'));
        $crossOwnerCreate->assertSessionHasErrors(['risk_owner']);
        $this->assertDatabaseMissing('risks', [
            'risk_title' => 'Cross Owner Create Should Fail',
        ]);

        $ownCreate = $this->post(route('risks.store'), [
            'risk_title' => 'Own Owner Create Success',
            'risk_description' => 'Created under own owner.',
            'risk_category_id' => $this->ensureRiskCategoryId(),
            'risk_owner' => 'BT - DIGITAL BANKING',
        ]);

        $ownCreate->assertRedirect(route('risks.index'));
        $ownCreate->assertSessionHas('success');
        $this->assertDatabaseHas('risks', [
            'risk_title' => 'Own Owner Create Success',
            'risk_owner' => 'BT - DIGITAL BANKING',
            'created_by' => $actor->id,
        ]);

        $crossOwnerUpdate = $this->put(route('risks.update', $crossOwnerRisk->id), [
            'risk_title' => 'Cross Owner Update Should Fail',
            'risk_category_id' => $crossOwnerRisk->risk_category_id,
            'risk_owner' => $crossOwnerRisk->risk_owner,
            'status' => 'Open',
        ]);
        $crossOwnerUpdate->assertForbidden();

        $ownUpdate = $this->put(route('risks.update', $ownRisk->id), [
            'risk_title' => 'Own Owner Update Success',
            'risk_category_id' => $ownRisk->risk_category_id,
            'risk_owner' => $ownRisk->risk_owner,
            'status' => 'Open',
        ]);
        $ownUpdate->assertRedirect(route('risks.index'));
        $ownUpdate->assertSessionHas('success');
        $this->assertDatabaseHas('risks', [
            'id' => $ownRisk->id,
            'risk_title' => 'Own Owner Update Success',
        ]);

        $this->delete(route('risks.destroy', $crossOwnerRisk->id))->assertForbidden();

        $ownDelete = $this->delete(route('risks.destroy', $ownRisk->id));
        $ownDelete->assertRedirect(route('risks.index'));
        $ownDelete->assertSessionHas('success');
        $this->assertDatabaseMissing('risks', [
            'id' => $ownRisk->id,
        ]);
    }

    public function test_user_can_upload_download_and_delete_risk_evidence_attachment(): void
    {
        Storage::fake('local');

        $user = $this->createUser('CO - COMPLIANCE', false);
        $risk = $this->createRiskRecord($user, 'Open', 'CO - COMPLIANCE');

        $this->actingAs($user);

        $uploadResponse = $this->post(route('risks.attachments.store', $risk->id), [
            'file' => UploadedFile::fake()->create('proof.pdf', 200, 'application/pdf'),
            'notes' => 'Initial evidence upload',
        ]);

        $uploadResponse->assertStatus(302);
        $uploadResponse->assertSessionHas('success');

        $attachment = DB::table('evidence_attachments')
            ->where('attachable_type', Risk::class)
            ->where('attachable_id', $risk->id)
            ->first();

        $this->assertNotNull($attachment);
        Storage::disk('local')->assertExists($attachment->storage_path);

        $this->get(route('attachments.download', $attachment->id))->assertOk();

        $deleteResponse = $this->delete(route('attachments.destroy', $attachment->id));
        $deleteResponse->assertStatus(302);
        $deleteResponse->assertSessionHas('success');

        $this->assertDatabaseMissing('evidence_attachments', [
            'id' => $attachment->id,
        ]);
    }

    public function test_scan_alerts_command_creates_due_soon_and_kri_breach_alerts(): void
    {
        $user = $this->createUser('CO - COMPLIANCE', true);
        $risk = $this->createRiskRecord($user, 'Open', 'CO - COMPLIANCE');

        $treatment = RiskTreatment::create([
            'risk_id' => $risk->id,
            'option' => 'Mitigation',
            'description' => 'Mitigate immediately',
            'pic' => 'CO - COMPLIANCE',
            'deadline' => now()->addDays(2)->toDateString(),
            'effectiveness' => 'High',
            'effectiveness_score' => 1.00,
        ]);

        $code = 'S' . random_int(100, 999);
        $radarGroup = RadarGroup::create([
            'code' => $code,
            'description' => 'Radar Group ' . $code,
        ]);

        RiskQuantProfile::create([
            'radar_group_id' => $radarGroup->id,
            'potential_loss_inherent' => 1000000,
            'residual_loss_factor' => 0.30,
            'risk_appetite_score' => 5,
            'risk_appetite_loss' => 50000,
        ]);

        RiskResidualAnalysis::create([
            'risk_id' => $risk->id,
            'probability_score' => 3,
            'impact_score' => 4,
            'residual_risk_score' => 10,
            'risk_level' => 'Moderate',
            'potential_loss' => 120000,
            'radar_group_id' => $radarGroup->id,
            'radar_code' => $code,
            'radar_description' => $radarGroup->description,
        ]);

        $this->artisan('risk:scan-alerts --days=3')->assertExitCode(0);

        $this->assertDatabaseHas('risk_alerts', [
            'risk_treatment_id' => $treatment->id,
            'alert_type' => 'treatment_deadline_due_soon',
            'status' => 'open',
        ]);

        $this->assertDatabaseHas('risk_alerts', [
            'risk_id' => $risk->id,
            'risk_treatment_id' => null,
            'alert_type' => 'kri_threshold_breach',
            'status' => 'open',
        ]);
    }

    private function createRiskRecord(User $creator, string $status, string $riskOwner): Risk
    {
        return Risk::create([
            'risk_id' => 'TEST-' . Str::upper(Str::random(8)),
            'year' => (int) now()->year,
            'risk_title' => 'Risk ' . Str::random(8),
            'risk_category_id' => $this->ensureRiskCategoryId(),
            'risk_owner' => $riskOwner,
            'status' => $status,
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
            'name' => 'Test Category ' . Str::random(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createUser(string $division, bool $isSuperadmin): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Workflow User ' . $suffix,
            'username' => 'workflow_' . $suffix,
            'division' => $division,
            'pending_division' => null,
            'division_verified_at' => now(),
            'is_superadmin' => $isSuperadmin,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }
}
