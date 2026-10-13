<?php

namespace Tests\Feature;

use App\OjkMitigationItem;
use App\Services\OjkMitigationSpreadsheetImportService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class OjkMitigationMonitoringWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_compliance_user_can_import_ojk_mitigation_spreadsheet(): void
    {
        $user = $this->createUser('CO - COMPLIANCE', false);
        $this->actingAs($user);

        $tempPath = $this->createTempSpreadsheet();
        $upload = new UploadedFile(
            $tempPath,
            'timeline-mitigasi-ojk.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->post(route('ojk-mitigation.import'), [
            'file' => $upload,
        ]);

        $response->assertRedirect(route('ojk-mitigation.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ojk_mitigation_items', [
            'item_number' => '1',
            'action_plan' => 'Aktivasi monitoring transaksi VA secara real-time.',
            'mitigation_status' => 'On Progress',
            'target_date' => '2026-03-10',
        ]);

        $this->assertDatabaseHas('ojk_mitigation_items', [
            'item_number' => '2',
            'mitigation_status' => 'Done',
        ]);

        @unlink($tempPath);
    }

    public function test_compliance_user_can_update_mitigation_status(): void
    {
        $user = $this->createUser('CO - COMPLIANCE', false);
        $this->actingAs($user);

        $item = OjkMitigationItem::query()->create([
            'item_number' => '1',
            'action_plan' => 'Mitigasi awal',
            'pic' => 'Digital Banking',
            'solution' => 'Tuning rule',
            'target' => '10 Maret 2026',
            'target_date' => '2026-03-10',
            'mitigation_status' => 'On Progress',
            'sort_order' => 1,
        ]);

        $response = $this->put(route('ojk-mitigation.update', $item->id), [
            'item_number' => '1',
            'action_plan' => 'Mitigasi awal',
            'pic' => 'Digital Banking',
            'solution' => 'Tuning rule',
            'target' => 'Done',
            'mitigation_status' => 'Done',
            'sort_order' => 1,
        ]);

        $response->assertRedirect(route('ojk-mitigation.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ojk_mitigation_items', [
            'id' => $item->id,
            'mitigation_status' => 'Done',
        ]);
    }

    public function test_import_marks_undated_rows_as_done(): void
    {
        $user = $this->createUser('CO - COMPLIANCE', false);
        $this->actingAs($user);

        $tempPath = $this->createTempSpreadsheetWithUndatedOnProgressRow();
        $upload = new UploadedFile(
            $tempPath,
            'timeline-mitigasi-ojk-undated.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );

        $response = $this->post(route('ojk-mitigation.import'), [
            'file' => $upload,
        ]);

        $response->assertRedirect(route('ojk-mitigation.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ojk_mitigation_items', [
            'item_number' => '9',
            'target_date' => null,
            'mitigation_status' => 'Done',
        ]);

        @unlink($tempPath);
    }

    public function test_compliance_user_can_open_edit_page_for_mitigation_item(): void
    {
        $user = $this->createUser('CO - COMPLIANCE', false);
        $this->actingAs($user);

        $item = OjkMitigationItem::query()->create([
            'item_number' => '3',
            'action_plan' => 'Penguatan kontrol monitoring',
            'pic' => 'Cyber Security',
            'solution' => 'Tuning rule',
            'target' => '11 Maret 2026',
            'target_date' => '2026-03-11',
            'mitigation_status' => 'On Progress',
            'sort_order' => 3,
        ]);

        $response = $this->get(route('ojk-mitigation.edit', $item->id));

        $response->assertOk();
        $response->assertSee('Edit Point Mitigasi OJK');
        $response->assertSee('Simpan Perubahan');
    }

    public function test_import_exception_does_not_expose_internal_detail(): void
    {
        Storage::fake('local');

        $user = $this->createUser('CO - COMPLIANCE', false);
        $this->mock(OjkMitigationSpreadsheetImportService::class, function ($mock): void {
            $mock->shouldReceive('parse')
                ->once()
                ->andThrow(new \RuntimeException('Sensitive OJK parser path'));
        });

        $response = $this->actingAs($user)->post(route('ojk-mitigation.import'), [
            'file' => UploadedFile::fake()->create(
                'invalid-mitigation.xlsx',
                10,
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ),
        ]);

        $response->assertRedirect(route('ojk-mitigation.index'));
        $message = (string) $response->getSession()->get('error');
        $this->assertStringNotContainsString('Sensitive OJK parser path', $message);
        $this->assertStringContainsString('Referensi insiden:', $message);
    }

    private function createTempSpreadsheet(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sheet1');

        $sheet->setCellValue('A2', 'NO');
        $sheet->setCellValue('B2', 'ACTION PLAN');
        $sheet->setCellValue('C2', 'PIC');
        $sheet->setCellValue('D2', 'SOLUSI');
        $sheet->setCellValue('E2', 'TARGET');

        $sheet->setCellValue('A3', '1');
        $sheet->setCellValue('B3', 'Aktivasi monitoring transaksi VA secara real-time.');
        $sheet->setCellValue('C3', 'Cyber Security');
        $sheet->setCellValue('D3', 'Integrasi rule ke FDS');
        $sheet->setCellValue('E3', '10 Maret 2026');

        $sheet->setCellValue('A4', '2');
        $sheet->setCellValue('B4', 'Penyesuaian validasi beneficiary.');
        $sheet->setCellValue('C4', 'Digital Banking');
        $sheet->setCellValue('D4', 'Release patch aplikasi');
        $sheet->setCellValue('E4', 'Done');

        $tempPath = tempnam(sys_get_temp_dir(), 'ojk-mitigation-test-');
        if ($tempPath === false) {
            throw new \RuntimeException('Unable to create temporary test file.');
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    private function createTempSpreadsheetWithUndatedOnProgressRow(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sheet1');

        $sheet->setCellValue('A2', 'NO');
        $sheet->setCellValue('B2', 'ACTION PLAN');
        $sheet->setCellValue('C2', 'PIC');
        $sheet->setCellValue('D2', 'SOLUSI');
        $sheet->setCellValue('E2', 'TARGET');

        $sheet->setCellValue('A3', '9');
        $sheet->setCellValue('B3', 'Validasi data existing.');
        $sheet->setCellValue('C3', 'Cyber Security');
        $sheet->setCellValue('D3', 'Koordinasi lintas unit');
        $sheet->setCellValue('E3', 'Pending verifikasi final');

        $tempPath = tempnam(sys_get_temp_dir(), 'ojk-mitigation-undated-');
        if ($tempPath === false) {
            throw new \RuntimeException('Unable to create temporary test file.');
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    private function createUser(string $division, bool $isSuperadmin): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'OJK User ' . $suffix,
            'username' => 'ojk_' . $suffix,
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
