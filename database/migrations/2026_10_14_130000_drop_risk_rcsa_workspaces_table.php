<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Removes the free-text RCSA workspace; the risk register already captures
 * ownership, inherent/residual rationale, treatments and KRIs.
 * down() recreates the empty table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('risk_rcsa_workspaces');
    }

    public function down(): void
    {
        Schema::create('risk_rcsa_workspaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_id')->unique()->constrained('risks')->cascadeOnDelete();
            $table->string('process_owner')->nullable();
            $table->text('objective')->nullable();
            $table->text('scope')->nullable();
            $table->text('key_controls')->nullable();
            $table->text('control_gaps')->nullable();
            $table->text('likelihood_rationale')->nullable();
            $table->text('impact_rationale')->nullable();
            $table->string('review_frequency', 50)->nullable();
            $table->date('next_review_at')->nullable();
            $table->string('sign_off_name')->nullable();
            $table->string('sign_off_role')->nullable();
            $table->timestamp('signed_off_at')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
};
