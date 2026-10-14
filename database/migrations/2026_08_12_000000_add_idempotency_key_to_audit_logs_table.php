<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('audit_logs', 'idempotency_key')) {
            return;
        }

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->string('idempotency_key', 64)
                ->nullable()
                ->after('event');
            $table->unique('idempotency_key', 'audit_logs_idempotency_key_unique');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('audit_logs', 'idempotency_key')) {
            return;
        }

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropUnique('audit_logs_idempotency_key_unique');
            $table->dropColumn('idempotency_key');
        });
    }
};
