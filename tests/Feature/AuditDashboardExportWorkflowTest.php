<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
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
