<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizational_units', function (Blueprint $table) {
            $table->id();
            $table->string('code', 80)->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug', 80)->unique();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug', 120)->unique();
            $table->string('group', 80)->default('general');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('organizational_unit_id')->nullable()->constrained('organizational_units')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['role_id', 'user_id', 'organizational_unit_id'], 'role_user_scope_unique');
            $table->index(['user_id', 'expires_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organizational_unit_id')->nullable()->after('division_verified_at')
                ->constrained('organizational_units')->nullOnDelete();
        });

        $now = now();
        $roles = [
            ['name' => 'Risk Owner', 'slug' => 'risk-owner', 'description' => 'Manages risks and treatments within an organizational scope.', 'is_system' => true],
            ['name' => 'Risk Manager', 'slug' => 'risk-manager', 'description' => 'Monitors enterprise risk and governance workflows.', 'is_system' => true],
            ['name' => 'Compliance Officer', 'slug' => 'compliance-officer', 'description' => 'Oversees compliance and audit remediation.', 'is_system' => true],
            ['name' => 'Auditor', 'slug' => 'auditor', 'description' => 'Read-only review and audit evidence access.', 'is_system' => true],
            ['name' => 'System Administrator', 'slug' => 'system-administrator', 'description' => 'Administers access control and application configuration.', 'is_system' => true],
        ];
        foreach ($roles as $role) {
            DB::table('roles')->insert(array_merge($role, ['created_at' => $now, 'updated_at' => $now]));
        }

        $permissions = [
            ['name' => 'View Risks', 'slug' => 'risks.view', 'group' => 'risk', 'description' => 'View risk register and monitoring data.'],
            ['name' => 'Manage Risks', 'slug' => 'risks.manage', 'group' => 'risk', 'description' => 'Create and maintain owned risk records.'],
            ['name' => 'Manage Control Assurance', 'slug' => 'controls.manage', 'group' => 'governance', 'description' => 'Register and test risk controls.'],
            ['name' => 'Request Risk Acceptance', 'slug' => 'risk-acceptance.request', 'group' => 'governance', 'description' => 'Submit risk acceptance requests.'],
            ['name' => 'Decide Risk Acceptance', 'slug' => 'risk-acceptance.decide', 'group' => 'governance', 'description' => 'Approve or reject risk acceptance requests.'],
            ['name' => 'View Audit Evidence', 'slug' => 'audit.view', 'group' => 'audit', 'description' => 'View audit logs and evidence.'],
            ['name' => 'Manage Access Control', 'slug' => 'access.manage', 'group' => 'administration', 'description' => 'Assign and revoke role assignments.'],
        ];
        foreach ($permissions as $permission) {
            DB::table('permissions')->insert(array_merge($permission, ['created_at' => $now, 'updated_at' => $now]));
        }

        $roleIds = DB::table('roles')->pluck('id', 'slug');
        $permissionIds = DB::table('permissions')->pluck('id', 'slug');
        $grants = [
            'risk-owner' => ['risks.view', 'risks.manage', 'controls.manage', 'risk-acceptance.request'],
            'risk-manager' => ['risks.view', 'controls.manage', 'risk-acceptance.request'],
            'compliance-officer' => ['risks.view', 'controls.manage', 'risk-acceptance.request', 'audit.view'],
            'auditor' => ['risks.view', 'audit.view'],
            'system-administrator' => ['risks.view', 'risk-acceptance.decide', 'audit.view', 'access.manage'],
        ];
        foreach ($grants as $roleSlug => $permissionSlugs) {
            foreach ($permissionSlugs as $permissionSlug) {
                DB::table('permission_role')->insert([
                    'role_id' => $roleIds[$roleSlug],
                    'permission_id' => $permissionIds[$permissionSlug],
                ]);
            }
        }

        $adminRoleId = $roleIds['system-administrator'];
        foreach (DB::table('users')->where('is_superadmin', true)->pluck('id') as $userId) {
            DB::table('role_user')->insert([
                'role_id' => $adminRoleId,
                'user_id' => $userId,
                'organizational_unit_id' => null,
                'assigned_by' => null,
                'expires_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('organizational_unit_id');
        });
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('organizational_units');
    }
};
