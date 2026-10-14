<?php

namespace Tests\Feature;

use App\Risk;
use App\RiskCategory;
use App\Policies\RiskPolicy;
use App\Role;
use App\RoleAssignment;
use App\Support\RiskAccess;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class RbacAuthorizationEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_risk_creation_policy_requires_risks_manage_for_explicit_roles(): void
    {
        $auditor = $this->createUser('policy-auditor');
        $this->assignRole($auditor, 'auditor');
        $riskOwner = $this->createUser('policy-risk-owner');
        $this->assignRole($riskOwner, 'risk-owner');
        $legacyUser = $this->createUser('policy-legacy');

        $policy = app(RiskPolicy::class);
        $this->assertFalse($policy->create($auditor->fresh()));
        $this->assertTrue($policy->create($riskOwner->fresh()));
        $this->assertTrue($policy->create($legacyUser));
    }

    public function test_auditor_with_view_only_assignment_can_view_but_not_edit_risks(): void
    {
        $auditor = $this->createUser('auditor');
        $this->assignRole($auditor, 'auditor');
        $creator = $this->createUser('risk-creator');
        $risk = $this->createRisk($creator);

        $auditor = $auditor->fresh();
        $this->assertTrue($auditor->hasPermission('risks.view'));
        $this->assertFalse($auditor->hasPermission('risks.manage'));
        $this->assertTrue(RiskAccess::canView($auditor, $risk));
        $this->assertFalse(RiskAccess::canManage($auditor, $risk));

        $this->actingAs($auditor)->get(route('risks.show', $risk->id))->assertOk();
        $this->assertContains($this->get(route('risks.edit', $risk->id))->getStatusCode(), [403, 404]);
    }

    public function test_risk_manager_assignment_views_without_managing_foreign_risks(): void
    {
        $manager = $this->createUser('risk-manager');
        $this->assignRole($manager, 'risk-manager');
        $creator = $this->createUser('risk-creator');
        $risk = $this->createRisk($creator);

        $manager = $manager->fresh();
        $this->assertTrue($manager->hasPermission('risks.view'));
        $this->assertFalse($manager->hasPermission('risks.manage'));
        $this->assertTrue(RiskAccess::canView($manager, $risk));
        $this->assertFalse(RiskAccess::canManage($manager, $risk));
    }

    public function test_expired_assignment_does_not_fall_back_to_legacy_division_access(): void
    {
        $auditor = $this->createUser('expired-auditor');
        $this->assignRole($auditor, 'auditor', now()->subDay());
        $creator = $this->createUser('risk-creator');
        $risk = $this->createRisk($creator);

        $auditor = $auditor->fresh();
        $this->assertTrue($auditor->hasRoleAssignments());
        $this->assertFalse($auditor->hasPermission('risks.view'));
        $this->assertFalse(RiskAccess::canView($auditor, $risk));
        $this->assertFalse(RiskAccess::canManage($auditor, $risk));

        $this->actingAs($auditor);
        $this->assertContains($this->get(route('risks.show', $risk->id))->getStatusCode(), [403, 404]);
    }

    public function test_unassigned_user_keeps_legacy_division_scoped_access(): void
    {
        $creator = $this->createUser('risk-creator');
        $legacyPeer = $this->createUser('legacy-peer');
        $risk = $this->createRisk($creator);

        $this->assertFalse($legacyPeer->hasRoleAssignments());
        $this->assertTrue(RiskAccess::canView($legacyPeer, $risk));
        $this->assertTrue(RiskAccess::canManage($legacyPeer, $risk));

        $this->actingAs($legacyPeer)->get(route('risks.show', $risk->id))->assertOk();
    }

    private function createUser(string $tag): User
    {
        $suffix = Str::lower(Str::random(10));

        return User::create([
            'name' => 'RBAC ' . $tag . ' ' . $suffix,
            'username' => 'rbac_' . $tag . '_' . $suffix,
            'division' => 'BT - DIGITAL BANKING',
            'division_verified_at' => now(),
            'is_superadmin' => false,
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('test-password'),
        ]);
    }

    private function assignRole(User $user, string $roleSlug, $expiresAt = null): void
    {
        $role = Role::query()->where('slug', $roleSlug)->firstOrFail();

        RoleAssignment::create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'organizational_unit_id' => null,
            'assigned_by' => null,
            'expires_at' => $expiresAt,
        ]);
    }

    private function createRisk(User $creator): Risk
    {
        $category = RiskCategory::query()->firstOrCreate(['name' => 'RBAC Authorization Test Category']);

        return Risk::create([
            'risk_id' => 'RBAC-' . Str::upper(Str::random(8)),
            'year' => (int) now()->year,
            'risk_title' => 'RBAC authorization isolated test risk',
            'risk_category_id' => $category->id,
            'risk_owner' => 'BT - DIGITAL BANKING',
            'status' => 'Open',
            'created_by' => $creator->id,
        ]);
    }
}
