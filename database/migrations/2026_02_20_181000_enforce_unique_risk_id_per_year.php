<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnforceUniqueRiskIdPerYear extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->repairDuplicateRiskIds();

        Schema::table('risks', function (Blueprint $table) {
            $table->unique(['year', 'risk_id'], 'risks_year_risk_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('risks', function (Blueprint $table) {
            $table->dropUnique('risks_year_risk_id_unique');
        });
    }

    private function repairDuplicateRiskIds(): void
    {
        $duplicates = DB::table('risks')
            ->select('year', 'risk_id')
            ->whereNotNull('year')
            ->whereNotNull('risk_id')
            ->groupBy('year', 'risk_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $maxNumberByYear = [];

        foreach ($duplicates as $duplicate) {
            $year = (int) $duplicate->year;
            $riskId = (string) $duplicate->risk_id;

            $ids = DB::table('risks')
                ->where('year', $year)
                ->where('risk_id', $riskId)
                ->orderBy('id')
                ->pluck('id')
                ->all();

            if (count($ids) <= 1) {
                continue;
            }

            $idsToReassign = array_slice($ids, 1);

            foreach ($idsToReassign as $id) {
                $newRiskId = $this->nextRiskIdForYear($year, $maxNumberByYear);

                DB::table('risks')
                    ->where('id', $id)
                    ->update(['risk_id' => $newRiskId]);
            }
        }
    }

    private function nextRiskIdForYear(int $year, array &$maxNumberByYear): string
    {
        if (!isset($maxNumberByYear[$year])) {
            $max = 0;
            $riskIds = DB::table('risks')
                ->where('year', $year)
                ->whereNotNull('risk_id')
                ->pluck('risk_id');

            foreach ($riskIds as $riskId) {
                if (preg_match('/^RISK\-' . preg_quote((string) $year, '/') . '\-(\d+)$/', (string) $riskId, $matches) !== 1) {
                    continue;
                }

                $number = (int) $matches[1];
                if ($number > $max) {
                    $max = $number;
                }
            }

            $maxNumberByYear[$year] = $max;
        }

        do {
            $maxNumberByYear[$year]++;
            $pad = max(3, strlen((string) $maxNumberByYear[$year]));
            $candidate = sprintf('RISK-%d-%0' . $pad . 'd', $year, $maxNumberByYear[$year]);
            $exists = DB::table('risks')
                ->where('year', $year)
                ->where('risk_id', $candidate)
                ->exists();
        } while ($exists);

        return $candidate;
    }
}
