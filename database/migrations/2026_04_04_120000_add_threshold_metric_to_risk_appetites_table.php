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
        if (!Schema::hasColumn('risk_appetites', 'threshold_metric')) {
            Schema::table('risk_appetites', function (Blueprint $table) {
                $table->string('threshold_metric', 64)->nullable()->after('risk_category');
            });
        }

        DB::table('risk_appetites')
            ->select('id', 'unit', 'appetite_limit_value', 'tolerance_limit_value')
            ->orderBy('id')
            ->get()
            ->each(function ($row) {
                DB::table('risk_appetites')
                    ->where('id', $row->id)
                    ->update([
                        'threshold_metric' => $this->guessThresholdMetric($row),
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('risk_appetites', 'threshold_metric')) {
            Schema::table('risk_appetites', function (Blueprint $table) {
                $table->dropColumn('threshold_metric');
            });
        }
    }

    private function guessThresholdMetric(object $row): string
    {
        $hasNumericThreshold = !is_null($row->appetite_limit_value) || !is_null($row->tolerance_limit_value);
        if (!$hasNumericThreshold) {
            return 'qualitative_only';
        }

        $unit = strtoupper(trim((string) ($row->unit ?? '')));
        if ($unit === '') {
            return 'qualitative_only';
        }

        if (str_contains($unit, 'SCORE')) {
            return 'residual_risk_score';
        }

        foreach (['IDR', 'RP', 'RUPIAH', 'USD', 'EUR', 'GBP', 'JPY', 'CNY', 'AUD', 'SGD'] as $currencyUnit) {
            if (str_contains($unit, $currencyUnit)) {
                return 'residual_potential_loss';
            }
        }

        return 'qualitative_only';
    }
};
