<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRadarColumnsToRiskResidualAnalyses extends Migration
{
    public function up()
    {
        Schema::table('risk_residual_analyses', function (Blueprint $table) {
            if (!Schema::hasColumn('risk_residual_analyses', 'radar_description')) {
                $table->string('radar_description')->nullable()->after('risk_level_adjustment');
            }

            if (!Schema::hasColumn('risk_residual_analyses', 'radar_code')) {
                $table->string('radar_code', 10)->nullable()->after('radar_description');
            }
        });
    }

    public function down()
    {
        Schema::table('risk_residual_analyses', function (Blueprint $table) {
            if (Schema::hasColumn('risk_residual_analyses', 'radar_code')) {
                $table->dropColumn('radar_code');
            }

            if (Schema::hasColumn('risk_residual_analyses', 'radar_description')) {
                $table->dropColumn('radar_description');
            }
        });
    }
}

