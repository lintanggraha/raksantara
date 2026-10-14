<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Month-end posture per risk, so the dashboard can show trends and
     * quarter-on-quarter comparisons (POJK 18/2016 Pasal 23). Analyses only
     * keep their current values, so history starts from the first snapshot.
     */
    public function up(): void
    {
        Schema::create('risk_score_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('period'); // first day of the month the snapshot represents
            $table->foreignId('risk_id')->constrained('risks')->cascadeOnDelete();
            $table->string('risk_owner')->nullable();
            $table->unsignedBigInteger('risk_category_id')->nullable();
            $table->integer('inherent_score')->nullable();
            $table->integer('residual_score')->nullable();
            $table->string('residual_level', 40)->nullable();
            $table->string('appetite_status', 20);
            $table->timestamp('captured_at');
            $table->timestamps();

            $table->unique(['period', 'risk_id'], 'risk_score_snapshots_period_risk_unique');
            $table->index(['period', 'appetite_status'], 'risk_score_snapshots_period_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_score_snapshots');
    }
};
