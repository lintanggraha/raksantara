<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_scenarios', function (Blueprint $table) {
            $table->id();
            $table->string('scenario_code', 80)->unique();
            $table->string('title');
            $table->string('scenario_type', 40);
            $table->string('status', 20)->default('draft');
            $table->string('time_horizon', 80)->nullable();
            $table->unsignedTinyInteger('likelihood')->nullable();
            $table->unsignedTinyInteger('impact')->nullable();
            $table->text('trigger_event')->nullable();
            $table->text('narrative');
            $table->text('response_strategy')->nullable();
            $table->string('owner_name', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['scenario_type', 'status']);
        });

        Schema::create('risk_scenario_impacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_scenario_id')->constrained('risk_scenarios')->cascadeOnDelete();
            $table->foreignId('risk_id')->constrained('risks')->cascadeOnDelete();
            $table->unsignedTinyInteger('scenario_probability')->nullable();
            $table->unsignedTinyInteger('scenario_impact')->nullable();
            $table->decimal('scenario_score', 10, 2)->nullable();
            $table->text('assumptions')->nullable();
            $table->text('mitigation_response')->nullable();
            $table->timestamps();
            $table->unique(['risk_scenario_id', 'risk_id'], 'scenario_risk_unique');
            $table->index(['risk_id', 'scenario_score']);
        });

        Schema::create('risk_maturity_assessments', function (Blueprint $table) {
            $table->id();
            $table->string('assessment_period', 40);
            $table->string('domain', 120);
            $table->unsignedTinyInteger('current_score');
            $table->unsignedTinyInteger('target_score');
            $table->text('evidence')->nullable();
            $table->text('improvement_plan')->nullable();
            $table->string('owner_name', 255)->nullable();
            $table->date('target_date')->nullable();
            $table->string('status', 20)->default('open');
            $table->foreignId('assessed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['assessment_period', 'domain'], 'maturity_period_domain_unique');
            $table->index(['assessment_period', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_maturity_assessments');
        Schema::dropIfExists('risk_scenario_impacts');
        Schema::dropIfExists('risk_scenarios');
    }
};
