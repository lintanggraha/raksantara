<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Removes the control assurance, risk acceptance and regulatory control
 * mapping module. down() recreates the empty tables and the RBAC grants.
 */
return new class extends Migration
{
    private const PERMISSIONS = [
        ['name' => 'Manage Control Assurance', 'slug' => 'controls.manage', 'group' => 'governance', 'description' => 'Register and test risk controls.'],
        ['name' => 'Request Risk Acceptance', 'slug' => 'risk-acceptance.request', 'group' => 'governance', 'description' => 'Submit risk acceptance requests.'],
        ['name' => 'Decide Risk Acceptance', 'slug' => 'risk-acceptance.decide', 'group' => 'governance', 'description' => 'Approve or reject risk acceptance requests.'],
    ];

    private const GRANTS = [
        'risk-owner' => ['controls.manage', 'risk-acceptance.request'],
        'risk-manager' => ['controls.manage', 'risk-acceptance.request'],
        'compliance-officer' => ['controls.manage', 'risk-acceptance.request'],
        'system-administrator' => ['risk-acceptance.decide'],
    ];

    public function up(): void
    {
        Schema::dropIfExists('risk_control_framework_mappings');
        Schema::dropIfExists('risk_control_tests');
        Schema::dropIfExists('risk_controls');
        Schema::dropIfExists('risk_acceptances');

        if (Schema::hasTable('permissions')) {
            // permission_role rows go with them (cascadeOnDelete).
            DB::table('permissions')->whereIn('slug', array_column(self::PERMISSIONS, 'slug'))->delete();
        }
    }

    public function down(): void
    {
        Schema::create('risk_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_id')->constrained('risks')->cascadeOnDelete();
            $table->string('control_code', 80);
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('owner_name', 255)->nullable();
            $table->string('review_frequency', 30)->default('Quarterly');
            $table->string('status', 20)->default('active');
            $table->string('design_effectiveness', 20)->default('not_assessed');
            $table->date('last_tested_at')->nullable();
            $table->date('next_test_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['risk_id', 'control_code'], 'risk_controls_risk_code_unique');
            $table->index(['risk_id', 'status']);
            $table->index(['status', 'next_test_at']);
        });

        Schema::create('risk_control_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_control_id')->constrained('risk_controls')->cascadeOnDelete();
            $table->foreignId('tested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('tested_at');
            $table->string('result', 20);
            $table->text('findings')->nullable();
            $table->text('remediation_plan')->nullable();
            $table->date('remediation_due_at')->nullable();
            $table->string('remediation_status', 20)->default('not_required');
            $table->timestamps();

            $table->index(['risk_control_id', 'tested_at']);
            $table->index(['result', 'remediation_due_at']);
        });

        Schema::create('risk_acceptances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_id')->constrained('risks')->cascadeOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('requested');
            $table->text('rationale');
            $table->text('compensating_controls')->nullable();
            $table->date('accepted_until');
            $table->text('decision_notes')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            $table->index(['risk_id', 'status']);
            $table->index(['status', 'accepted_until']);
        });

        Schema::create('risk_control_framework_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_control_id')->constrained('risk_controls')->cascadeOnDelete();
            $table->string('framework_code', 80);
            $table->string('control_reference', 120);
            $table->string('requirement_title', 255)->nullable();
            $table->string('mapping_status', 30)->default('mapped');
            $table->text('evidence_notes')->nullable();
            $table->date('last_reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['risk_control_id', 'framework_code', 'control_reference'], 'control_framework_mapping_unique');
            $table->index(['framework_code', 'mapping_status'], 'rcfm_framework_status_idx');
        });

        if (!Schema::hasTable('permissions')) {
            return;
        }

        $now = now();
        foreach (self::PERMISSIONS as $permission) {
            DB::table('permissions')->updateOrInsert(['slug' => $permission['slug']], $permission + ['created_at' => $now, 'updated_at' => $now]);
        }

        $roleIds = DB::table('roles')->pluck('id', 'slug');
        $permissionIds = DB::table('permissions')->pluck('id', 'slug');
        foreach (self::GRANTS as $role => $slugs) {
            if (!isset($roleIds[$role])) {
                continue;
            }
            foreach ($slugs as $slug) {
                DB::table('permission_role')->updateOrInsert(['role_id' => $roleIds[$role], 'permission_id' => $permissionIds[$slug]]);
            }
        }
    }
};
