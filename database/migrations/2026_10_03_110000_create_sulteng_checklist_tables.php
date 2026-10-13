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
        Schema::create('sulteng_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->string('category_code', 10); // A, B, C, D, E, F
            $table->string('category_name', 255);
            $table->integer('item_number'); // 1, 2, 3...
            $table->string('code', 20)->index(); // A.1, B.2...
            $table->text('title'); // Full title/description
            $table->string('clean_title', 255)->nullable(); // Sanitized single-line title for filename
            $table->string('period', 100)->nullable(); // e.g. 11–20 September 2026
            $table->string('pic_division', 100)->nullable(); // e.g. Infra, ITOM
            $table->enum('status', ['Belum Diterima', 'Dalam Proses', 'Diterima'])->default('Belum Diterima');
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('sulteng_checklist_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_item_id')->constrained('sulteng_checklist_items')->onDelete('cascade');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_name', 255); // Adjusted name based on title (-1, -2, -3)
            $table->text('file_path'); // Path to file on disk (D:\Playground\SULTENG\evidence\...)
            $table->string('storage_location', 50)->default('local_sulteng');
            $table->string('original_filename', 255);
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type', 100)->nullable();
            $table->integer('evidence_index')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sulteng_checklist_evidences');
        Schema::dropIfExists('sulteng_checklist_items');
    }
};
