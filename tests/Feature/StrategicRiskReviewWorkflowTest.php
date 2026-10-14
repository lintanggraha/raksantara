<?php

namespace Tests\Feature;

use App\Risk;
use App\RiskCategory;
use App\RiskScenario;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class StrategicRiskReviewWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_compliance_can_create_scenario_map_risk_impact_and_save_maturity_review(): void
    {
        $compliance = $this->createUser('CO - COMPLIANCE');
        $risk = $this->createRisk($compliance, 'CO - COMPLIANCE');

        $this->actingAs($compliance)
            ->post(route('strategic-risk-reviews.scenarios.store'), [
                'title' => 'Cloud concentration disruption',
                'scenario_type' => 'Third Party',
                'status' => 'approved',
                'time_horizon' => '12 months',
                'likelihood' => 4,
                'impact' => 5,
                'trigger_event' => 'Primary cloud region becomes unavailable.',
                'narrative' => 'A multi-day outage interrupts critical customer journeys and back-office operations.',
                'response_strategy' => 'Activate regional failover and crisis response plan.',
                'owner_name' => 'Technology Risk',
            ])
            ->assertRedirect(route('strategic-risk-reviews.index'));

        $scenario = RiskScenario::query()->firstOrFail();

        $this->actingAs($compliance)
            ->post(route('strategic-risk-reviews.scenarios.impacts.store', $scenario->id), [
                'risk_id' => $risk->id,
                'scenario_probability' => 4,
                'scenario_impact' => 5,
                'assumptions' => 'Failover completion requires 24 hours.',
                'mitigation_response' => 'Test multi-region failover quarterly.',
            ])
            ->assertRedirect(route('strategic-risk-reviews.index'));

        $this->actingAs($compliance)
            ->post(route('strategic-risk-reviews.maturity.store'), [
                'assessment_period' => '2026',
                'domain' => 'Scenario Analysis',
                'current_score' => 2,
                'target_score' => 4,
                'evidence' => 'Annual tabletop exercise exists.',
                'improvement_plan' => 'Introduce quarterly scenario refresh and stress testing.',
                'owner_name' => 'Enterprise Risk',
                'target_date' => now()->addYear()->toDateString(),
                'status' => 'in_progress',
            ])
            ->assertRedirect(route('strategic-risk-reviews.index'));

        $this->assertDatabaseHas('risk_scenario_impacts', [
            'risk_scenario_id' => $scenario->id,
            'risk_id' => $risk->id,
            'scenario_score' => 20,
        ]);
        $this->assertDatabaseHas('risk_maturity_assessments', [
            'assessment_period' => '2026',
            'domain' => 'Scenario Analysis',
            'current_score' => 2,
            'target_score' => 4,
            'status' => 'in_progress',
        ]);
    }

    public function test_non_compliance_cannot_create_strategic_scenario(): void
    {
        $owner = $this->createUser('BT - DIGITAL BANKING');

        $this->actingAs($owner)
            ->post(route('strategic-risk-reviews.scenarios.store'), [
                'title' => 'Unauthorized scenario',
                'scenario_type' => 'Operational',
                'status' => 'draft',
                'narrative' => 'This scenario must not be created.',
            ])
            ->assertRedirect(route('risks.index'));

        $this->assertSame(0, RiskScenario::query()->count());
    }

    private function createUser(string $division): User
    {
        $suffix = Str::lower(Str::random(10));

        return User::create([
            'name' => 'Strategic Review User ' . $suffix,
            'username' => 'strategic_review_' . $suffix,
            'division' => $division,
            'division_verified_at' => now(),
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }

    private function createRisk(User $user, string $owner): Risk
    {
        $category = RiskCategory::query()->firstOrCreate(['name' => 'Strategic Review Category']);

        return Risk::create([
            'risk_id' => 'SRR-' . Str::upper(Str::random(8)),
            'year' => now()->year,
            'risk_title' => 'Strategic Review Risk',
            'risk_category_id' => $category->id,
            'risk_owner' => $owner,
            'status' => 'Open',
            'created_by' => $user->id,
        ]);
    }
}
