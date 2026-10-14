<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('risk_incidents', function (Blueprint $table) {
            $table->string('affected_client', 150)->nullable()->after('risk_owner');
            $table->index('affected_client', 'risk_incidents_affected_client_idx');
        });
    }

    public function down(): void
    {
        Schema::table('risk_incidents', function (Blueprint $table) {
            $table->dropIndex('risk_incidents_affected_client_idx');
            $table->dropColumn('affected_client');
        });
    }
};
