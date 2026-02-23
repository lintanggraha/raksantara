<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnforceUniqueRiskAnalysisRows extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->deduplicateByRiskId('risk_inherent_analyses');
        $this->deduplicateByRiskId('risk_residual_analyses');

        Schema::table('risk_inherent_analyses', function (Blueprint $table) {
            $table->unique('risk_id', 'risk_inherent_analyses_risk_id_unique');
        });

        Schema::table('risk_residual_analyses', function (Blueprint $table) {
            $table->unique('risk_id', 'risk_residual_analyses_risk_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('risk_inherent_analyses', function (Blueprint $table) {
            $table->dropUnique('risk_inherent_analyses_risk_id_unique');
        });

        Schema::table('risk_residual_analyses', function (Blueprint $table) {
            $table->dropUnique('risk_residual_analyses_risk_id_unique');
        });
    }

    private function deduplicateByRiskId(string $table): void
    {
        $duplicateRiskIds = DB::table($table)
            ->select('risk_id')
            ->whereNotNull('risk_id')
            ->groupBy('risk_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('risk_id');

        foreach ($duplicateRiskIds as $riskId) {
            $ids = DB::table($table)
                ->where('risk_id', $riskId)
                ->orderByDesc('updated_at')
                ->orderByDesc('id')
                ->pluck('id')
                ->all();

            if (count($ids) <= 1) {
                continue;
            }

            $idsToDelete = array_slice($ids, 1);
            if (!empty($idsToDelete)) {
                DB::table($table)->whereIn('id', $idsToDelete)->delete();
            }
        }
    }
}
