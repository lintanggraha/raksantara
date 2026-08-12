<?php

namespace Tests\Feature;

use App\ResilienceBackupRecord;
use App\ResilienceRestoreTest;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class OperationalResilienceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_compliance_can_record_backup_restore_test_and_verify_evidence(): void
    {
        $compliance = $this->createUser('CO - COMPLIANCE');

        $this->actingAs($compliance)
            ->post(route('operational-resilience.backups.store'), [
                'backup_reference' => 'BKP-DB-20260812-001',
                'asset_scope' => 'database',
                'environment' => 'production',
                'storage_location' => 'vault://riskmanagement/database/2026-08-12',
                'encryption_status' => 'encrypted',
                'status' => 'successful',
                'started_at' => now()->subMinutes(20)->toDateTimeString(),
                'completed_at' => now()->subMinutes(10)->toDateTimeString(),
                'retention_until' => now()->addDays(90)->toDateTimeString(),
                'checksum' => 'sha256:backup-checksum',
                'evidence_reference' => 'backup-run-2026-08-12',
            ])
            ->assertRedirect(route('operational-resilience.index'));

        $backup = ResilienceBackupRecord::query()->firstOrFail();
        $this->actingAs($compliance)
            ->post(route('operational-resilience.restore-tests.store', $backup->id), [
                'test_type' => 'ransomware_recovery',
                'performed_at' => now()->toDateTimeString(),
                'recovery_time_minutes' => 47,
                'recovery_point_minutes' => 15,
                'result' => 'passed_with_findings',
                'findings' => 'Restore completed. Automate evidence collection.',
                'remediation_plan' => 'Add scheduled evidence capture to the runbook.',
                'evidence_reference' => 'restore-drill-2026-08-12',
            ])
            ->assertRedirect(route('operational-resilience.index'));

        $restoreTest = ResilienceRestoreTest::query()->firstOrFail();
        $this->actingAs($compliance)
            ->put(route('operational-resilience.restore-tests.verify', $restoreTest->id))
            ->assertRedirect(route('operational-resilience.index'));

        $this->assertDatabaseHas('resilience_backup_records', [
            'id' => $backup->id,
            'status' => 'successful',
            'encryption_status' => 'encrypted',
        ]);
        $this->assertDatabaseHas('resilience_restore_tests', [
            'id' => $restoreTest->id,
            'test_type' => 'ransomware_recovery',
            'result' => 'passed_with_findings',
            'verified_by' => $compliance->id,
        ]);
    }

    public function test_non_compliance_cannot_record_backup_evidence(): void
    {
        $owner = $this->createUser('BT - DIGITAL BANKING');

        $this->actingAs($owner)
            ->post(route('operational-resilience.backups.store'), [
                'asset_scope' => 'database',
                'environment' => 'production',
                'storage_location' => 'vault://unauthorized',
                'encryption_status' => 'encrypted',
                'status' => 'successful',
                'started_at' => now()->subMinutes(20)->toDateTimeString(),
            ])
            ->assertRedirect(route('risks.index'));

        $this->assertSame(0, ResilienceBackupRecord::query()->count());
    }

    private function createUser(string $division): User
    {
        $suffix = Str::lower(Str::random(10));

        return User::create([
            'name' => 'Resilience User ' . $suffix,
            'username' => 'resilience_' . $suffix,
            'division' => $division,
            'division_verified_at' => now(),
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }
}
