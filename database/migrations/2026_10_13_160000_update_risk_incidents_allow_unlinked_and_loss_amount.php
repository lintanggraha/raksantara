<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Laravel 11+ can alter foreign keys on every supported driver
        // (SQLite rebuilds the table), so the cascade is replaced everywhere.
        Schema::table('risk_incidents', function (Blueprint $table) {
            $table->dropForeign(['risk_id']);
        });

        Schema::table('risk_incidents', function (Blueprint $table) {
            $table->unsignedBigInteger('risk_id')->nullable()->change();
            $table->string('risk_owner', 100)->nullable()->after('risk_id');
            $table->boolean('is_new_finding')->default(false)->after('risk_owner');
            $table->decimal('loss_amount', 18, 2)->default(0)->after('severity');

            $table->foreign('risk_id')->references('id')->on('risks')->nullOnDelete();

            $table->index('risk_owner', 'risk_incidents_risk_owner_idx');
            $table->index(['occurred_at', 'loss_amount'], 'risk_incidents_occurred_loss_idx');
            $table->index('is_new_finding', 'risk_incidents_is_new_finding_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $unlinkedCount = DB::table('risk_incidents')->whereNull('risk_id')->count();
        if ($unlinkedCount > 0) {
            throw new RuntimeException(sprintf(
                'Cannot roll back: %d risk incident(s) have no linked risk. Link or archive them before restoring the NOT NULL constraint on risk_incidents.risk_id.',
                $unlinkedCount
            ));
        }

        Schema::table('risk_incidents', function (Blueprint $table) {
            $table->dropForeign(['risk_id']);
        });

        Schema::table('risk_incidents', function (Blueprint $table) {
            $table->dropIndex('risk_incidents_risk_owner_idx');
            $table->dropIndex('risk_incidents_occurred_loss_idx');
            $table->dropIndex('risk_incidents_is_new_finding_idx');
            $table->dropColumn(['risk_owner', 'is_new_finding', 'loss_amount']);

            $table->unsignedBigInteger('risk_id')->nullable(false)->change();

            $table->foreign('risk_id')->references('id')->on('risks')->cascadeOnDelete();
        });
    }
};
