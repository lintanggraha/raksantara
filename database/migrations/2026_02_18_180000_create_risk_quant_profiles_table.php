<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiskQuantProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('risk_quant_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('radar_group_id')
                ->constrained('radar_groups')
                ->onDelete('cascade');
            $table->unsignedBigInteger('potential_loss_inherent')->default(0);
            $table->decimal('residual_loss_factor', 8, 4)->default(0.3000);
            $table->timestamps();

            $table->unique('radar_group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('risk_quant_profiles');
    }
}

