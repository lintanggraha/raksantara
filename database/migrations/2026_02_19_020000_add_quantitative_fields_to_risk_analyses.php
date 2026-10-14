<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuantitativeFieldsToRiskAnalyses extends Migration
{
    public function up()
    {
        Schema::table('risk_inherent_analyses', function (Blueprint $table) {
            if (!Schema::hasColumn('risk_inherent_analyses', 'impact_amount')) {
                $table->unsignedBigInteger('impact_amount')->nullable()->after('impact_scale_id');
            }

            if (!Schema::hasColumn('risk_inherent_analyses', 'probability_percent')) {
                $table->decimal('probability_percent', 8, 4)->nullable()->after('impact_amount');
            }

            if (!Schema::hasColumn('risk_inherent_analyses', 'potential_loss')) {
                $table->unsignedBigInteger('potential_loss')->nullable()->after('probability_percent');
            }
        });

        Schema::table('risk_residual_analyses', function (Blueprint $table) {
            if (!Schema::hasColumn('risk_residual_analyses', 'impact_amount')) {
                $table->unsignedBigInteger('impact_amount')->nullable()->after('impact_scale_id');
            }

            if (!Schema::hasColumn('risk_residual_analyses', 'probability_percent')) {
                $table->decimal('probability_percent', 8, 4)->nullable()->after('impact_amount');
            }

            if (!Schema::hasColumn('risk_residual_analyses', 'potential_loss')) {
                $table->unsignedBigInteger('potential_loss')->nullable()->after('probability_percent');
            }
        });
    }

    public function down()
    {
        Schema::table('risk_inherent_analyses', function (Blueprint $table) {
            if (Schema::hasColumn('risk_inherent_analyses', 'potential_loss')) {
                $table->dropColumn('potential_loss');
            }

            if (Schema::hasColumn('risk_inherent_analyses', 'probability_percent')) {
                $table->dropColumn('probability_percent');
            }

            if (Schema::hasColumn('risk_inherent_analyses', 'impact_amount')) {
                $table->dropColumn('impact_amount');
            }
        });

        Schema::table('risk_residual_analyses', function (Blueprint $table) {
            if (Schema::hasColumn('risk_residual_analyses', 'potential_loss')) {
                $table->dropColumn('potential_loss');
            }

            if (Schema::hasColumn('risk_residual_analyses', 'probability_percent')) {
                $table->dropColumn('probability_percent');
            }

            if (Schema::hasColumn('risk_residual_analyses', 'impact_amount')) {
                $table->dropColumn('impact_amount');
            }
        });
    }
}
