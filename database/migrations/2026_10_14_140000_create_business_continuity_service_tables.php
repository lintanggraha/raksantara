<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Business continuity per service (ISO 22301 cl. 8.2.2 / ISO 22317): the BIA
 * belongs to a business service or process, and risks are linked to the
 * services they can disrupt. Replaces the per-risk `bias` / `bcps` tables,
 * which held no data. down() restores those (empty) tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_services', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('name');
            $table->string('owner_unit');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft'); // draft, active, retired
            $table->text('dependency_systems')->nullable();
            $table->text('dependency_vendors')->nullable();
            $table->text('dependency_sites')->nullable();
            $table->text('key_personnel')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['owner_unit', 'status']);
        });

        Schema::create('service_bias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_service_id')->unique()->constrained('business_services')->cascadeOnDelete();
            $table->json('impact_matrix'); // {"60": {"financial": 1, ...}, "240": {...}, ...} keyed by minutes
            $table->unsignedInteger('mtpd_minutes')->nullable(); // null = tolerable beyond the longest horizon
            $table->string('tier', 10);
            $table->unsignedInteger('rto_minutes');
            $table->unsignedInteger('rpo_minutes');
            $table->text('minimum_resources')->nullable();
            $table->text('notes')->nullable();
            $table->date('next_review_at')->nullable();
            $table->foreignId('assessed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assessed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['tier', 'approved_at']);
        });

        Schema::create('business_service_risk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_service_id')->constrained('business_services')->cascadeOnDelete();
            $table->foreignId('risk_id')->constrained('risks')->cascadeOnDelete();
            $table->foreignId('linked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['business_service_id', 'risk_id'], 'business_service_risk_unique');
            $table->index('risk_id');
        });

        Schema::create('service_bcps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_service_id')->unique()->constrained('business_services')->cascadeOnDelete();
            $table->text('activation_criteria')->nullable();
            $table->text('strategy_summary');
            $table->text('recovery_procedures');
            $table->text('primary_workaround')->nullable();
            $table->text('contacts')->nullable();
            $table->string('testing_frequency', 20); // Quarterly, Semi-Annual, Annual
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('service_bcp_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_service_id')->constrained('business_services')->cascadeOnDelete();
            $table->date('tested_at');
            $table->string('test_type', 20); // tabletop, walkthrough, simulation, full_dr, restore
            $table->string('result', 10); // pass, partial, fail
            $table->unsignedInteger('actual_recovery_minutes')->nullable();
            $table->text('findings')->nullable();
            $table->text('follow_up')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['business_service_id', 'tested_at']);
        });

        Schema::dropIfExists('bcps');
        Schema::dropIfExists('bias');
    }

    public function down(): void
    {
        Schema::dropIfExists('service_bcp_tests');
        Schema::dropIfExists('service_bcps');
        Schema::dropIfExists('business_service_risk');
        Schema::dropIfExists('service_bias');
        Schema::dropIfExists('business_services');

        Schema::create('bias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('risk_id')->unique();
            $table->string('rto')->nullable();
            $table->string('rpo')->nullable();
            $table->string('mao')->nullable();
            $table->string('impact_financial')->nullable();
            $table->string('impact_operational')->nullable();
            $table->string('impact_regulatory')->nullable();
            $table->string('impact_reputational')->nullable();
            $table->string('criticality_tier')->nullable();
            $table->text('justification_notes')->nullable();
            $table->timestamps();
            $table->foreign('risk_id')->references('id')->on('risks')->onDelete('cascade');
        });

        Schema::create('bcps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bia_id')->unique();
            $table->text('strategy_summary')->nullable();
            $table->text('recovery_procedures')->nullable();
            $table->text('primary_workaround')->nullable();
            $table->string('testing_frequency')->nullable();
            $table->date('last_tested_at')->nullable();
            $table->string('last_test_result')->nullable();
            $table->timestamps();
            $table->foreign('bia_id')->references('id')->on('bias')->onDelete('cascade');
        });
    }
};
