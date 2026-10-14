<?php

namespace App\Console\Commands;

use App\Risk;
use App\Support\RiskAppetiteSql;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SnapshotRiskScores extends Command
{
    protected $signature = 'risks:snapshot-scores {--period= : Month to record, as YYYY-MM (default: current month)}';

    protected $description = 'Record the current score and appetite status of every active risk for the month';

    public function handle(): int
    {
        $periodOption = (string) ($this->option('period') ?? '');
        if ($periodOption !== '' && !preg_match('/^\d{4}-\d{2}$/', $periodOption)) {
            $this->error('Use --period=YYYY-MM.');

            return self::INVALID;
        }

        $period = ($periodOption !== '' ? CarbonImmutable::createFromFormat('!Y-m', $periodOption) : CarbonImmutable::now())
            ->startOfMonth()
            ->toDateString();
        $now = now();
        $written = 0;

        RiskAppetiteSql::joinSources(Risk::query())
            ->leftJoin('risk_inherent_analyses as ir', 'ir.risk_id', '=', 'risks.id')
            ->whereRaw("LOWER(COALESCE(risks.status, 'open')) <> 'closed'")
            ->select([
                'risks.id', 'risks.risk_owner', 'risks.risk_category_id',
                DB::raw('ir.inherent_risk_score as inherent_score'),
                DB::raw('rr.residual_risk_score as residual_score'),
                DB::raw('COALESCE(rr.risk_level, ir.risk_level) as residual_level'),
                DB::raw('(' . RiskAppetiteSql::statusExpression() . ') as appetite_status'),
            ])
            ->orderBy('risks.id')
            ->chunk(500, function ($rows) use ($period, $now, &$written) {
                DB::table('risk_score_snapshots')->upsert(
                    $rows->map(fn ($row) => [
                        'period' => $period,
                        'risk_id' => $row->id,
                        'risk_owner' => $row->risk_owner,
                        'risk_category_id' => $row->risk_category_id,
                        'inherent_score' => $row->inherent_score,
                        'residual_score' => $row->residual_score,
                        'residual_level' => $row->residual_level,
                        'appetite_status' => $row->appetite_status,
                        'captured_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all(),
                    ['period', 'risk_id'],
                    ['risk_owner', 'risk_category_id', 'inherent_score', 'residual_score', 'residual_level', 'appetite_status', 'captured_at', 'updated_at']
                );
                $written += $rows->count();
            });

        $this->info("Recorded {$written} risk snapshot(s) for {$period}.");

        return self::SUCCESS;
    }
}
