<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiskInherentAnalysesTable extends Migration
{
    public function up()
    {
        Schema::create('risk_inherent_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_id')->constrained()->cascadeOnDelete();
            
            // Probability
            $table->integer('probability_score')->nullable();
            $table->text('probability_description')->nullable();
            
            // Impact
            $table->integer('impact_score')->nullable();
            $table->text('impact_description')->nullable();
            $table->foreignId('impact_scale_id')->nullable()->constrained();
            
            // Inherent Risk Score
            $table->integer('inherent_risk_score')->nullable();
            $table->string('risk_level')->nullable();
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('risk_inherent_analyses');
    }
}
