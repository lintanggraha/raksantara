<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiskRcsaWorkspacesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('risk_rcsa_workspaces');
    }
}
