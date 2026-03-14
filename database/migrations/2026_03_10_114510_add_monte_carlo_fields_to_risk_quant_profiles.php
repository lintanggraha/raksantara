<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMonteCarloFieldsToRiskQuantProfiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('risk_quant_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('financial_impact_best_case')->nullable()->after('residual_loss_factor');
            $table->unsignedBigInteger('financial_impact_most_likely')->nullable()->after('financial_impact_best_case');
            $table->unsignedBigInteger('financial_impact_worst_case')->nullable()->after('financial_impact_most_likely');
            $table->decimal('monte_carlo_var', 15, 2)->nullable()->after('financial_impact_worst_case');
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
            $table->dropColumn([
                'financial_impact_best_case',
                'financial_impact_most_likely',
                'financial_impact_worst_case',
                'monte_carlo_var'
            ]);
        });
    }
}
