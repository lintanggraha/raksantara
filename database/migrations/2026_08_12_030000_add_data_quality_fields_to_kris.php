<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kris', function (Blueprint $table) {
            $table->string('data_source', 40)->default('manual')->after('description');
            $table->string('source_identifier', 255)->nullable()->after('data_source');
            $table->string('source_reference', 500)->nullable()->after('source_identifier');
            $table->unsignedInteger('expected_refresh_minutes')->nullable()->after('source_reference');
            $table->timestamp('source_updated_at')->nullable()->after('expected_refresh_minutes');
            $table->string('data_quality_status', 20)->default('unverified')->after('status');
            $table->text('data_quality_notes')->nullable()->after('data_quality_status');
            $table->timestamp('last_quality_checked_at')->nullable()->after('data_quality_notes');
            $table->text('last_sync_error')->nullable()->after('last_quality_checked_at');
            $table->index(['data_quality_status', 'source_updated_at']);
            $table->index(['data_source', 'source_identifier']);
        });

        Schema::table('kri_logs', function (Blueprint $table) {
            $table->timestamp('source_recorded_at')->nullable()->after('recorded_value');
            $table->string('source_reference', 500)->nullable()->after('source_recorded_at');
            $table->string('data_quality_status', 20)->default('unverified')->after('status_calculated');
        });
    }

    public function down(): void
    {
        Schema::table('kri_logs', function (Blueprint $table) {
            $table->dropColumn(['source_recorded_at', 'source_reference', 'data_quality_status']);
        });

        Schema::table('kris', function (Blueprint $table) {
            $table->dropIndex(['data_quality_status', 'source_updated_at']);
            $table->dropIndex(['data_source', 'source_identifier']);
            $table->dropColumn([
                'data_source',
                'source_identifier',
                'source_reference',
                'expected_refresh_minutes',
                'source_updated_at',
                'data_quality_status',
                'data_quality_notes',
                'last_quality_checked_at',
                'last_sync_error',
            ]);
        });
    }
};
