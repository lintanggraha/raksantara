<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_retention_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('record_type', 120)->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('retention_months');
            $table->string('owner_name', 255)->nullable();
            $table->string('status', 20)->default('active');
            $table->date('next_review_at')->nullable();
            $table->text('legal_basis')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'next_review_at']);
        });

        Schema::create('data_purge_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_retention_schedule_id')->constrained('data_retention_schedules')->restrictOnDelete();
            $table->string('scope_reference', 500);
            $table->unsignedBigInteger('estimated_record_count')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('proposed_purge_at')->nullable();
            $table->boolean('legal_hold_checked')->default(false);
            $table->text('justification');
            $table->string('status', 20)->default('requested');
            $table->text('review_notes')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('executed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('executed_at')->nullable();
            $table->string('evidence_reference', 500)->nullable();
            $table->timestamps();
            $table->index(['status', 'proposed_purge_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_purge_requests');
        Schema::dropIfExists('data_retention_schedules');
    }
};
