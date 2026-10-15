<?php

namespace Tests\Feature;

use App\ImpactCategory;
use App\ProbabilityScale;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SuperadminParameterManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_manage_parameters_and_mitigation_playbooks(): void
    {
        $superadmin = $this->createUser('CO - COMPLIANCE', true);

        $this->actingAs($superadmin);

        $this->get(route('parameters.index'))
            ->assertOk()
            ->assertSee('App Management')
            ->assertSee('User Management')
            ->assertSee('Corporate Risk Appetite')
            ->assertSee($superadmin->username)
            ->assertSee('superadmin')
            ->assertSee('data-app-management-toggle', false)
            ->assertSee('parameter-tab-risk-category', false)
            ->assertSee('parameter-tab-group-radar-description', false)
            ->assertSee('Third-Party Vendor')
            ->assertSee('Mitigation Playbook');

        $this->post(route('parameters.risk-categories.store'), [
            'name' => 'Strategic Test Category',
            'tab' => 'risk-category',
        ])->assertRedirect(route('parameters.index', ['tab' => 'risk-category']));

        $this->post(route('parameters.risk-owners.store'), [
            'name' => 'ZZ - TEST OWNER',
            'sort_order' => 90,
            'is_active' => '1',
        ])->assertRedirect(route('parameters.index'));

        $this->post(route('parameters.cause-categories.store'), [
            'name' => 'External Test Cause',
        ])->assertRedirect(route('parameters.index'));

        ProbabilityScale::query()->where('score', 5)->delete();

        $this->post(route('parameters.probability-scales.store'), [
            'score' => 5,
            'description' => 'Nyaris pasti terjadi',
            'sort_order' => 5,
            'is_active' => '1',
        ])->assertRedirect(route('parameters.index'));

        $this->post(route('parameters.impact-categories.store'), [
            'name' => 'Reputational Test Impact',
        ])->assertRedirect(route('parameters.index'));

        $impactCategory = ImpactCategory::query()
            ->where('name', 'Reputational Test Impact')
            ->firstOrFail();

        $this->post(route('parameters.impact-scales.store'), [
            'impact_category_id' => $impactCategory->id,
            'description' => 'Publikasi negatif nasional',
            'score' => 4,
        ])->assertRedirect(route('parameters.index'));

        $this->post(route('parameters.radar-groups.store'), [
            'code' => 'RG/TST',
            'description' => 'Radar Test Group',
        ])->assertRedirect(route('parameters.index'));

        $this->post(route('treatment-templates.store'), [
            'name' => 'Playbook Superadmin',
            'risk_owner' => 'ZZ - TEST OWNER',
            'option' => 'Mitigation',
            'description' => 'Mitigation steps for the new owner.',
            'default_pic' => 'ZZ - TEST OWNER',
            'default_effectiveness' => 'High',
            'target_days' => 30,
            'next_action_offset_days' => 7,
            'checklist' => "Review control\nAttach evidence",
            'is_active' => '1',
        ])->assertRedirect(route('treatment-templates.index'));

        $this->assertDatabaseHas('risk_categories', [
            'name' => 'Strategic Test Category',
        ]);
        $this->assertDatabaseHas('risk_owner_parameters', [
            'name' => 'ZZ - TEST OWNER',
            'sort_order' => 90,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('risk_cause_categories', [
            'name' => 'External Test Cause',
        ]);
        $this->assertDatabaseHas('probability_scales', [
            'score' => 5,
            'description' => 'Nyaris pasti terjadi',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('impact_scales', [
            'impact_category_id' => $impactCategory->id,
            'description' => 'Publikasi negatif nasional',
            'score' => 4,
        ]);
        $this->assertDatabaseHas('radar_groups', [
            'code' => 'RG/TST',
            'description' => 'Radar Test Group',
        ]);
        $this->assertDatabaseHas('risk_treatment_templates', [
            'name' => 'Playbook Superadmin',
            'risk_owner' => 'ZZ - TEST OWNER',
            'created_by' => $superadmin->id,
        ]);
    }

    public function test_risk_create_form_uses_managed_risk_owner_and_probability_descriptions(): void
    {
        $superadmin = $this->createUser('CO - COMPLIANCE', true);
        $complianceUser = $this->createUser('CO - COMPLIANCE', false);

        $this->actingAs($superadmin);

        $this->post(route('parameters.risk-owners.store'), [
            'name' => 'ZZ - FORM OWNER',
            'sort_order' => 95,
            'is_active' => '1',
        ])->assertRedirect(route('parameters.index'));

        $probabilityScale = ProbabilityScale::query()
            ->where('score', 3)
            ->firstOrFail();

        $this->put(route('parameters.probability-scales.update', $probabilityScale->id), [
            'description' => 'Probabilitas uji superadmin',
            'sort_order' => 3,
            'is_active' => '1',
        ])->assertRedirect(route('parameters.index'));

        $this->actingAs($complianceUser);

        $this->get(route('risks.create'))
            ->assertOk()
            ->assertSee($complianceUser->username)
            ->assertSee('Compliance')
            ->assertSee('risk-menu-columns', false)
            ->assertSee('ZZ - FORM OWNER')
            ->assertSee('3. Probabilitas uji superadmin');
    }

    public function test_only_superadmin_and_compliance_can_open_parameter_management_page(): void
    {
        // Compliance owns the application and manages risk parameters too.
        $this->actingAs($this->createUser('CO - COMPLIANCE', false));
        $this->get(route('parameters.index'))->assertOk();

        $this->actingAs($this->createUser('BT - DIGITAL BANKING', false));
        $this->get(route('parameters.index'))->assertForbidden();
    }

    public function test_parameter_page_respects_requested_active_tab(): void
    {
        $superadmin = $this->createUser('CO - COMPLIANCE', true);

        $this->actingAs($superadmin);

        $this->get(route('parameters.index', ['tab' => 'risk-owner']))
            ->assertOk()
            ->assertSee('data-parameter-active-tab="risk-owner"', false)
            ->assertSee('id="parameter-tab-risk-owner"', false);
    }

    private function createUser(string $division, bool $isSuperadmin): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Parameter User ' . $suffix,
            'username' => 'parameter_' . $suffix,
            'division' => $division,
            'pending_division' => null,
            'division_verified_at' => now(),
            'is_superadmin' => $isSuperadmin,
            'force_password_change' => false,
            'email' => 'parameter_' . $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }
}
