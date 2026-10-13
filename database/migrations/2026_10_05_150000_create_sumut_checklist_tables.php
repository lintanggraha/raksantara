<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sumut_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->integer('point_number')->unique(); // 1 to 27
            $table->string('category', 150); // e.g. CBS & Integritas Transaksi
            $table->text('title'); // Full activity description (kegiatan)
            $table->string('clean_title', 255)->nullable();
            $table->string('progress', 50)->default('0%'); // e.g. 100%, 25%
            $table->integer('progress_percent')->default(0); // 0 - 100
            $table->string('progress_2509', 50)->nullable();
            $table->string('progress_2609', 50)->nullable();
            $table->text('keterangan')->nullable();
            $table->string('target_date', 100)->nullable(); // e.g. 25/09/2026
            $table->string('pic', 150)->nullable(); // e.g. Div. TI, Tim CS / DTI
            $table->string('status_bs', 100)->nullable(); // e.g. Done, On Progress
            $table->string('progress_bs', 50)->nullable();
            $table->text('follow_up')->nullable(); // OJK / Bank follow up notes
            $table->enum('status', ['Ada', 'Tidak Ada'])->default('Ada');
            $table->text('notes')->nullable(); // Analysis of evidence compliance
            $table->text('recommendation')->nullable(); // Other recommended forensic/technical evidence
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('sumut_checklist_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_item_id')->constrained('sumut_checklist_items')->onDelete('cascade');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('folder_number', 10); // e.g. '01', '02', ... '27'
            $table->string('file_name', 255);
            $table->string('original_filename', 255);
            $table->text('file_path');
            $table->string('storage_location', 50)->default('local_sumut');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type', 100)->nullable();
            $table->string('file_extension', 20)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sumut_checklist_evidences');
        Schema::dropIfExists('sumut_checklist_items');
    }
};
