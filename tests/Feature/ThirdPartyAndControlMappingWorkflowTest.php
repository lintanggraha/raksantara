<?php

namespace Tests\Feature;

use App\Risk;
use App\RiskCategory;
use App\RiskControl;
use App\User;
use App\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ThirdPartyAndControlMappingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_compliance_can_complete_vendor_assessment_lifecycle(): void
    {
        $compliance = $this->createUser('CO - COMPLIANCE');
        $vendor = Vendor::create([
            'name' => 'Critical Cloud Provider',
            'status' => 'Active',
            'criticality_level' => 'High',
        ]);

        $this->actingAs($compliance)
            ->post(route('vendors.third-party-risk.assessments.store', $vendor->id), [
                'assessment_type' => 'Due Diligence',
                'assessed_at' => now()->toDateString(),
                'inherent_rating' => 5,
                'residual_rating' => 3,
                'decision' => 'Conditional',
                'valid_until' => now()->addYear()->toDateString(),
                'findings' => 'Independent penetration test evidence is pending.',
                'risk_treatment' => 'Track evidence as a contractual remediation action.',
                'status' => 'remediating',
            ])
            ->assertRedirect(route('vendors.third-party-risk.show', $vendor->id));

        $this->assertDatabaseHas('vendor_risk_assessments', [
            'vendor_id' => $vendor->id,
            'decision' => 'Conditional',
            'residual_rating' => 3,
            'status' => 'remediating',
        ]);
        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id,
            'risk_rating' => 'Medium',
            'onboarding_status' => 'conditional',
        ]);
    }

    public function test_compliance_can_map_control_to_framework_and_non_compliance_is_denied(): void
    {
        $compliance = $this->createUser('CO - COMPLIANCE');
        $owner = $this->createUser('BT - DIGITAL BANKING');
        $risk = $this->createRisk($compliance, 'CO - COMPLIANCE');
        $control = RiskControl::create([
            'risk_id' => $risk->id,
            'control_code' => 'CTRL-' . Str::upper(Str::random(5)),
            'name' => 'Privileged Access Review',
            'owner_name' => 'Information Security',
            'created_by' => $compliance->id,
        ]);

        $this->actingAs($compliance)
            ->post(route('control-framework-mappings.store'), [
                'risk_control_id' => $control->id,
                'framework_code' => 'ISO 27001',
                'control_reference' => 'A.5.18',
                'requirement_title' => 'Access rights review',
                'mapping_status' => 'mapped',
                'evidence_notes' => 'Quarterly access review report.',
            ])
            ->assertRedirect(route('control-framework-mappings.index'));

        $this->assertDatabaseHas('risk_control_framework_mappings', [
            'risk_control_id' => $control->id,
            'framework_code' => 'ISO 27001',
            'control_reference' => 'A.5.18',
            'mapping_status' => 'mapped',
        ]);

        $this->actingAs($owner)
            ->post(route('control-framework-mappings.store'), [
                'risk_control_id' => $control->id,
                'framework_code' => 'ISO 27001',
                'control_reference' => 'A.5.18',
                'mapping_status' => 'gap',
            ])
            ->assertRedirect(route('risks.index'));
    }

    private function createUser(string $division): User
    {
        $suffix = Str::lower(Str::random(10));

        return User::create([
            'name' => 'Third Party User ' . $suffix,
            'username' => 'third_party_' . $suffix,
            'division' => $division,
            'division_verified_at' => now(),
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }

    private function createRisk(User $user, string $owner): Risk
    {
        $category = RiskCategory::query()->firstOrCreate(['name' => 'Third Party Category']);

        return Risk::create([
            'risk_id' => 'TPR-' . Str::upper(Str::random(8)),
            'year' => now()->year,
            'risk_title' => 'Third Party Platform Risk',
            'risk_category_id' => $category->id,
            'risk_owner' => $owner,
            'status' => 'Open',
            'created_by' => $user->id,
        ]);
    }
}
