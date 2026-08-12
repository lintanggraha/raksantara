<?php

namespace Tests\Feature;

use App\Role;
use App\RoleAssignment;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccessControlWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_role_grants_permission_and_expired_role_does_not(): void
    {
        $user = $this->createUser(false);
        $riskOwner = Role::query()->where('slug', 'risk-owner')->firstOrFail();

        RoleAssignment::create([
            'user_id' => $user->id,
            'role_id' => $riskOwner->id,
            'expires_at' => now()->addDay(),
        ]);

        $this->assertTrue($user->fresh()->hasPermission('controls.manage'));
        $this->assertTrue($user->fresh()->hasRole('risk-owner'));

        $user->roleAssignments()->update(['expires_at' => now()->subDay()]);

        $this->assertFalse($user->fresh()->hasPermission('controls.manage'));
        $this->assertFalse($user->fresh()->hasRole('risk-owner'));
    }

    public function test_only_superadmin_can_manage_role_assignments(): void
    {
        $superadmin = $this->createUser(true);
        $ordinaryUser = $this->createUser(false);
        $targetUser = $this->createUser(false);
        $role = Role::query()->where('slug', 'auditor')->firstOrFail();

        $this->actingAs($ordinaryUser)
            ->get(route('access-control.index'))
            ->assertForbidden();

        $this->actingAs($superadmin)
            ->post(route('access-control.store'), [
                'user_id' => $targetUser->id,
                'role_id' => $role->id,
                'expires_at' => now()->addDays(30)->toDateString(),
            ])
            ->assertRedirect(route('access-control.index'));

        $this->assertDatabaseHas('role_user', [
            'user_id' => $targetUser->id,
            'role_id' => $role->id,
            'assigned_by' => $superadmin->id,
        ]);
    }

    private function createUser(bool $isSuperadmin): User
    {
        $suffix = Str::lower(Str::random(10));

        return User::create([
            'name' => 'Access User ' . $suffix,
            'username' => 'access_' . $suffix,
            'division' => 'BT - DIGITAL BANKING',
            'division_verified_at' => now(),
            'is_superadmin' => $isSuperadmin,
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }
}
