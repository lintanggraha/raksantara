<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bcps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bia_id')->unique();
            $table->text('strategy_summary')->nullable();
            $table->text('recovery_procedures')->nullable();
            $table->text('primary_workaround')->nullable();
            
            // Testing & Maintenance
            $table->string('testing_frequency')->nullable(); // e.g., Annual, Semi-Annual, None
            $table->date('last_tested_at')->nullable();
            $table->string('last_test_result')->nullable(); // Pass, Partial, Fail
            
            $table->timestamps();

            $table->foreign('bia_id')->references('id')->on('bias')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bcps');
    }
};
