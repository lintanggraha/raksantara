<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKrisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kris', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_id')->constrained('risks')->onDelete('cascade');
            $table->string('metric_name');
            $table->text('description')->nullable();
            
            // Defines whether a high number is good or bad
            $table->string('threshold_type')->default('lower_is_better'); // 'higher_is_better' or 'lower_is_better'
            
            // Thresholds
            $table->decimal('threshold_warn', 15, 2)->nullable();
            $table->decimal('threshold_critical', 15, 2)->nullable();
            
            // Current State
            $table->decimal('current_value', 15, 2)->nullable();
            $table->string('status')->default('Green'); // 'Green', 'Amber', 'Red'
            
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->onDelete('set null');
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
        Schema::dropIfExists('kris');
    }
}
