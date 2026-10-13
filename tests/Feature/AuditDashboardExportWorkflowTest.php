<?php

namespace Tests\Feature;

use App\User;
use App\Services\AuditDashboardSpreadsheetImportService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuditDashboardExportWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_audit_dashboard_index_uses_snapshot_source_when_no_imported_data(): void
    {
        $user = $this->createUser('CO - COMPLIANCE', false);
        $this->actingAs($user);

        $response = $this->get(route('audit.placeholder'));

        $response->assertOk()
            ->assertViewHas('auditDashboard', function ($auditDashboard) {
                return is_array($auditDashboard)
                    && ($auditDashboard['source'] ?? null) === 'snapshot';
            });
    }

    public function test_compliance_user_can_export_audit_dashboard_pdf(): void
    {
        $user = $this->createUser('CO - COMPLIANCE', false);
        $this->actingAs($user);

        $response = $this->get(route('audit.export', ['format' => 'pdf']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertNotEmpty((string) $response->getContent());
    }

    public function test_audit_import_failure_does_not_expose_exception_detail(): void
    {
        Storage::fake('local');

        $user = $this->createUser('CO - COMPLIANCE', false);
        $this->mock(AuditDashboardSpreadsheetImportService::class, function ($mock): void {
            $mock->shouldReceive('parse')
                ->once()
                ->andThrow(new \RuntimeException('Sensitive parser filesystem detail'));
        });

        $response = $this->actingAs($user)->post(route('audit.import.store'), [
            'file' => UploadedFile::fake()->create(
                'invalid-audit.xlsx',
                10,
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ),
        ]);

        $response->assertRedirect(route('audit.placeholder'));
        $message = (string) $response->getSession()->get('error');
        $this->assertStringNotContainsString('Sensitive parser filesystem detail', $message);
        $this->assertStringContainsString('Referensi insiden:', $message);
    }

    private function createUser(string $division, bool $isSuperadmin): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Audit User ' . $suffix,
            'username' => 'audit_' . $suffix,
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
