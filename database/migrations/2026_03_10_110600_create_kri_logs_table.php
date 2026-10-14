<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKriLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kri_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kri_id')->constrained('kris')->onDelete('cascade');
            $table->decimal('recorded_value', 15, 2);
            $table->string('status_calculated'); // 'Green', 'Amber', 'Red'
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->onDelete('set null');
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
        Schema::dropIfExists('kri_logs');
    }
}
