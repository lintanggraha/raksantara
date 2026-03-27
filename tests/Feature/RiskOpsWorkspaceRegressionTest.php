<?php

namespace Tests\Feature;

use App\Risk;
use App\RiskCategory;
use App\RiskTreatment;
use App\RiskTreatmentTemplate;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class RiskOpsWorkspaceRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_workload_dashboard_and_playbooks_are_scoped_to_visible_owner(): void
    {
        $viewer = $this->createUser('BT - DIGITAL BANKING', false);
        $foreignUser = $this->createUser('CO - COMPLIANCE', false);

        $ownRisk = $this->createRiskRecord($viewer, 'BT - DIGITAL BANKING', 'Scoped Workload Risk');
        $foreignRisk = $this->createRiskRecord($foreignUser, 'CO - COMPLIANCE', 'Hidden Workload Risk');

        $this->createTreatment($ownRisk, [
            'description' => 'Own overdue treatment',
            'status' => 'Blocked',
            'deadline' => now()->subDays(1)->toDateString(),
            'next_action_at' => now()->addDays(2)->toDateString(),
            'blocked_reason' => 'Waiting for approval',
        ]);
        $this->createTreatment($foreignRisk, [
            'description' => 'Foreign treatment',
            'status' => 'Blocked',
            'deadline' => now()->subDays(2)->toDateString(),
            'next_action_at' => now()->addDays(1)->toDateString(),
            'blocked_reason' => 'Hidden issue',
        ]);

        $ownTemplate = $this->createTemplate($viewer, 'BT - DIGITAL BANKING', 'Own Playbook');
        $this->createTemplate($foreignUser, 'CO - COMPLIANCE', 'Foreign Playbook');

        $this->actingAs($viewer);

        $this->get(route('owners.workload'))
            ->assertOk()
            ->assertSee('DIGITAL BANKING')
            ->assertDontSee('COMPLIANCE');

        $this->get(route('treatment-templates.index'))
            ->assertOk()
            ->assertSee($ownTemplate->name)
            ->assertDontSee('Foreign Playbook');
    }

    public function test_rcsa_workspace_can_be_saved_for_owned_risk_and_foreign_risk_stays_hidden(): void
    {
        $viewer = $this->createUser('BT - DIGITAL BANKING', false);
        $foreignUser = $this->createUser('CO - COMPLIANCE', false);
        $ownRisk = $this->createRiskRecord($viewer, 'BT - DIGITAL BANKING', 'Owned RCSA Risk');
        $foreignRisk = $this->createRiskRecord($foreignUser, 'CO - COMPLIANCE', 'Foreign RCSA Risk');

        $this->actingAs($viewer);

        $this->put(route('risks.rcsa.update', $ownRisk->id), [
            'process_owner' => 'Digital Banking Ops',
            'objective' => 'Validate control design and operating effectiveness.',
            'scope' => 'Retail digital onboarding process.',
            'key_controls' => 'Dual approval and reconciliation.',
            'control_gaps' => 'Manual follow-up still exists.',
            'likelihood_rationale' => 'Incidents are occasional but recurring.',
            'impact_rationale' => 'Customer disruption and recovery effort.',
            'review_frequency' => 'Quarterly',
            'next_review_at' => now()->addMonth()->toDateString(),
            'sign_off_name' => 'Risk Owner',
            'sign_off_role' => 'Head of Digital Banking',
            'signed_off_at' => now()->toDateString(),
            'notes' => 'Capture control remediation status here.',
        ])->assertRedirect(route('risks.rcsa.show', $ownRisk->id));

        $this->assertDatabaseHas('risk_rcsa_workspaces', [
            'risk_id' => $ownRisk->id,
            'process_owner' => 'Digital Banking Ops',
            'review_frequency' => 'Quarterly',
            'updated_by' => $viewer->id,
        ]);

        $this->get(route('risks.rcsa.show', $ownRisk->id))
            ->assertOk()
            ->assertSee('Digital Banking Ops')
            ->assertSee('Quarterly');

        $this->get(route('risks.rcsa.show', $foreignRisk->id))->assertNotFound();
    }

    private function createRiskRecord(User $creator, string $riskOwner, string $title): Risk
    {
        return Risk::create([
            'risk_id' => 'OPS-' . Str::upper(Str::random(8)),
            'year' => (int) now()->year,
            'risk_title' => $title,
            'risk_category_id' => $this->ensureRiskCategoryId(),
            'risk_owner' => $riskOwner,
            'status' => 'Open',
            'created_by' => $creator->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createTreatment(Risk $risk, array $overrides = []): RiskTreatment
    {
        return RiskTreatment::create(array_merge([
            'risk_id' => $risk->id,
            'option' => 'Mitigation',
            'description' => 'Default workload treatment',
            'pic' => $risk->risk_owner,
            'status' => 'Not Started',
            'status_updated_at' => now()->subDays(3),
            'deadline' => now()->addDays(7)->toDateString(),
            'effectiveness' => 'Medium',
            'effectiveness_score' => 0.5,
        ], $overrides));
    }

    private function createTemplate(User $creator, string $riskOwner, string $name): RiskTreatmentTemplate
    {
        return RiskTreatmentTemplate::create([
            'name' => $name,
            'risk_owner' => $riskOwner,
            'option' => 'Mitigation',
            'description' => 'Template action for ' . $name,
            'default_pic' => $riskOwner,
            'default_effectiveness' => 'Medium',
            'target_days' => 30,
            'next_action_offset_days' => 7,
            'checklist' => "Collect evidence\nConfirm approval",
            'is_active' => true,
            'created_by' => $creator->id,
        ]);
    }

    private function ensureRiskCategoryId(): int
    {
        $existingId = RiskCategory::query()->value('id');
        if (!is_null($existingId)) {
            return (int) $existingId;
        }

        return (int) RiskCategory::query()->insertGetId([
            'name' => 'Ops Regression Category ' . Str::random(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createUser(string $division, bool $isSuperadmin): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Risk Ops User ' . $suffix,
            'username' => 'risk_ops_' . $suffix,
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
