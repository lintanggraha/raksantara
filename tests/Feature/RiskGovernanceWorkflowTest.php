<?php

namespace Tests\Feature;

use App\Risk;
use App\RiskCategory;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class RiskGovernanceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_risk_owner_can_register_and_test_a_control(): void
    {
        $owner = $this->createUser('BT - DIGITAL BANKING', false);
        $risk = $this->createRisk($owner, 'BT - DIGITAL BANKING');

        $this->actingAs($owner)
            ->post(route('risks.governance.controls.store', $risk->id), [
                'control_code' => 'CTRL-001',
                'name' => 'Daily transaction reconciliation',
                'description' => 'Reconcile source and settlement transactions each business day.',
                'owner_name' => 'Digital Banking Operations',
                'review_frequency' => 'Quarterly',
                'status' => 'active',
                'next_test_at' => now()->addDays(30)->toDateString(),
            ])
            ->assertRedirect(route('risks.governance.show', $risk->id));

        $this->assertDatabaseHas('risk_controls', [
            'risk_id' => $risk->id,
            'control_code' => 'CTRL-001',
            'status' => 'active',
        ]);

        $controlId = (int) $risk->controls()->value('id');

        $this->actingAs($owner)
            ->post(route('risks.governance.controls.tests.store', [$risk->id, $controlId]), [
                'tested_at' => now()->toDateString(),
                'result' => 'effective',
                'findings' => 'No exception identified.',
                'next_test_at' => now()->addDays(90)->toDateString(),
            ])
            ->assertRedirect(route('risks.governance.show', $risk->id));

        $this->assertDatabaseHas('risk_control_tests', [
            'risk_control_id' => $controlId,
            'result' => 'effective',
            'remediation_status' => 'not_required',
        ]);
        $this->assertDatabaseHas('risk_controls', [
            'id' => $controlId,
            'design_effectiveness' => 'effective',
        ]);
    }

    public function test_risk_acceptance_requires_superadmin_decision(): void
    {
        $owner = $this->createUser('BT - DIGITAL BANKING', false);
        $superadmin = $this->createUser('CO - COMPLIANCE', true);
        $risk = $this->createRisk($owner, 'BT - DIGITAL BANKING');

        $this->actingAs($owner)
            ->post(route('risks.governance.acceptances.store', $risk->id), [
                'rationale' => 'Residual risk is accepted temporarily while a replacement control is implemented.',
                'compensating_controls' => 'Daily exception review by the process owner.',
                'accepted_until' => now()->addDays(45)->toDateString(),
            ])
            ->assertRedirect(route('risks.governance.show', $risk->id));

        $acceptanceId = (int) $risk->acceptances()->value('id');

        $this->actingAs($owner)
            ->put(route('risks.governance.acceptances.decision', [$risk->id, $acceptanceId]), [
                'decision' => 'approved',
            ])
            ->assertForbidden();

        $this->actingAs($superadmin)
            ->put(route('risks.governance.acceptances.decision', [$risk->id, $acceptanceId]), [
                'decision' => 'approved',
                'decision_notes' => 'Approved until the control implementation checkpoint.',
            ])
            ->assertRedirect(route('risks.governance.show', $risk->id));

        $this->assertDatabaseHas('risk_acceptances', [
            'id' => $acceptanceId,
            'status' => 'approved',
            'reviewed_by' => $superadmin->id,
        ]);
    }

    private function createRisk(User $creator, string $owner): Risk
    {
        return Risk::create([
            'risk_id' => 'GOV-' . Str::upper(Str::random(8)),
            'year' => (int) now()->year,
            'risk_title' => 'Governance workflow risk',
            'risk_category_id' => $this->categoryId(),
            'risk_owner' => $owner,
            'status' => 'Open',
            'created_by' => $creator->id,
        ]);
    }

    private function categoryId(): int
    {
        return (int) RiskCategory::query()->firstOrCreate([
            'name' => 'Governance Test Category',
        ])->id;
    }

    private function createUser(string $division, bool $isSuperadmin): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Governance User ' . $suffix,
            'username' => 'governance_' . $suffix,
            'division' => $division,
            'division_verified_at' => now(),
            'is_superadmin' => $isSuperadmin,
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }
}
