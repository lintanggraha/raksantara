<?php

namespace Tests\Feature;

use App\User;
use App\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ThirdPartyRiskWorkflowTest extends TestCase
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
}
