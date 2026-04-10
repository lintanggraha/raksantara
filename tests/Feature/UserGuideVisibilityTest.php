<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserGuideVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_only_sees_active_superadmin_guide_tabs(): void
    {
        $superadmin = $this->createUser('CO - COMPLIANCE', true);

        $this->actingAs($superadmin);

        $this->get(route('howto'))
            ->assertOk()
            ->assertSee('App Management')
            ->assertSee('Threat Intel')
            ->assertSee('id="guide-superadmin-tab"', false)
            ->assertSee('id="guide-intel-tab"', false)
            ->assertSee('Reset Password')
            ->assertSee('Threshold Metric')
            ->assertSee('Review note (required)')
            ->assertSee('Resolve')
            ->assertDontSee('id="guide-risk-tab"', false)
            ->assertDontSee('id="guide-compliance-tab"', false)
            ->assertDontSee('Scheduled Reports');
    }

    public function test_compliance_user_only_sees_active_operational_and_compliance_guide_tabs(): void
    {
        $complianceUser = $this->createUser('CO - COMPLIANCE', false);

        $this->actingAs($complianceUser);

        $this->get(route('howto'))
            ->assertOk()
            ->assertSee('id="guide-risk-tab"', false)
            ->assertSee('id="guide-intel-tab"', false)
            ->assertSee('id="guide-compliance-tab"', false)
            ->assertSee('Third-Party Vendors')
            ->assertSee('Risk Category')
            ->assertSee('Mitigation Playbook (Optional)')
            ->assertSee('Save KRI')
            ->assertSee('Add Vendor')
            ->assertSee('Threat Intelligence')
            ->assertDontSee('id="guide-superadmin-tab"', false)
            ->assertDontSee('Scheduled Reports');
    }

    private function createUser(string $division, bool $isSuperadmin): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Guide User ' . $suffix,
            'username' => 'guide_' . $suffix,
            'division' => $division,
            'pending_division' => null,
            'division_verified_at' => now(),
            'is_superadmin' => $isSuperadmin,
            'force_password_change' => false,
            'email' => 'guide_' . $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }
}
