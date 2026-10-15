<?php

namespace Tests\Feature;

use App\AuditLog;
use App\Risk;
use App\RiskCategory;
use App\RiskIncident;
use App\RiskStatusChangeRequest;
use App\ScheduledReport;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuditLogTrailTest extends TestCase
{
    use RefreshDatabase;

    public function test_approval_shows_who_requested_and_who_approved(): void
    {
        $requester = $this->createUser('Rina Pengaju', 'BT - DIGITAL BANKING');
        $approver = $this->createUser('Bayu Compliance', 'CO - COMPLIANCE');
        $risk = $this->createRisk($requester);
        $request = RiskStatusChangeRequest::create([
            'risk_id' => $risk->id,
            'requested_by' => $requester->id,
            'from_status' => 'Open',
            'to_status' => 'Closed',
            'reason' => 'Treatment selesai',
            'status' => 'pending',
        ]);

        $this->actingAs($approver)->put(route('risk-status-approvals.approve', $request->id), ['review_note' => 'Bukti lengkap'])->assertRedirect();

        $this->actingAs($this->createUser('Super Admin', 'CO - COMPLIANCE', true))
            ->get(route('audit-logs.index', ['module' => 'risk']))
            ->assertOk()
            ->assertSee('Menyetujui perubahan status risk')
            ->assertSee('Diajukan oleh Rina Pengaju')
            ->assertSee('Ditinjau oleh Bayu Compliance')
            ->assertSee('Bukti lengkap')
            ->assertSee('WIB');
    }

    public function test_model_changes_are_recorded_field_by_field(): void
    {
        $user = $this->createUser('Dewi Insiden', 'CO - COMPLIANCE');
        $this->actingAs($user);
        $incident = RiskIncident::createWithGeneratedIncidentCode([
            'risk_owner' => 'BT - DIGITAL BANKING',
            'reported_by' => $user->id,
            'last_updated_by' => $user->id,
            'title' => 'Gangguan transfer',
            'incident_description' => 'Transfer antarbank gagal',
            'occurred_at' => now(),
            'severity' => 'High',
            'status' => 'Open',
            'mitigation_verdict' => 'Not Tested',
        ]);
        $incident->update(['affected_client' => 'BANK CONTOH A']);

        $log = AuditLog::query()->where('event', 'incident.updated')->latest('id')->firstOrFail();
        $this->assertSame($user->id, $log->actor_id);
        // Only the changed field, plus identity fields that name the record.
        $this->assertArrayHasKey('affected_client', $log->before_state);
        $this->assertNull($log->before_state['affected_client']);
        $this->assertSame('BANK CONTOH A', $log->after_state['affected_client']);
        $this->assertSame($incident->incident_code, $log->after_state['incident_code']);
        $this->assertArrayNotHasKey('incident_description', $log->after_state);
        $this->assertTrue(AuditLog::query()->where('event', 'incident.created')->exists());

        $this->actingAs($this->createUser('Super Admin', 'CO - COMPLIANCE', true))
            ->get(route('audit-logs.index', ['event' => 'incident.updated']))
            ->assertOk()
            ->assertSee('Mengubah insiden')
            ->assertSee('Client terdampak')
            ->assertSee('BANK CONTOH A')
            ->assertSee($incident->incident_code)
            ->assertSee('Dewi Insiden');
    }

    public function test_bookkeeping_only_changes_and_bulk_work_are_not_logged_per_row(): void
    {
        $user = $this->createUser('Penjadwal', 'CO - COMPLIANCE');
        $this->actingAs($user);
        $schedule = ScheduledReport::query()->create([
            'user_id' => $user->id, 'name' => 'Harian', 'scope' => 'monitoring', 'format' => 'xlsx', 'frequency' => 'daily',
            'send_time' => '06:00', 'timezone' => 'UTC', 'recipients' => [$user->email], 'filters' => [], 'is_active' => true,
            'next_run_at' => now()->addDay(),
        ]);
        $schedule->update(['next_run_at' => now()->addDays(2), 'last_run_at' => now()]);
        $this->assertFalse(AuditLog::query()->where('event', 'scheduled_report.updated')->exists());

        $schedule->update(['is_active' => false]);
        $this->assertTrue(AuditLog::query()->where('event', 'scheduled_report.updated')->exists());

        RiskCategory::withoutAuditing(fn () => RiskCategory::create(['name' => 'Kategori Massal']));
        $this->assertFalse(AuditLog::query()->where('event', 'parameter.risk_category.created')->exists());
        RiskCategory::create(['name' => 'Kategori Manual']);
        $this->assertTrue(AuditLog::query()->where('event', 'parameter.risk_category.created')->exists());
    }

    public function test_filters_access_and_export(): void
    {
        $alice = $this->createUser('Alice', 'CO - COMPLIANCE');
        $bob = $this->createUser('Bob', 'CO - COMPLIANCE');
        $this->actingAs($alice);
        RiskCategory::create(['name' => 'Kategori Alice']);
        $this->actingAs($bob);
        RiskCategory::create(['name' => 'Kategori Bob']);
        AuditLog::query()->where('actor_id', $bob->id)->update(['created_at' => now()->subDays(10)]);

        $admin = $this->createUser('Super Admin', 'CO - COMPLIANCE', true);
        $this->actingAs($admin);
        $this->get(route('audit-logs.index', ['actor' => $alice->id]))->assertSee('Kategori Alice')->assertDontSee('Kategori Bob');
        $this->get(route('audit-logs.index', ['from' => now()->subDay()->toDateString()]))->assertSee('Kategori Alice')->assertDontSee('Kategori Bob');
        $this->get(route('audit-logs.index', ['q' => 'Kategori Bob']))->assertSee('Kategori Bob')->assertDontSee('Kategori Alice');

        $csv = $this->get(route('audit-logs.export', ['module' => 'parameter']))->assertOk()->streamedContent();
        $this->assertStringContainsString('Kategori Alice', $csv);
        $this->assertStringContainsString('Menambah kategori risk', $csv);
        $this->assertTrue(AuditLog::query()->where('event', 'audit.exported')->where('actor_id', $admin->id)->exists());

        // Compliance owns the application but the audit trail stays with superadmins.
        $this->actingAs($alice)->get(route('audit-logs.index'))->assertStatus(403);
    }

    private function createUser(string $name, string $division, bool $superadmin = false): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => $name,
            'username' => 'audit_' . $suffix,
            'division' => $division,
            'division_verified_at' => now(),
            'is_superadmin' => $superadmin,
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('test-password'),
        ]);
    }

    private function createRisk(User $creator): Risk
    {
        return Risk::create([
            'risk_id' => 'AUD-' . Str::upper(Str::random(6)),
            'year' => (int) now()->year,
            'risk_title' => 'Audit trail test risk',
            'risk_category_id' => RiskCategory::withoutAuditing(fn () => RiskCategory::query()->firstOrCreate(['name' => 'Audit Test Category']))->id,
            'risk_owner' => 'BT - DIGITAL BANKING',
            'status' => 'Open',
            'created_by' => $creator->id,
        ]);
    }
}
