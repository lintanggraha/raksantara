<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvidenceAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evidence_attachments', function (Blueprint $table) {
            $table->id();
            $table->string('attachable_type', 160);
            $table->unsignedBigInteger('attachable_id');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('original_name', 255);
            $table->string('storage_path', 1024);
            $table->string('mime_type', 255)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['attachable_type', 'attachable_id'], 'evidence_attachable_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('evidence_attachments');
    }
}

