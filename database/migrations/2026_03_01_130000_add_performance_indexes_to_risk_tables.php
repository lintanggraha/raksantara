<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('risks', function (Blueprint $table) {
            $table->index('created_at', 'risks_created_at_idx');
            $table->index('risk_owner', 'risks_risk_owner_idx');
            $table->index(['status', 'created_at'], 'risks_status_created_at_idx');
            $table->index(['risk_category_id', 'created_at'], 'risks_category_created_at_idx');
            $table->index(['risk_cause_category_id', 'created_at'], 'risks_cause_category_created_at_idx');
        });

        Schema::table('risk_treatments', function (Blueprint $table) {
            $table->index('deadline', 'risk_treatments_deadline_idx');
            $table->index(['risk_id', 'deadline'], 'risk_treatments_risk_id_deadline_idx');
        });
    }

    public function down(): void
    {
        Schema::table('risk_treatments', function (Blueprint $table) {
            $table->dropIndex('risk_treatments_risk_id_deadline_idx');
            $table->dropIndex('risk_treatments_deadline_idx');
        });

        Schema::table('risks', function (Blueprint $table) {
            $table->dropIndex('risks_cause_category_created_at_idx');
            $table->dropIndex('risks_category_created_at_idx');
            $table->dropIndex('risks_status_created_at_idx');
            $table->dropIndex('risks_risk_owner_idx');
            $table->dropIndex('risks_created_at_idx');
        });
    }
};

