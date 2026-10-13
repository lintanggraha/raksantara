<?php

namespace Tests\Feature;

use App\User;
use App\SumutChecklistItem;
use App\SumutChecklistEvidence;
use Database\Seeders\SumutChecklistSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SumutMonitoringWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SumutChecklistSeeder::class);
        $this->user = $this->createApprovedUser();
        $this->actingAs($this->user);
    }

    public function test_user_can_access_sumut_dashboard(): void
    {
        $response = $this->get(route('sumut.dashboard'));

        $response->assertOk();
        $response->assertSee('Bank Sumut');
        $response->assertSee('Sumut 27 Point');
        $response->assertSee('Executive Dashboard: Sumut 27 Point');
        $response->assertSee('Total Point Pencegahan dan Penguatan');
        $response->assertSee('27');
        $response->assertSee('Bukti Ada');
        $response->assertSee('Rata-rata Progress Bank Sumut');
        $response->assertSee('Buka 27 Point Checklist');
        $response->assertSee('Export Excel Rekap');
        $response->assertSee('chart-categories-bar');
        $response->assertSee('chart-pic-donut');
        $response->assertDontSee('Target Point');
    }

    public function test_user_can_access_sumut_items_page(): void
    {
        $response = $this->get(route('sumut.items'));

        $response->assertOk();
        $response->assertSee('27 Point Checklist & Evidence Explorer');
        $response->assertSee('Progress Bank Sumut');
        $response->assertSee('Point #01');
        $response->assertSee('Point #02');
        $response->assertSee('CBS & Integritas Transaksi');
        $response->assertSee('Analisis Kesesuaian Point');
        $response->assertSee('Rekomendasi Bukti Tambahan');
    }

    public function test_user_can_filter_items_by_category(): void
    {
        $response = $this->get(route('sumut.items', ['category' => 'CBS & Integritas Transaksi']));

        $response->assertOk();
        $response->assertSee('Point #01');
        $response->assertSee('Point #02');
        $response->assertSee('Point #03');
        $response->assertSee('Point #04');
        $response->assertDontSee('Point #09'); // ManageEngine is under Containment
    }

    public function test_user_can_filter_items_by_status(): void
    {
        $response = $this->get(route('sumut.items', ['status' => 'Ada']));

        $response->assertOk();
        $response->assertSee('Point #01');
        $response->assertSee('Bukti Ada');
    }

    public function test_user_can_filter_items_by_progress_status(): void
    {
        $response = $this->get(route('sumut.items', ['progress_status' => 'on_progress']));

        $response->assertOk();
        $response->assertSee('Point #08');
        $response->assertSee('Point #11');
        $response->assertSee('Point #17');
        $response->assertSee('Point #19');
        $response->assertSee('Point #24');
        $response->assertSee('Point #27');
        $response->assertDontSee('Point #01'); // Point 01 is 100% Done
    }

    public function test_user_can_search_items_by_keyword(): void
    {
        $response = $this->get(route('sumut.items', ['search' => 'ManageEngine']));

        $response->assertOk();
        $response->assertSee('Point #09');
        $response->assertSee('Point #10');
        $response->assertDontSee('Point #01');
    }

    public function test_user_can_update_item_status_and_recommendation(): void
    {
        $item = SumutChecklistItem::where('point_number', 1)->firstOrFail();

        $response = $this->post(route('sumut.items.update', $item->id), [
            'status' => 'Ada',
            'notes' => 'Catatan verifikasi diperbarui secara otomatis.',
            'recommendation' => 'Rekomendasi bukti tambahan yang telah disesuaikan.',
            'follow_up' => 'Follow up status done.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sumut_checklist_items', [
            'id' => $item->id,
            'status' => 'Ada',
            'notes' => 'Catatan verifikasi diperbarui secara otomatis.',
            'recommendation' => 'Rekomendasi bukti tambahan yang telah disesuaikan.',
        ]);
    }

    public function test_user_can_download_evidence_file(): void
    {
        $evidence = SumutChecklistEvidence::first();
        $this->assertNotNull($evidence);

        if (file_exists($evidence->file_path)) {
            $response = $this->get(route('sumut.evidence.download', $evidence->id));
            $response->assertOk();
            $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
        } else {
            $this->markTestSkipped('Physical evidence file path not present on local machine in this environment.');
        }
    }

    public function test_user_can_preview_evidence_file(): void
    {
        $evidence = SumutChecklistEvidence::first();
        $this->assertNotNull($evidence);

        if (file_exists($evidence->file_path)) {
            $response = $this->get(route('sumut.evidence.preview', $evidence->id));
            $response->assertOk();
            $this->assertStringContainsString('inline', $response->headers->get('content-disposition'));
        } else {
            $this->markTestSkipped('Physical evidence file path not present on local machine in this environment.');
        }
    }

    public function test_user_can_export_excel(): void
    {
        $response = $this->get(route('sumut.export-excel'));

        $response->assertOk();
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
        $this->assertStringContainsString('Monitoring_Sumut_27_Point_', $response->headers->get('content-disposition'));
    }

    /**
     * Helper to create an approved active user
     */
    protected function createApprovedUser(bool $isSuperadmin = true): User
    {
        $suffix = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(8));

        return User::create([
            'name' => 'Sumut Auditor ' . $suffix,
            'username' => 'sumut_auditor_' . $suffix,
            'division' => 'CO - COMPLIANCE',
            'pending_division' => null,
            'division_verified_at' => now(),
            'is_superadmin' => $isSuperadmin,
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);
    }
}
