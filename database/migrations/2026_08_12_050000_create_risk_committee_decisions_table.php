<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_committee_decisions', function (Blueprint $table) {
            $table->id();
            $table->date('meeting_date');
            $table->string('meeting_reference', 120)->nullable();
            $table->foreignId('risk_id')->nullable()->constrained('risks')->nullOnDelete();
            $table->string('title');
            $table->text('decision');
            $table->string('owner_name', 255);
            $table->date('due_at')->nullable();
            $table->string('status', 20)->default('open');
            $table->text('closure_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'due_at']);
            $table->index(['meeting_date', 'risk_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_committee_decisions');
    }
};
