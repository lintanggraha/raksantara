<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_acceptances');
        Schema::dropIfExists('risk_control_tests');
        Schema::dropIfExists('risk_controls');
    }
};
