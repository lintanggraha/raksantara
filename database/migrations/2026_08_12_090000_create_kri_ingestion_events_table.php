<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kri_ingestion_events', function (Blueprint $table) {
            $table->id();
            $table->string('source', 100);
            $table->string('event_id', 191);
            $table->foreignId('kri_id')->nullable()->constrained('kris')->nullOnDelete();
            $table->timestamp('source_recorded_at')->nullable();
            $table->string('payload_hash', 64);
            $table->string('status', 20)->default('accepted');
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->unique(['source', 'event_id'], 'kri_ingestion_source_event_unique');
            $table->index(['status', 'processed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kri_ingestion_events');
    }
};
