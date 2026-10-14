<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRadarGroupIdToRiskResidualAnalyses extends Migration
{
    public function up()
    {
        Schema::table('risk_residual_analyses', function (Blueprint $table) {
            if (!Schema::hasColumn('risk_residual_analyses', 'radar_group_id')) {
                $table->foreignId('radar_group_id')
                    ->nullable()
                    ->after('risk_level_adjustment')
                    ->constrained('radar_groups')
                    ->nullOnDelete();
            }
        });
    }

    public function down()
    {
        Schema::table('risk_residual_analyses', function (Blueprint $table) {
            if (Schema::hasColumn('risk_residual_analyses', 'radar_group_id')) {
                $table->dropForeign(['radar_group_id']);
                $table->dropColumn('radar_group_id');
            }
        });
    }
}
