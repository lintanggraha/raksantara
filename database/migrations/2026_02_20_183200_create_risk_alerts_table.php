<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiskAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('risk_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('risk_treatment_id')->nullable()->constrained('risk_treatments')->nullOnDelete();
            $table->string('alert_type', 64);
            $table->string('severity', 24)->default('warning');
            $table->string('status', 24)->default('open');
            $table->date('due_date')->nullable();
            $table->text('message');
            $table->timestamp('alerted_at')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'severity']);
            $table->index(['alert_type', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('risk_alerts');
    }
}

