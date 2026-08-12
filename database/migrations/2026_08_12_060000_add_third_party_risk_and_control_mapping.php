<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('onboarding_status', 30)->default('draft')->after('status');
            $table->string('risk_rating', 20)->nullable()->after('criticality_level');
            $table->string('service_owner', 255)->nullable()->after('risk_rating');
            $table->string('data_classification', 40)->nullable()->after('service_owner');
            $table->date('contract_end_date')->nullable()->after('data_classification');
            $table->date('next_review_at')->nullable()->after('contract_end_date');
            $table->timestamp('last_assessed_at')->nullable()->after('next_review_at');
            $table->index(['onboarding_status', 'next_review_at']);
        });

        Schema::create('vendor_risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('assessment_type', 30);
            $table->date('assessed_at');
            $table->foreignId('assessed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('inherent_rating')->nullable();
            $table->unsignedTinyInteger('residual_rating')->nullable();
            $table->string('decision', 30);
            $table->date('valid_until')->nullable();
            $table->text('findings')->nullable();
            $table->text('risk_treatment')->nullable();
            $table->string('status', 20)->default('open');
            $table->timestamps();
            $table->index(['vendor_id', 'assessed_at']);
            $table->index(['decision', 'valid_until']);
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
            $table->index(['framework_code', 'mapping_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_control_framework_mappings');
        Schema::dropIfExists('vendor_risk_assessments');

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropIndex(['onboarding_status', 'next_review_at']);
            $table->dropColumn([
                'onboarding_status',
                'risk_rating',
                'service_owner',
                'data_classification',
                'contract_end_date',
                'next_review_at',
                'last_assessed_at',
            ]);
        });
    }
};
