<?php

namespace Tests\Feature;

use App\DataPurgeRequest;
use App\DataRetentionSchedule;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class DataRetentionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_compliance_requests_purge_and_independent_superadmin_approves_and_records_evidence(): void
    {
        $compliance = $this->createUser('CO - COMPLIANCE');
        $superadmin = $this->createUser('CO - COMPLIANCE', true);

        $this->actingAs($compliance)
            ->post(route('data-retention.schedules.store'), [
                'record_type' => 'Audit Logs',
                'description' => 'Application audit event records.',
                'retention_months' => 84,
                'owner_name' => 'Compliance',
                'status' => 'active',
                'next_review_at' => now()->addYear()->toDateString(),
                'legal_basis' => 'Internal retention policy.',
            ])
            ->assertRedirect(route('data-retention.index'));

        $schedule = DataRetentionSchedule::query()->firstOrFail();
        $this->actingAs($compliance)
            ->post(route('data-retention.purge-requests.store'), [
                'data_retention_schedule_id' => $schedule->id,
                'scope_reference' => 'audit_logs before 2019-01-01',
                'estimated_record_count' => 1200,
                'proposed_purge_at' => now()->addDays(7)->toDateTimeString(),
                'legal_hold_checked' => '1',
                'justification' => 'Records have exceeded the approved retention period.',
            ])
            ->assertRedirect(route('data-retention.index'));

        $request = DataPurgeRequest::query()->firstOrFail();
        $this->actingAs($superadmin)
            ->put(route('data-retention.purge-requests.review', $request->id), [
                'decision' => 'approved',
                'review_notes' => 'Legal hold check confirmed.',
            ])
            ->assertRedirect(route('data-retention.index'));

        $this->actingAs($superadmin)
            ->put(route('data-retention.purge-requests.execute', $request->id), [
                'evidence_reference' => 'storage-job-2026-08-12',
            ])
            ->assertRedirect(route('data-retention.index'));

        $this->assertDatabaseHas('data_purge_requests', [
            'id' => $request->id,
            'status' => 'executed',
            'requested_by' => $compliance->id,
            'reviewed_by' => $superadmin->id,
            'executed_by' => $superadmin->id,
            'legal_hold_checked' => 1,
            'evidence_reference' => 'storage-job-2026-08-12',
        ]);
    }

    public function test_requester_cannot_approve_own_purge_request(): void
    {
        $superadmin = $this->createUser('CO - COMPLIANCE', true);
        $schedule = DataRetentionSchedule::create([
            'record_type' => 'Test Records',
            'retention_months' => 12,
            'status' => 'active',
            'created_by' => $superadmin->id,
            'updated_by' => $superadmin->id,
        ]);
        $request = DataPurgeRequest::create([
            'data_retention_schedule_id' => $schedule->id,
            'scope_reference' => 'test records before 2025',
            'requested_at' => now(),
            'legal_hold_checked' => true,
            'justification' => 'Expired records.',
            'status' => 'requested',
            'requested_by' => $superadmin->id,
        ]);

        $this->actingAs($superadmin)
            ->put(route('data-retention.purge-requests.review', $request->id), ['decision' => 'approved'])
            ->assertStatus(422);

        $this->assertDatabaseHas('data_purge_requests', ['id' => $request->id, 'status' => 'requested']);
    }

    public function test_purge_request_requires_legal_hold_confirmation(): void
    {
        $compliance = $this->createUser('CO - COMPLIANCE');
        $schedule = DataRetentionSchedule::create([
            'record_type' => 'KRI Raw Inputs',
            'retention_months' => 24,
            'status' => 'active',
            'created_by' => $compliance->id,
            'updated_by' => $compliance->id,
        ]);

        $this->actingAs($compliance)
            ->post(route('data-retention.purge-requests.store'), [
                'data_retention_schedule_id' => $schedule->id,
                'scope_reference' => 'kri inputs before 2024',
                'justification' => 'Expired records.',
            ])
            ->assertSessionHasErrors('legal_hold_checked');

        $this->assertSame(0, DataPurgeRequest::query()->count());
    }

    private function createUser(string $division, bool $superadmin = false): User
    {
        $suffix = Str::lower(Str::random(10));

        return User::create([
            'name' => 'Retention User ' . $suffix,
            'username' => 'retention_' . $suffix,
            'division' => $division,
            'division_verified_at' => now(),
            'is_superadmin' => $superadmin,
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }
}
