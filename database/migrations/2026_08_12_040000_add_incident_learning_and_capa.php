<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('risk_incidents', function (Blueprint $table) {
            $table->text('lessons_learned')->nullable()->after('corrective_action');
            $table->string('risk_review_status', 20)->default('pending')->after('lessons_learned');
            $table->string('control_review_status', 20)->default('pending')->after('risk_review_status');
            $table->string('kri_review_status', 20)->default('pending')->after('control_review_status');
            $table->foreignId('post_incident_reviewed_by')->nullable()->after('last_updated_by')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('post_incident_reviewed_at')->nullable()->after('post_incident_reviewed_by');
            $table->index(['risk_review_status', 'control_review_status', 'kri_review_status'], 'incident_review_status_index');
        });

        Schema::create('risk_incident_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_incident_id')->constrained('risk_incidents')->cascadeOnDelete();
            $table->string('action_type', 20);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('owner_name', 255);
            $table->date('due_at')->nullable();
            $table->string('status', 20)->default('open');
            $table->text('completion_notes')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['risk_incident_id', 'status']);
            $table->index(['status', 'due_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_incident_actions');

        Schema::table('risk_incidents', function (Blueprint $table) {
            $table->dropIndex('incident_review_status_index');
            $table->dropConstrainedForeignId('post_incident_reviewed_by');
            $table->dropColumn([
                'lessons_learned',
                'risk_review_status',
                'control_review_status',
                'kri_review_status',
                'post_incident_reviewed_at',
            ]);
        });
    }
};
