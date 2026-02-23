<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRiskAppetiteFieldsToRiskQuantProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('risk_quant_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('risk_quant_profiles', 'risk_appetite_score')) {
                $table->unsignedTinyInteger('risk_appetite_score')->nullable()->after('residual_loss_factor');
            }

            if (!Schema::hasColumn('risk_quant_profiles', 'risk_appetite_loss')) {
                $table->unsignedBigInteger('risk_appetite_loss')->nullable()->after('risk_appetite_score');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('risk_quant_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('risk_quant_profiles', 'risk_appetite_loss')) {
                $table->dropColumn('risk_appetite_loss');
            }

            if (Schema::hasColumn('risk_quant_profiles', 'risk_appetite_score')) {
                $table->dropColumn('risk_appetite_score');
            }
        });
    }
}

