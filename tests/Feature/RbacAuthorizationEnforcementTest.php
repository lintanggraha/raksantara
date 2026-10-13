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

    public function test_auditor_with_view_only_assignment_cannot_write_governance_records(): void
    {
        $auditor = $this->createUser('auditor');
        $this->assignRole($auditor, 'auditor');
        $creator = $this->createUser('risk-creator');
        $risk = $this->createRisk($creator);

        $auditor = $auditor->fresh();
        $this->assertTrue($auditor->hasPermission('risks.view'));
        $this->assertFalse($auditor->hasPermission('controls.manage'));
        $this->assertTrue(RiskAccess::canView($auditor, $risk));
        $this->assertFalse(RiskAccess::canManage($auditor, $risk, 'controls.manage'));

        $this->actingAs($auditor)
            ->get(route('risks.governance.show', $risk->id))
            ->assertOk()
            ->assertViewHas('canManageControls', false)
            ->assertViewHas('canRequestAcceptance', false)
            ->assertViewHas('canDecide', false);

        $this->post(route('risks.governance.controls.store', $risk->id), $this->controlPayload())
            ->assertForbidden();
        $this->post(route('risks.governance.acceptances.store', $risk->id), $this->acceptancePayload())
            ->assertForbidden();

        $this->assertDatabaseMissing('risk_controls', ['risk_id' => $risk->id]);
        $this->assertDatabaseMissing('risk_acceptances', ['risk_id' => $risk->id]);
    }

    public function test_risk_manager_assignment_grants_control_and_acceptance_request_permissions_only(): void
    {
        $manager = $this->createUser('risk-manager');
        $this->assignRole($manager, 'risk-manager');
        $creator = $this->createUser('risk-creator');
        $risk = $this->createRisk($creator);

        $manager = $manager->fresh();
        $this->assertTrue($manager->hasPermission('controls.manage'));
        $this->assertTrue($manager->hasPermission('risk-acceptance.request'));
        $this->assertFalse($manager->hasPermission('risks.manage'));
        $this->assertFalse(RiskAccess::canManage($manager, $risk));
        $this->assertTrue(RiskAccess::canManage($manager, $risk, 'controls.manage'));

        $this->actingAs($manager)
            ->get(route('risks.governance.show', $risk->id))
            ->assertOk()
            ->assertViewHas('canManageControls', true)
            ->assertViewHas('canRequestAcceptance', true)
            ->assertViewHas('canDecide', false);

        $this->post(route('risks.governance.controls.store', $risk->id), $this->controlPayload())
            ->assertRedirect(route('risks.governance.show', $risk->id));
        $controlId = (int) $risk->controls()->value('id');
        $this->assertGreaterThan(0, $controlId);

        $this->post(route('risks.governance.controls.tests.store', [$risk->id, $controlId]), [
            'tested_at' => now()->toDateString(),
            'result' => 'effective',
        ])->assertRedirect(route('risks.governance.show', $risk->id));

        $this->post(route('risks.governance.acceptances.store', $risk->id), $this->acceptancePayload())
            ->assertRedirect(route('risks.governance.show', $risk->id));

        $this->assertDatabaseHas('risk_control_tests', [
            'risk_control_id' => $controlId,
            'result' => 'effective',
        ]);
        $this->assertDatabaseHas('risk_acceptances', [
            'risk_id' => $risk->id,
            'status' => 'requested',
        ]);
    }

    public function test_system_administrator_role_can_decide_acceptance_but_cannot_manage_controls(): void
    {
        $owner = $this->createUser('legacy-owner');
        $risk = $this->createRisk($owner);
        $systemAdministrator = $this->createUser('system-administrator');
        $this->assignRole($systemAdministrator, 'system-administrator');

        $this->actingAs($owner)
            ->post(route('risks.governance.acceptances.store', $risk->id), $this->acceptancePayload())
            ->assertRedirect(route('risks.governance.show', $risk->id));
        $acceptanceId = (int) $risk->acceptances()->value('id');
        $this->assertGreaterThan(0, $acceptanceId);

        $systemAdministrator = $systemAdministrator->fresh();
        $this->assertTrue($systemAdministrator->hasPermission('risk-acceptance.decide'));
        $this->assertFalse($systemAdministrator->hasPermission('controls.manage'));

        $this->actingAs($systemAdministrator)
            ->get(route('risks.governance.show', $risk->id))
            ->assertOk()
            ->assertViewHas('canDecide', true)
            ->assertViewHas('canManageControls', false)
            ->assertViewHas('canRequestAcceptance', false);

        $this->put(route('risks.governance.acceptances.decision', [$risk->id, $acceptanceId]), [
            'decision' => 'approved',
            'decision_notes' => 'Approved by the explicitly assigned decision authority.',
        ])->assertRedirect(route('risks.governance.show', $risk->id));

        $this->post(route('risks.governance.controls.store', $risk->id), $this->controlPayload())
            ->assertForbidden();

        $this->assertDatabaseHas('risk_acceptances', [
            'id' => $acceptanceId,
            'status' => 'approved',
            'reviewed_by' => $systemAdministrator->id,
        ]);
        $this->assertDatabaseMissing('risk_controls', ['risk_id' => $risk->id]);
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

        $this->actingAs($auditor)
            ->get(route('risks.governance.show', $risk->id))
            ->assertNotFound();
        $this->post(route('risks.governance.controls.store', $risk->id), $this->controlPayload())
            ->assertNotFound();

        $this->assertDatabaseMissing('risk_controls', ['risk_id' => $risk->id]);
    }

    public function test_unassigned_user_keeps_legacy_division_scoped_governance_access(): void
    {
        $creator = $this->createUser('risk-creator');
        $legacyPeer = $this->createUser('legacy-peer');
        $risk = $this->createRisk($creator);

        $this->assertFalse($legacyPeer->hasRoleAssignments());
        $this->assertTrue(RiskAccess::canView($legacyPeer, $risk));
        $this->assertTrue(RiskAccess::canManage($legacyPeer, $risk));

        $this->actingAs($legacyPeer)
            ->post(route('risks.governance.controls.store', $risk->id), $this->controlPayload())
            ->assertRedirect(route('risks.governance.show', $risk->id));

        $this->assertDatabaseHas('risk_controls', [
            'risk_id' => $risk->id,
            'control_code' => 'RBAC-TEST-01',
        ]);
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

    private function controlPayload(): array
    {
        return [
            'control_code' => 'RBAC-TEST-01',
            'name' => 'RBAC Authorization Test Control',
            'description' => 'Synthetic control used only in the isolated test database.',
            'owner_name' => 'Test Control Owner',
            'review_frequency' => 'Quarterly',
            'status' => 'active',
            'next_test_at' => now()->addDays(30)->toDateString(),
        ];
    }

    private function acceptancePayload(): array
    {
        return [
            'rationale' => 'Synthetic acceptance request used only in the isolated test database.',
            'compensating_controls' => 'Review the test record during this isolated run.',
            'accepted_until' => now()->addDays(30)->toDateString(),
        ];
    }
}
