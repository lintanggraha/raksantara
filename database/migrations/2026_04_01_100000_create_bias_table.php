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
        Schema::create('bias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('risk_id')->unique();
            $table->string('rto')->nullable(); // Recovery Time Objective
            $table->string('rpo')->nullable(); // Recovery Point Objective
            $table->string('mao')->nullable(); // Maximum Acceptable Outage
            
            // Impact scales e.g., Low, Medium, High, Critical
            $table->string('impact_financial')->nullable();
            $table->string('impact_operational')->nullable();
            $table->string('impact_regulatory')->nullable();
            $table->string('impact_reputational')->nullable();
            
            // Tier / Criticality
            $table->string('criticality_tier')->nullable();
            
            $table->text('justification_notes')->nullable();
            $table->timestamps();

            $table->foreign('risk_id')->references('id')->on('risks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bias');
    }
};
