<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiskTreatmentTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('risk_treatment_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('risk_owner');
            $table->string('option');
            $table->text('description');
            $table->string('default_pic')->nullable();
            $table->string('default_effectiveness', 50)->default('Medium');
            $table->unsignedInteger('target_days')->nullable();
            $table->unsignedInteger('next_action_offset_days')->nullable();
            $table->text('checklist')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['risk_owner', 'is_active'], 'risk_treatment_templates_owner_active_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('risk_treatment_templates');
    }
}
