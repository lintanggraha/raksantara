<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSupportNeededToComplianceWorkProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('compliance_work_programs') || Schema::hasColumn('compliance_work_programs', 'support_needed')) {
            return;
        }

        Schema::table('compliance_work_programs', function (Blueprint $table) {
            $table->text('support_needed')->nullable()->after('alignment_risk_profile_2025');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('compliance_work_programs') || !Schema::hasColumn('compliance_work_programs', 'support_needed')) {
            return;
        }

        Schema::table('compliance_work_programs', function (Blueprint $table) {
            $table->dropColumn('support_needed');
        });
    }
}
