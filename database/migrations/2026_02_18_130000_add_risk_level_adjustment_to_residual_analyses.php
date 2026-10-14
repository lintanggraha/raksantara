<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRiskLevelAdjustmentToResidualAnalyses extends Migration
{
    public function up()
    {
        Schema::table('risk_residual_analyses', function (Blueprint $table) {
            if (!Schema::hasColumn('risk_residual_analyses', 'risk_level_adjustment')) {
                $table->string('risk_level_adjustment')->nullable()->after('risk_level');
            }
        });
    }

    public function down()
    {
        Schema::table('risk_residual_analyses', function (Blueprint $table) {
            if (Schema::hasColumn('risk_residual_analyses', 'risk_level_adjustment')) {
                $table->dropColumn('risk_level_adjustment');
            }
        });
    }
}
