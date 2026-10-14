<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiskIncidentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('risk_incidents', function (Blueprint $table) {
            $table->id();
            $table->string('incident_code', 32)->unique();
            $table->foreignId('risk_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 255);
            $table->text('incident_description');
            $table->timestamp('occurred_at');
            $table->timestamp('detected_at')->nullable();
            $table->string('severity', 16)->default('Medium');
            $table->string('status', 24)->default('Open');
            $table->string('mitigation_verdict', 32)->default('Not Tested');
            $table->text('business_impact')->nullable();
            $table->text('immediate_action')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('corrective_action')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['risk_id', 'occurred_at'], 'risk_incidents_risk_occurred_idx');
            $table->index(['status', 'occurred_at'], 'risk_incidents_status_occurred_idx');
            $table->index('mitigation_verdict', 'risk_incidents_verdict_idx');
            $table->index('severity', 'risk_incidents_severity_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('risk_incidents');
    }
}
