<?php

namespace Tests\Feature;

use App\User;
use App\SultengChecklistItem;
use App\SultengChecklistEvidence;
use App\Services\SultengEvidenceService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SultengInvestigationWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\SultengChecklistSeeder::class);
        $this->user = $this->createApprovedUser();
        $this->actingAs($this->user);
    }

    public function test_user_can_access_sulteng_dashboard(): void
    {
        $response = $this->get(route('sulteng.dashboard'));

        $response->assertOk();
        $response->assertSee('Monitoring Assessment');
        $response->assertSee('Bank Sulteng');
        $response->assertSee('Total Item Checklist');
        $response->assertSee('Upload Evidence');
    }

    public function test_user_can_access_sulteng_items_page(): void
    {
        $response = $this->get(route('sulteng.items'));

        $response->assertOk();
        $response->assertSee('Upload Evidence');
        $response->assertSee('Rule Firewall');
        $response->assertSee('Log Firewall');
        $response->assertSee('Monitoring Assessment');
    }

    public function test_normal_user_sees_assessment_submenu_with_sulteng_and_sumut(): void
    {
        $normalUser = $this->createApprovedUser(false);
        $this->actingAs($normalUser);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Monitoring Assessment');
        $response->assertSee('data-assessment-menu', false);
        $response->assertSee('Sulteng');
        $response->assertSee('Sumut 27 Point');
        $response->assertSee(route('sulteng.dashboard'));
        $response->assertSee(route('sumut.dashboard'));
    }

    public function test_user_can_access_sumut_monitoring_page(): void
    {
        $response = $this->get(route('assessment.sumut'));

        $response->assertOk();
        $response->assertSee('Bank Sumut');
        $response->assertSee('Sumut 27 Point');
        $response->assertSee('Executive Dashboard');
    }

    public function test_user_can_filter_items_by_category(): void
    {
        $response = $this->get(route('sulteng.items', ['category' => 'A']));

        $response->assertOk();
        $response->assertSee('Firewall dan WAF');
        $response->assertSee('A.1');
    }

    public function test_upload_single_evidence_names_file_matching_point_title(): void
    {
        $item = SultengChecklistItem::where('code', 'A.1')->firstOrFail();
        $cleanTitle = $item->clean_title;

        // Cleanup before test
        $this->cleanupItemEvidences($item);

        $tempFile = tempnam(sys_get_temp_dir(), 'sulteng_test_');
        file_put_contents($tempFile, 'Sample evidence content for single upload');
        $uploaded = new UploadedFile($tempFile, 'original_upload.pdf', 'application/pdf', null, true);

        $response = $this->post(route('sulteng.items.upload', $item->id), [
            'files' => [$uploaded],
            'notes' => 'Testing single upload',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $item->refresh();
        $this->assertEquals(1, $item->evidences()->count());

        $evidence = $item->evidences()->first();
        $this->assertEquals("{$cleanTitle}.pdf", $evidence->file_name);
        $this->assertTrue(file_exists($evidence->file_path));

        // Cleanup after test
        $this->cleanupItemEvidences($item);
    }

    public function test_upload_multiple_evidences_adds_numerical_suffixes(): void
    {
        $item = SultengChecklistItem::where('code', 'A.2')->firstOrFail();
        $cleanTitle = $item->clean_title;

        // Cleanup before test
        $this->cleanupItemEvidences($item);

        // Upload first file
        $tempFile1 = tempnam(sys_get_temp_dir(), 'sulteng_test_');
        file_put_contents($tempFile1, 'Content 1');
        $uploaded1 = new UploadedFile($tempFile1, 'first.pdf', 'application/pdf', null, true);

        $this->post(route('sulteng.items.upload', $item->id), [
            'files' => [$uploaded1],
        ]);

        // Upload second file
        $tempFile2 = tempnam(sys_get_temp_dir(), 'sulteng_test_');
        file_put_contents($tempFile2, 'Content 2');
        $uploaded2 = new UploadedFile($tempFile2, 'second.pdf', 'application/pdf', null, true);

        $this->post(route('sulteng.items.upload', $item->id), [
            'files' => [$uploaded2],
        ]);

        $item->refresh();
        $evidences = $item->evidences()->orderBy('evidence_index')->get();

        $this->assertEquals(2, $evidences->count());
        $this->assertEquals("{$cleanTitle}-1.pdf", $evidences[0]->file_name);
        $this->assertEquals("{$cleanTitle}-2.pdf", $evidences[1]->file_name);
        $this->assertTrue(file_exists($evidences[0]->file_path));
        $this->assertTrue(file_exists($evidences[1]->file_path));

        // Cleanup
        $this->cleanupItemEvidences($item);
    }

    public function test_user_can_update_item_status(): void
    {
        $item = SultengChecklistItem::where('code', 'A.3')->firstOrFail();

        $response = $this->post(route('sulteng.items.update', $item->id), [
            'status' => 'Diterima',
            'pic_division' => 'Infra Team',
            'notes' => 'Status updated via test',
        ]);

        $response->assertRedirect();
        $item->refresh();
        $this->assertEquals('Diterima', $item->status);
        $this->assertEquals('Infra Team', $item->pic_division);
    }

    public function test_dangerous_file_upload_is_rejected_by_validation(): void
    {
        $item = SultengChecklistItem::where('code', 'A.1')->firstOrFail();

        $dangerousFile = UploadedFile::fake()->create('malicious_payload.php', 10, 'application/x-php');

        $response = $this->post(route('sulteng.items.upload', $item->id), [
            'files' => [$dangerousFile],
        ]);

        $response->assertSessionHasErrors('files.0');
    }

    public function test_path_traversal_on_evidence_download_is_forbidden(): void
    {
        $item = SultengChecklistItem::where('code', 'A.1')->firstOrFail();

        // Create an evidence record pointing to an unauthorized external path
        $maliciousEvidence = $item->evidences()->create([
            'uploaded_by' => $this->user->id,
            'file_name' => 'fake_win.ini',
            'file_path' => 'C:\\Windows\\win.ini', // Outside allowed storage roots
            'storage_location' => 'local_sulteng',
            'original_filename' => 'fake_win.ini',
            'file_size' => 100,
            'mime_type' => 'text/plain',
            'evidence_index' => 99,
        ]);

        $response = $this->get(route('sulteng.evidence.download', $maliciousEvidence->id));
        $response->assertStatus(403);

        $previewResponse = $this->get(route('sulteng.evidence.preview', $maliciousEvidence->id));
        $previewResponse->assertStatus(403);
    }

    public function test_non_superadmin_cannot_trigger_sync_excel(): void
    {
        $normalUser = $this->createApprovedUser(false);
        $this->actingAs($normalUser);

        $response = $this->post(route('sulteng.sync-excel'));
        $response->assertStatus(403);
    }

    public function test_non_superadmin_cannot_delete_other_user_evidence_idor_protection(): void
    {
        $item = SultengChecklistItem::where('code', 'A.1')->firstOrFail();

        // Upload evidence as user1
        $uploaderUser = $this->createApprovedUser(false);
        $otherUser = $this->createApprovedUser(false);

        $fakeFile = UploadedFile::fake()->create('legit_evidence.pdf', 50, 'application/pdf');
        $this->actingAs($uploaderUser);

        $this->post(route('sulteng.items.upload', $item->id), [
            'files' => [$fakeFile],
        ]);

        $evidence = $item->evidences()->latest()->firstOrFail();

        // Now attempt to delete as otherUser (IDOR attack attempt)
        $this->actingAs($otherUser);
        $deleteResponse = $this->delete(route('sulteng.evidence.destroy', $evidence->id));
        $deleteResponse->assertStatus(403);

        // Verify evidence was not deleted
        $this->assertDatabaseHas('sulteng_checklist_evidences', [
            'id' => $evidence->id,
        ]);

        // Cleanup
        $this->cleanupItemEvidences($item);
    }

    public function test_export_excel_succeeds_with_nosniff_header(): void
    {
        $response = $this->get(route('sulteng.export-excel'));

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertTrue(str_contains(
            (string) $response->headers->get('content-type'),
            'spreadsheetml'
        ));
    }

    public function test_binary_status_system_only_has_diterima_and_belum_diterima_with_clickable_filters(): void
    {
        // Assert no items in database have 'Dalam Proses'
        $inProgressCount = SultengChecklistItem::where('status', 'Dalam Proses')->count();
        $this->assertEquals(0, $inProgressCount);

        // Check dashboard response contains binary indicators
        $dashboardResponse = $this->get(route('sulteng.dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('Diterima (Hijau)');
        $dashboardResponse->assertSee('Belum Diterima (Merah)');
        $dashboardResponse->assertDontSee('Status Orange (Dalam Proses)');

        // Test filtering items page by status 'Diterima'
        $responseDiterima = $this->get(route('sulteng.items', ['status' => 'Diterima']));
        $responseDiterima->assertOk();
        $responseDiterima->assertSee('Diterima');

        // Test filtering items page by status 'Belum Diterima'
        $responseBelum = $this->get(route('sulteng.items', ['status' => 'Belum Diterima']));
        $responseBelum->assertOk();
        $responseBelum->assertSee('Belum Diterima');
    }

    private function createApprovedUser(bool $isSuperadmin = true): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Sulteng Auditor ' . $suffix,
            'username' => 'auditor_' . $suffix,
            'division' => 'CO - COMPLIANCE',
            'pending_division' => null,
            'division_verified_at' => now(),
            'is_superadmin' => $isSuperadmin,
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }

    private function cleanupItemEvidences(SultengChecklistItem $item): void
    {
        $service = new SultengEvidenceService();
        foreach ($item->evidences as $ev) {
            $service->deleteEvidence($ev);
        }
    }
}
