<?php

namespace Tests\Feature;

use App\AuditLog;
use App\DataPurgeRequest;
use App\DataRetentionSchedule;
use App\Exports\MultiSheetReportExport;
use App\ResilienceBackupRecord;
use App\ResilienceRestoreTest;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class OperationalResilienceEvidenceExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_compliance_can_download_resilience_audit_pack_and_event_is_audited(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-12 06:30:00'));
        $compliance = $this->createUser('CO - COMPLIANCE');
        $backup = ResilienceBackupRecord::create([
            'backup_reference' => 'BKP-DB-TEST',
            'asset_scope' => 'database',
            'environment' => 'production',
            'storage_location' => 'vault://test/backup',
            'encryption_status' => 'encrypted',
            'status' => 'successful',
            'started_at' => now()->subHour(),
            'completed_at' => now(),
            'recorded_by' => $compliance->id,
        ]);
        ResilienceRestoreTest::create([
            'resilience_backup_record_id' => $backup->id,
            'test_type' => 'full_restore',
            'performed_at' => now(),
            'result' => 'passed',
            'performed_by' => $compliance->id,
        ]);
        $schedule = DataRetentionSchedule::create([
            'record_type' => 'Audit Logs',
            'retention_months' => 84,
            'status' => 'active',
            'created_by' => $compliance->id,
            'updated_by' => $compliance->id,
        ]);
        DataPurgeRequest::create([
            'data_retention_schedule_id' => $schedule->id,
            'scope_reference' => 'audit logs before 2019',
            'requested_at' => now(),
            'legal_hold_checked' => true,
            'justification' => 'Retention elapsed.',
            'status' => 'approved',
            'requested_by' => $compliance->id,
        ]);

        Excel::fake();
        $this->actingAs($compliance)
            ->get(route('operational-resilience.evidence-pack.download'))
            ->assertOk();

        Excel::assertDownloaded('operational-resilience-audit-pack-20260812-063000.xlsx', function (MultiSheetReportExport $export) {
            return count($export->sheets()) === 5;
        });
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $compliance->id,
            'event' => 'resilience.evidence_pack.exported',
        ]);
        Carbon::setTestNow();
    }

    public function test_non_compliance_cannot_download_resilience_audit_pack(): void
    {
        $owner = $this->createUser('BT - DIGITAL BANKING');

        $this->actingAs($owner)
            ->get(route('operational-resilience.evidence-pack.download'))
            ->assertRedirect(route('risks.index'));
    }

    private function createUser(string $division): User
    {
        $suffix = Str::lower(Str::random(10));

        return User::create([
            'name' => 'Evidence User ' . $suffix,
            'username' => 'evidence_' . $suffix,
            'division' => $division,
            'division_verified_at' => now(),
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }
}
