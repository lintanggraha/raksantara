<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_report_runs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scheduled_report_id');
            $table->string('trigger_type', 20)->default('auto');
            $table->string('status', 20)->default('success');
            $table->string('scope', 20);
            $table->string('format', 10);
            $table->unsignedInteger('recipient_count')->default(0);
            $table->string('output_filename', 255)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('ran_at');
            $table->timestamps();

            $table->foreign('scheduled_report_id')
                ->references('id')
                ->on('scheduled_reports')
                ->onDelete('cascade');

            $table->index(['scheduled_report_id', 'ran_at']);
            $table->index(['status', 'ran_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_report_runs');
    }
};
