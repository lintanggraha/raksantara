<?php

namespace Tests\Feature;

use App\Risk;
use App\RiskCategory;
use App\RiskCommitteeDecision;
use App\RiskResidualAnalysis;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class EnterpriseRiskProfileWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_compliance_can_register_committee_decision_and_view_top_risk_profile(): void
    {
        $compliance = $this->createUser('CO - COMPLIANCE');
        $risk = $this->createRisk($compliance, 'CO - COMPLIANCE');
        RiskResidualAnalysis::create([
            'risk_id' => $risk->id,
            'residual_probability' => 4,
            'residual_impact' => 5,
            'residual_risk_score' => 20,
        ]);

        $this->actingAs($compliance)
            ->get(route('enterprise-risk-profile.index'))
            ->assertOk()
            ->assertSee($risk->risk_id);

        $this->actingAs($compliance)
            ->post(route('enterprise-risk-profile.decisions.store'), [
                'meeting_date' => now()->toDateString(),
                'meeting_reference' => 'ERC-2026-08',
                'risk_id' => $risk->id,
                'title' => 'Accelerate capacity remediation',
                'decision' => 'Approve the remediation plan and require a monthly progress update.',
                'owner_name' => 'Digital Banking Operations',
                'due_at' => now()->addDays(30)->toDateString(),
                'status' => 'open',
            ])
            ->assertRedirect(route('enterprise-risk-profile.index'));

        $this->assertDatabaseHas('risk_committee_decisions', [
            'risk_id' => $risk->id,
            'meeting_reference' => 'ERC-2026-08',
            'status' => 'open',
            'created_by' => $compliance->id,
        ]);
    }

    public function test_non_compliance_cannot_register_committee_decision(): void
    {
        $owner = $this->createUser('BT - DIGITAL BANKING');
        $risk = $this->createRisk($owner, 'BT - DIGITAL BANKING');

        $this->actingAs($owner)
            ->post(route('enterprise-risk-profile.decisions.store'), [
                'meeting_date' => now()->toDateString(),
                'risk_id' => $risk->id,
                'title' => 'Unauthorized decision',
                'decision' => 'This must not be stored.',
                'owner_name' => 'Owner',
                'status' => 'open',
            ])
            ->assertRedirect(route('risks.index'));

        $this->assertSame(0, RiskCommitteeDecision::query()->count());
    }

    private function createUser(string $division): User
    {
        $suffix = Str::lower(Str::random(10));

        return User::create([
            'name' => 'Enterprise User ' . $suffix,
            'username' => 'enterprise_' . $suffix,
            'division' => $division,
            'division_verified_at' => now(),
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }

    private function createRisk(User $user, string $owner): Risk
    {
        $category = RiskCategory::query()->firstOrCreate(['name' => 'Enterprise Profile Category']);

        return Risk::create([
            'risk_id' => 'ERP-' . Str::upper(Str::random(8)),
            'year' => now()->year,
            'risk_title' => 'Enterprise Profile Risk',
            'risk_category_id' => $category->id,
            'risk_owner' => $owner,
            'status' => 'Open',
            'created_by' => $user->id,
        ]);
    }
}
