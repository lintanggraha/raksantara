<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOperationalFieldsToRiskTreatmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('risk_treatments', function (Blueprint $table) {
            $table->foreignId('template_id')
                ->nullable()
                ->after('risk_id')
                ->constrained('risk_treatment_templates')
                ->nullOnDelete();
            $table->timestamp('status_updated_at')->nullable()->after('status');
            $table->date('next_action_at')->nullable()->after('deadline');
            $table->text('blocked_reason')->nullable()->after('next_action_at');
            $table->text('playbook_checklist')->nullable()->after('blocked_reason');

            $table->index(['status', 'deadline'], 'risk_treatments_status_deadline_idx');
            $table->index('next_action_at', 'risk_treatments_next_action_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('risk_treatments', function (Blueprint $table) {
            $table->dropIndex('risk_treatments_status_deadline_idx');
            $table->dropIndex('risk_treatments_next_action_idx');
            $table->dropConstrainedForeignId('template_id');
            $table->dropColumn([
                'status_updated_at',
                'next_action_at',
                'blocked_reason',
                'playbook_checklist',
            ]);
        });
    }
}
