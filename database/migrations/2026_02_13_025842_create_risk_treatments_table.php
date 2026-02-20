<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiskTreatmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('risk_treatments', function (Blueprint $table) {
        $table->id();

        $table->foreignId('risk_id')->constrained()->cascadeOnDelete();

        $table->string('option');
        $table->text('description')->nullable();
        $table->string('pic');

        $table->date('deadline');

        $table->string('effectiveness');
        $table->decimal('effectiveness_score', 3,2);

        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('risk_treatments');
    }
}
