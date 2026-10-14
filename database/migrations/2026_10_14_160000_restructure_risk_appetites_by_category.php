<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Risk appetite is set per risk category (null = default for every category)
 * and applies to risks automatically; a risk may still override it. Board
 * approval and the review cycle are recorded on the statement.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('risk_appetites', function (Blueprint $table) {
            $table->foreignId('risk_category_id')->nullable()->after('name')->constrained('risk_categories')->nullOnDelete();
            $table->string('approval_reference', 255)->nullable()->after('is_active');
            $table->date('approved_at')->nullable()->after('approval_reference');
            $table->date('next_review_at')->nullable()->after('approved_at');
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->index(['risk_category_id', 'is_active'], 'risk_appetites_category_active_idx');
        });

        // The free-text category could not be matched to risks; no rows used it.
        Schema::table('risk_appetites', function (Blueprint $table) {
            $table->dropColumn('risk_category');
        });
    }

    public function down(): void
    {
        Schema::table('risk_appetites', function (Blueprint $table) {
            $table->string('risk_category')->nullable()->after('name');
        });

        Schema::table('risk_appetites', function (Blueprint $table) {
            $table->dropIndex('risk_appetites_category_active_idx');
            $table->dropConstrainedForeignId('updated_by');
            $table->dropConstrainedForeignId('risk_category_id');
            $table->dropColumn(['approval_reference', 'approved_at', 'next_review_at']);
        });
    }
};
