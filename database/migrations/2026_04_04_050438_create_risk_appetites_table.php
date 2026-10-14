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
        Schema::create('risk_appetites', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('risk_category')->nullable();
            $table->text('appetite_statement');
            $table->decimal('appetite_limit_value', 20, 2)->nullable();
            $table->decimal('tolerance_limit_value', 20, 2)->nullable();
            $table->string('unit')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_appetites');
    }
};
