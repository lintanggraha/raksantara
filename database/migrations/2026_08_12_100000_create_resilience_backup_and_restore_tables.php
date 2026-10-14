<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resilience_backup_records', function (Blueprint $table) {
            $table->id();
            $table->string('backup_reference', 100)->unique();
            $table->string('asset_scope', 40);
            $table->string('environment', 40)->default('production');
            $table->string('storage_location', 500);
            $table->string('encryption_status', 30)->default('encrypted');
            $table->string('status', 30)->default('successful');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->string('checksum', 255)->nullable();
            $table->timestamp('retention_until')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('evidence_reference', 500)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['asset_scope', 'status', 'completed_at']);
            $table->index(['retention_until', 'status']);
        });

        Schema::create('resilience_restore_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resilience_backup_record_id')->constrained('resilience_backup_records')->cascadeOnDelete();
            $table->string('test_type', 40);
            $table->timestamp('performed_at');
            $table->unsignedInteger('recovery_time_minutes')->nullable();
            $table->unsignedInteger('recovery_point_minutes')->nullable();
            $table->string('result', 30);
            $table->text('findings')->nullable();
            $table->text('remediation_plan')->nullable();
            $table->string('evidence_reference', 500)->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->index(['result', 'performed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resilience_restore_tests');
        Schema::dropIfExists('resilience_backup_records');
    }
};
