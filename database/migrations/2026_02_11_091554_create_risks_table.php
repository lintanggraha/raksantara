<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRisksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    Schema::create('risks', function (Blueprint $table) {
        $table->id();
        $table->string('risk_title');
        $table->text('risk_description')->nullable();
        $table->foreignId('risk_category_id')->nullable()->constrained();
        $table->foreignId('risk_cause_category_id')->nullable()->constrained();
        $table->unsignedBigInteger('asset_id')->nullable();
        $table->string('risk_owner');
        $table->string('status')->default('open');
        $table->timestamps();
        $table->text('probability_notes')->nullable();
        $table->integer('probability_score')->nullable();
        $table->text('impact_notes')->nullable();
        $table->integer('impact_score')->nullable();
        $table->integer('risk_score')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('risks');
    }
}
