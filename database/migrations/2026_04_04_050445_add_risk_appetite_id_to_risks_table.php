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
        Schema::table('risks', function (Blueprint $table) {
            $table->unsignedBigInteger('risk_appetite_id')->nullable()->after('risk_category_id');
            
            $table->foreign('risk_appetite_id')->references('id')->on('risk_appetites')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risks', function (Blueprint $table) {
            $table->dropForeign(['risk_appetite_id']);
            $table->dropColumn('risk_appetite_id');
        });
    }
};
