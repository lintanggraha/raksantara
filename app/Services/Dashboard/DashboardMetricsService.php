<?php

namespace App\Services\Dashboard;

use App\Kri;
use App\KriLog;
use App\Risk;
use App\RiskCategory;
use App\RiskCommitteeDecision;
use App\RiskIncident;
use App\RiskScoreMatrix;
use App\Support\RiskAccess;
use App\Support\RiskAppetiteSql;
use App\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * Database-side aggregates for the main GRC dashboard. Every query is scoped
 * to what the viewer may see (RiskAccess) and to the dashboard filters.
 */
class DashboardMetricsService
{
    public const LEVELS = ['High', 'Moderate to High', 'Moderate', 'Low to Moderate', 'Low'];

    private const OPEN_INCIDENT_ACTIONS = ['open', 'in_progress'];
    private const OPEN_INCIDENT_STATUSES = ['open', 'investigating'];
    private const ASSURANCE_HORIZON_DAYS = 30;

    private CarbonImmutable $now;

    public function __construct(
        private readonly User $user,
        private readonly DashboardFilters $filters,
        ?CarbonImmutable $now = null,
    ) {
        $this->now = $now ?? CarbonImmutable::now();
    }

    // ---------------------------------------------------------------------
    // Sections
    // ---------------------------------------------------------------------

    /**
     * A1–A5 status tiles plus data-quality facts for the header.
     */
    public function summary(): array
    {
        $window = $this->filters->window($this->now);
        $today = $this->now->toDateString();

        $appetite = $this->appetiteCounts();

        $kriCounts = Kri::query()
            ->whereIn('risk_id', $this->riskIds())
            ->selectRaw("CASE WHEN current_value IS NULL THEN 'no_data' ELSE LOWER(status) END as status_key, COUNT(*) as total")
            ->groupBy('status_key')
            ->pluck('total', 'status_key');

        $overdueTreatments = DB::table('risk_treatments')
            ->whereIn('risk_id', $this->riskIds())
            ->where('deadline', '<', $today)
            ->where(fn ($q) => $q->whereNull('status')->orWhere('status', '<>', 'Completed'))
            ->count();
        $overdueIncidentActions = DB::table('risk_incident_actions')
            ->whereIn('risk_incident_id', $this->incidentIds())
            ->whereIn('status', self::OPEN_INCIDENT_ACTIONS)
            ->where('due_at', '<', $today)
            ->count();
        $overdueDecisions = $this->decisionBase()
            ->where('due_at', '<', $today)
            ->where('status', '<>', 'closed')
            ->count();

        $lossCurrent = $this->lossBetween($window['start'], $window['end']);
        $lossPrevious = $this->lossBetween($window['prev_start'], $window['prev_end']);

        $openIncidents = RiskIncident::query()
            ->whereIn('id', $this->incidentIds())
            ->whereIn(DB::raw('LOWER(status)'), self::OPEN_INCIDENT_STATUSES)
            ->selectRaw("COUNT(*) as total, SUM(CASE WHEN severity IN ('Critical', 'High') THEN 1 ELSE 0 END) as severe")
            ->first();
        $reportedCurrent = RiskIncident::query()
            ->whereIn('id', $this->incidentIds())
            ->whereBetween('occurred_at', [$window['start'], $window['end']])
            ->count();
        $reportedPrevious = RiskIncident::query()
            ->whereIn('id', $this->incidentIds())
            ->whereBetween('occurred_at', [$window['prev_start'], $window['prev_end']])
            ->count();

        $activeRisks = $this->riskBase()->count();
        $withoutResidual = $this->riskBase()
            ->whereNotExists(fn ($q) => $q->selectRaw('1')->from('risk_residual_analyses')->whereColumn('risk_residual_analyses.risk_id', 'risks.id'))
            ->count();
        $withoutTreatment = $this->riskBase()
            ->whereNotExists(fn ($q) => $q->selectRaw('1')->from('risk_treatments')->whereColumn('risk_treatments.risk_id', 'risks.id'))
            ->count();

        return [
            'period' => ['label' => $window['label'], 'previous_label' => $window['prev_label'], 'key' => $this->filters->period],
            'appetite' => $appetite + ['previous_breach' => $this->previousSnapshotBreaches()],
            'kri' => [
                'red' => (int) ($kriCounts['red'] ?? 0),
                'amber' => (int) ($kriCounts['amber'] ?? 0),
                'green' => (int) ($kriCounts['green'] ?? 0),
                'no_data' => (int) ($kriCounts['no_data'] ?? 0),
                'total' => (int) $kriCounts->sum(),
                'stale' => $this->staleKriCount(),
            ],
            'overdue' => [
                'total' => $overdueTreatments + $overdueIncidentActions + $overdueDecisions,
                'treatments' => $overdueTreatments,
                'incident_actions' => $overdueIncidentActions,
                'committee_decisions' => $overdueDecisions,
            ],
            'loss' => [
                'amount' => $lossCurrent['amount'],
                'events' => $lossCurrent['events'],
                'previous_amount' => $lossPrevious['amount'],
                'previous_events' => $lossPrevious['events'],
            ],
            'incidents' => [
                'open' => (int) ($openIncidents->total ?? 0),
                'open_severe' => (int) ($openIncidents->severe ?? 0),
                'reported' => $reportedCurrent,
                'previous_reported' => $reportedPrevious,
            ],
            'data_quality' => [
                'active_risks' => $activeRisks,
                'without_residual' => $withoutResidual,
                'without_treatment' => $withoutTreatment,
            ],
        ];
    }

    /**
     * B: exceptions that need follow-up, most severe and oldest first.
     */
    public function attention(int $limit = 15, int $max = 60): array
    {
        $today = $this->now->startOfDay();
        $items = collect();

        $appetiteRows = $this->appetiteRiskQuery()
            ->whereRaw('(' . RiskAppetiteSql::statusExpression() . ') IN (?, ?)', [RiskAppetiteSql::BREACH, RiskAppetiteSql::WARNING])
            ->selectRaw('risks.id, risks.risk_id as code, risks.risk_title as title, risks.risk_owner as owner, rr.residual_risk_score as residual, (' . RiskAppetiteSql::statusExpression() . ') as appetite_status')
            ->orderByDesc('rr.residual_risk_score')
            ->limit($limit)
            ->get();
        foreach ($appetiteRows as $row) {
            $breach = $row->appetite_status === RiskAppetiteSql::BREACH;
            $items->push($this->attentionItem(
                type: 'appetite',
                severity: $breach ? 'critical' : 'high',
                title: $row->title,
                detail: ($breach ? 'Melewati toleransi appetite' : 'Di atas target appetite') . ($row->residual !== null ? ' · skor residual ' . (int) $row->residual : ''),
                owner: $row->owner,
                code: $row->code,
                days: null,
                url: route('risks.show', $row->id),
            ));
        }

        $redKris = Kri::query()
            ->join('risks', 'risks.id', '=', 'kris.risk_id')
            ->whereIn('kris.risk_id', $this->riskIds())
            ->whereRaw('LOWER(kris.status) = ?', ['red'])
            ->orderByDesc('kris.updated_at')
            ->limit($limit)
            ->get(['kris.id', 'kris.metric_name', 'kris.current_value', 'kris.threshold_critical', 'kris.updated_at', 'risks.id as risk_pk', 'risks.risk_id as code', 'risks.risk_owner as owner']);
        foreach ($redKris as $kri) {
            $items->push($this->attentionItem(
                type: 'kri',
                severity: 'critical',
                title: 'KRI merah: ' . $kri->metric_name,
                detail: 'Nilai ' . $this->number($kri->current_value) . ($kri->threshold_critical !== null ? ' · batas kritis ' . $this->number($kri->threshold_critical) : ''),
                owner: $kri->owner,
                code: $kri->code,
                days: null,
                url: route('risks.show', $kri->risk_pk) . '#kri',
            ));
        }

        $treatments = DB::table('risk_treatments')
            ->join('risks', 'risks.id', '=', 'risk_treatments.risk_id')
            ->whereIn('risk_treatments.risk_id', $this->riskIds())
            ->where('risk_treatments.deadline', '<', $today->toDateString())
            ->where(fn ($q) => $q->whereNull('risk_treatments.status')->orWhere('risk_treatments.status', '<>', 'Completed'))
            ->orderBy('risk_treatments.deadline')
            ->limit($limit)
            ->get(['risk_treatments.description', 'risk_treatments.option', 'risk_treatments.pic', 'risk_treatments.deadline', 'risks.id as risk_pk', 'risks.risk_id as code', 'risks.risk_owner as owner']);
        foreach ($treatments as $treatment) {
            $days = (int) CarbonImmutable::parse($treatment->deadline)->startOfDay()->diffInDays($today);
            $items->push($this->attentionItem(
                type: 'treatment',
                severity: $days > 30 ? 'high' : 'medium',
                title: 'Treatment terlambat: ' . $this->truncate($treatment->description ?: $treatment->option, 90),
                detail: 'PIC ' . ($treatment->pic ?: '-'),
                owner: $treatment->owner,
                code: $treatment->code,
                days: $days,
                url: route('risks.show', $treatment->risk_pk),
            ));
        }

        $incidents = RiskIncident::query()
            ->whereIn('id', $this->incidentIds())
            ->whereIn(DB::raw('LOWER(status)'), self::OPEN_INCIDENT_STATUSES)
            ->whereIn('severity', ['Critical', 'High'])
            ->orderBy('occurred_at')
            ->limit($limit)
            ->get(['id', 'incident_code', 'title', 'severity', 'occurred_at', 'risk_owner', 'loss_amount']);
        foreach ($incidents as $incident) {
            $items->push($this->attentionItem(
                type: 'incident',
                severity: $incident->severity === 'Critical' ? 'critical' : 'high',
                title: 'Insiden ' . ($incident->severity === 'Critical' ? 'kritis' : 'tinggi') . ' terbuka: ' . $incident->title,
                detail: (float) $incident->loss_amount > 0 ? 'Loss ' . $this->rupiah((float) $incident->loss_amount) : 'Belum ada nilai loss',
                owner: $incident->risk_owner,
                code: $incident->incident_code,
                days: $incident->occurred_at ? (int) $incident->occurred_at->startOfDay()->diffInDays($today) : null,
                url: route('incidents.learning.show', $incident->id),
            ));
        }

        $decisions = $this->decisionBase()
            ->where('due_at', '<', $today->toDateString())
            ->where('status', '<>', 'closed')
            ->orderBy('due_at')
            ->limit($limit)
            ->get(['id', 'risk_id', 'title', 'owner_name', 'due_at']);
        foreach ($decisions as $decision) {
            $items->push($this->attentionItem(
                type: 'decision',
                severity: 'high',
                title: 'Keputusan komite belum ditindaklanjuti: ' . $decision->title,
                detail: 'Penanggung jawab ' . ($decision->owner_name ?: '-'),
                owner: $decision->owner_name,
                code: null,
                days: (int) CarbonImmutable::parse($decision->due_at)->startOfDay()->diffInDays($today),
                url: $decision->risk_id ? route('risks.show', $decision->risk_id) : route('enterprise-risk-profile.index'),
            ));
        }

        $rank = ['critical' => 0, 'high' => 1, 'medium' => 2];
        $sorted = $items
            ->sort(fn ($a, $b) => [$rank[$a['severity']], -($a['days'] ?? -1)] <=> [$rank[$b['severity']], -($b['days'] ?? -1)])
            ->values();

        return [
            'total' => $sorted->count(),
            'counts' => $sorted->countBy('type')->all(),
            // Up to $limit per type, so the client can filter by type without
            // losing items; it shows the first $limit of whatever is selected.
            'items' => $sorted->take($max)->all(),
        ];
    }

    /**
     * C: inherent and residual 5x5 heatmaps (probability rows, impact columns).
     */
    public function heatmap(): array
    {
        $levels = $this->cellLevels();

        $build = function (string $table, string $alias) use ($levels): array {
            $rows = DB::table($table . ' as ' . $alias)
                ->whereIn($alias . '.risk_id', $this->riskIds())
                ->whereBetween($alias . '.probability_score', [1, 5])
                ->whereBetween($alias . '.impact_score', [1, 5])
                ->selectRaw($alias . '.probability_score as p, ' . $alias . '.impact_score as i, COUNT(*) as total')
                ->groupBy('p', 'i')
                ->get();

            $cells = [];
            foreach ($rows as $row) {
                $cells[(int) $row->p][(int) $row->i] = (int) $row->total;
            }

            $matrix = [];
            for ($p = 5; $p >= 1; $p--) {
                $line = [];
                for ($i = 1; $i <= 5; $i++) {
                    $line[] = ['p' => $p, 'i' => $i, 'count' => $cells[$p][$i] ?? 0, 'level' => $levels[$p][$i]];
                }
                $matrix[] = $line;
            }

            return ['rows' => $matrix, 'total' => array_sum(array_map('array_sum', $cells))];
        };

        return [
            'inherent' => $build('risk_inherent_analyses', 'ia'),
            'residual' => $build('risk_residual_analyses', 'ra2'),
        ];
    }

    /**
     * Drill-down: risks in one heatmap cell.
     */
    public function heatmapCell(string $kind, int $probability, int $impact, int $limit = 50): array
    {
        $table = $kind === 'inherent' ? 'risk_inherent_analyses' : 'risk_residual_analyses';
        $scoreColumn = $kind === 'inherent' ? 'inherent_risk_score' : 'residual_risk_score';

        $risks = $this->riskBase()
            ->join($table . ' as cell', 'cell.risk_id', '=', 'risks.id')
            ->where('cell.probability_score', $probability)
            ->where('cell.impact_score', $impact)
            ->orderByDesc('cell.' . $scoreColumn)
            ->limit($limit)
            ->get(['risks.id', 'risks.risk_id as code', 'risks.risk_title as title', 'risks.risk_owner as owner', 'cell.' . $scoreColumn . ' as score']);

        return [
            'kind' => $kind,
            'probability' => $probability,
            'impact' => $impact,
            'items' => $risks->map(fn ($risk) => [
                'code' => $risk->code,
                'title' => $risk->title,
                'owner' => $risk->owner,
                'score' => $risk->score !== null ? (int) $risk->score : null,
                'url' => route('risks.show', $risk->id),
            ])->all(),
        ];
    }

    /**
     * D: residual risk level distribution per category (5 grades).
     */
    public function profile(): array
    {
        $levelExpression = "COALESCE(rr.risk_level, ir.risk_level, 'Belum dinilai')";

        $rows = $this->riskBase()
            ->leftJoin('risk_residual_analyses as rr', 'rr.risk_id', '=', 'risks.id')
            ->leftJoin('risk_inherent_analyses as ir', 'ir.risk_id', '=', 'risks.id')
            ->leftJoin('risk_categories as rc', 'rc.id', '=', 'risks.risk_category_id')
            ->selectRaw("COALESCE(rc.name, 'Tanpa kategori') as category, {$levelExpression} as level, COUNT(*) as total")
            ->groupBy('category', 'level')
            ->get();

        $levels = array_merge(self::LEVELS, ['Belum dinilai']);
        $categories = [];
        foreach ($rows as $row) {
            $level = in_array($row->level, $levels, true) ? $row->level : 'Belum dinilai';
            $categories[$row->category] ??= array_fill_keys($levels, 0);
            $categories[$row->category][$level] += (int) $row->total;
        }

        $result = collect($categories)
            ->map(fn ($counts, $name) => ['category' => $name, 'counts' => $counts, 'total' => array_sum($counts)])
            ->sortByDesc(fn ($row) => [$row['counts']['High'], $row['counts']['Moderate to High'], $row['total']])
            ->values()
            ->all();

        return ['levels' => $levels, 'categories' => $result];
    }

    /**
     * E: top residual risks with appetite, treatment and KRI context.
     */
    public function topRisks(int $limit = 10): array
    {
        $today = $this->now->toDateString();

        $rows = $this->appetiteRiskQuery()
            ->leftJoin('risk_inherent_analyses as ir', 'ir.risk_id', '=', 'risks.id')
            ->select([
                'risks.id', 'risks.risk_id', 'risks.risk_title', 'risks.risk_owner',
                DB::raw('ir.inherent_risk_score as inherent_score'),
                DB::raw('rr.residual_risk_score as residual_score'),
                DB::raw('(' . RiskAppetiteSql::statusExpression() . ') as appetite_status'),
            ])
            ->withCount([
                'treatments as treatments_total',
                'treatments as treatments_completed' => fn ($q) => $q->where('status', 'Completed'),
                'treatments as treatments_overdue' => fn ($q) => $q->where('deadline', '<', $today)
                    ->where(fn ($s) => $s->whereNull('status')->orWhere('status', '<>', 'Completed')),
                'kris as kris_red' => fn ($q) => $q->whereRaw('LOWER(status) = ?', ['red']),
                'kris as kris_amber' => fn ($q) => $q->whereRaw('LOWER(status) = ?', ['amber']),
            ])
            ->orderByRaw('COALESCE(rr.residual_risk_score, ir.inherent_risk_score, risks.risk_score, 0) DESC')
            ->orderBy('risks.id')
            ->limit($limit)
            ->get();

        return [
            'items' => $rows->map(fn ($risk) => [
                'code' => $risk->risk_id,
                'title' => $risk->risk_title,
                'owner' => $risk->risk_owner,
                'inherent' => $risk->inherent_score !== null ? (int) $risk->inherent_score : null,
                'residual' => $risk->residual_score !== null ? (int) $risk->residual_score : null,
                'level' => RiskScoreMatrix::getRiskLevel($risk->residual_score ?? $risk->inherent_score),
                'appetite' => $risk->appetite_status,
                'treatments' => [
                    'total' => (int) $risk->treatments_total,
                    'completed' => (int) $risk->treatments_completed,
                    'overdue' => (int) $risk->treatments_overdue,
                ],
                'kri' => ['red' => (int) $risk->kris_red, 'amber' => (int) $risk->kris_amber],
                'url' => route('risks.show', $risk->id),
            ])->all(),
        ];
    }

    /**
     * F: KRIs that are red, amber or stale, with a value history for sparklines.
     */
    public function kriWatchlist(int $limit = 8, int $points = 12): array
    {
        $staleIds = $this->staleKriQuery()->pluck('kris.id')->all();

        $kris = Kri::query()
            ->join('risks', 'risks.id', '=', 'kris.risk_id')
            ->whereIn('kris.risk_id', $this->riskIds())
            ->whereNotNull('kris.current_value')
            ->where(function ($q) use ($staleIds) {
                $q->whereRaw('LOWER(kris.status) IN (?, ?)', ['red', 'amber']);
                if (!empty($staleIds)) {
                    $q->orWhereIn('kris.id', $staleIds);
                }
            })
            ->orderByRaw("CASE LOWER(kris.status) WHEN 'red' THEN 0 WHEN 'amber' THEN 1 ELSE 2 END")
            ->orderByDesc('kris.updated_at')
            ->limit($limit)
            ->get([
                'kris.id', 'kris.metric_name', 'kris.status', 'kris.current_value', 'kris.threshold_warn',
                'kris.threshold_critical', 'kris.threshold_type', 'kris.source_updated_at', 'kris.updated_at',
                'risks.id as risk_pk', 'risks.risk_id as code', 'risks.risk_owner as owner',
            ]);

        $history = KriLog::query()
            ->whereIn('kri_id', $kris->pluck('id'))
            ->where('created_at', '>=', $this->now->subYear())
            ->orderBy('kri_id')
            ->orderBy('created_at')
            ->get(['kri_id', 'recorded_value', 'created_at'])
            ->groupBy('kri_id')
            ->map(fn ($logs) => $logs->slice(-$points)->map(fn ($log) => [
                'value' => (float) $log->recorded_value,
                'at' => optional($log->created_at)->toDateString(),
            ])->values()->all());

        return [
            'stale_total' => count($staleIds),
            'no_data_total' => Kri::query()->whereIn('risk_id', $this->riskIds())->whereNull('current_value')->count(),
            'items' => $kris->map(fn ($kri) => [
                'metric' => $kri->metric_name,
                'status' => ucfirst(strtolower((string) $kri->status)),
                'stale' => in_array($kri->id, $staleIds, true),
                'value' => $kri->current_value !== null ? (float) $kri->current_value : null,
                'warn' => $kri->threshold_warn !== null ? (float) $kri->threshold_warn : null,
                'critical' => $kri->threshold_critical !== null ? (float) $kri->threshold_critical : null,
                'direction' => $kri->threshold_type,
                'updated_at' => optional($kri->source_updated_at ?? $kri->updated_at)->toIso8601String(),
                'code' => $kri->code,
                'owner' => $kri->owner,
                'history' => $history->get($kri->id, []),
                'url' => route('risks.show', $kri->risk_pk) . '#kri',
            ])->all(),
        ];
    }

    /**
     * G: monthly loss events over the last 12 months and the largest events.
     */
    public function losses(int $months = 12): array
    {
        $start = $this->now->startOfMonth()->subMonthsNoOverflow($months - 1);
        $monthExpression = $this->monthExpression('occurred_at');

        $rows = RiskIncident::query()
            ->whereIn('id', $this->incidentIds())
            ->where('occurred_at', '>=', $start)
            ->selectRaw("{$monthExpression} as month_key, COALESCE(SUM(loss_amount), 0) as amount, COUNT(*) as events")
            ->groupBy('month_key')
            ->get()
            ->keyBy('month_key');

        $series = [];
        for ($cursor = $start; $cursor <= $this->now; $cursor = $cursor->addMonthNoOverflow()) {
            $key = $cursor->format('Y-m');
            $series[] = [
                'month' => $key,
                'label' => $cursor->locale('id')->translatedFormat('M y'),
                'amount' => (float) ($rows[$key]->amount ?? 0),
                'events' => (int) ($rows[$key]->events ?? 0),
            ];
        }

        $largest = RiskIncident::query()
            ->whereIn('id', $this->incidentIds())
            ->where('occurred_at', '>=', $start)
            ->where('loss_amount', '>', 0)
            ->orderByDesc('loss_amount')
            ->limit(5)
            ->get(['id', 'incident_code', 'title', 'loss_amount', 'occurred_at', 'severity']);

        return [
            'series' => $series,
            'total_amount' => array_sum(array_column($series, 'amount')),
            'total_events' => array_sum(array_column($series, 'events')),
            'largest' => $largest->map(fn ($incident) => [
                'code' => $incident->incident_code,
                'title' => $incident->title,
                'amount' => (float) $incident->loss_amount,
                'severity' => $incident->severity,
                'date' => optional($incident->occurred_at)->toDateString(),
                'url' => route('incidents.learning.show', $incident->id),
            ])->all(),
        ];
    }

    /**
     * H: treatment progress per unit and committee decision follow-up.
     */
    public function actions(int $units = 10): array
    {
        $today = $this->now->toDateString();
        $bucket = "CASE WHEN risk_treatments.status = 'Completed' THEN 'completed'"
            . " WHEN risk_treatments.deadline < '{$today}' THEN 'overdue'"
            . " WHEN risk_treatments.status = 'Blocked' THEN 'blocked'"
            . " ELSE 'on_track' END";

        $rows = DB::table('risk_treatments')
            ->join('risks', 'risks.id', '=', 'risk_treatments.risk_id')
            ->whereIn('risk_treatments.risk_id', $this->riskIds())
            ->selectRaw("risks.risk_owner as owner, {$bucket} as bucket, COUNT(*) as total")
            ->groupBy('owner', 'bucket')
            ->get();

        $byUnit = [];
        foreach ($rows as $row) {
            $byUnit[$row->owner] ??= ['completed' => 0, 'on_track' => 0, 'blocked' => 0, 'overdue' => 0];
            $byUnit[$row->owner][$row->bucket] = (int) $row->total;
        }

        $unitRows = collect($byUnit)
            ->map(fn ($counts, $owner) => ['owner' => $owner, 'counts' => $counts, 'total' => array_sum($counts)])
            ->sortByDesc(fn ($row) => [$row['counts']['overdue'], $row['counts']['blocked'], $row['total']])
            ->values();

        $decisionCounts = $this->decisionBase()
            ->selectRaw("CASE WHEN status = 'closed' THEN 'closed' WHEN due_at IS NOT NULL AND due_at < ? THEN 'overdue' ELSE 'open' END as bucket, COUNT(*) as total", [$today])
            ->groupBy('bucket')
            ->pluck('total', 'bucket');

        $nextDecisions = $this->decisionBase()
            ->where('status', '<>', 'closed')
            ->orderByRaw('CASE WHEN due_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_at')
            ->limit(5)
            ->get(['id', 'risk_id', 'title', 'owner_name', 'due_at', 'status']);

        return [
            'units' => $unitRows->take($units)->all(),
            'units_total' => $unitRows->count(),
            'totals' => [
                'completed' => $unitRows->sum('counts.completed'),
                'on_track' => $unitRows->sum('counts.on_track'),
                'blocked' => $unitRows->sum('counts.blocked'),
                'overdue' => $unitRows->sum('counts.overdue'),
            ],
            'decisions' => [
                'open' => (int) ($decisionCounts['open'] ?? 0),
                'overdue' => (int) ($decisionCounts['overdue'] ?? 0),
                'closed' => (int) ($decisionCounts['closed'] ?? 0),
                'next' => $nextDecisions->map(fn ($decision) => [
                    'title' => $decision->title,
                    'owner' => $decision->owner_name,
                    'due_at' => optional($decision->due_at)->toDateString() ?? ($decision->due_at ? (string) $decision->due_at : null),
                    'overdue' => $decision->due_at && CarbonImmutable::parse($decision->due_at)->toDateString() < $today,
                    'url' => $decision->risk_id ? route('risks.show', $decision->risk_id) : route('enterprise-risk-profile.index'),
                ])->all(),
            ],
        ];
    }

    /**
     * I: assurance work due in the next 30 days or already overdue.
     */
    public function assurance(int $limit = 10): array
    {
        $today = $this->now->toDateString();
        $horizon = $this->now->addDays(self::ASSURANCE_HORIZON_DAYS)->toDateString();
        $items = collect();


        $vendors = $this->vendorBase()
            ->where('status', 'Active')
            ->whereNotNull('next_review_at')
            ->where('next_review_at', '<=', $horizon)
            ->orderBy('next_review_at')
            ->get(['id', 'name', 'next_review_at as due']);
        foreach ($vendors as $row) {
            $items->push($this->assuranceItem('vendor', 'Review vendor', $row->name, $row->due, route('vendors.show', $row->id)));
        }

        // Business continuity is managed per service; the catalogue is small, so
        // test due dates (which depend on each plan's frequency) are derived here.
        $services = \App\BusinessService::query()
            ->where('status', 'active')
            ->with(['bia:id,business_service_id,next_review_at', 'bcp:id,business_service_id,testing_frequency', 'latestTest'])
            ->tap(fn ($q) => \App\Support\ServiceAccess::applyVisibilityScope($q, $this->user));
        if ($this->filters->owner !== null) {
            $services->where('owner_unit', $this->filters->owner);
        }
        if ($this->filters->categoryId !== null) {
            $services->whereHas('risks', fn ($q) => $q->where('risks.risk_category_id', $this->filters->categoryId));
        }
        foreach ($services->get(['id', 'code', 'name']) as $service) {
            $url = route('bcm.services.show', $service->id);
            if ($service->bia && $service->bia->next_review_at && $service->bia->next_review_at->toDateString() <= $horizon) {
                $items->push($this->assuranceItem('bia', 'Review BIA', $service->code . ' · ' . $service->name, $service->bia->next_review_at, $url . '#bia'));
            }
            if ($service->bcp) {
                $due = $service->latestTest
                    ? \App\Support\BusinessContinuity::nextTestDue($service->bcp->testing_frequency, $service->latestTest->tested_at)
                    : null;
                if ($due === null || $due->toDateString() <= $horizon) {
                    $items->push($this->assuranceItem('bcp', $due ? 'Uji BCP' : 'BCP belum pernah diuji', $service->code . ' · ' . $service->name, $due, $url . '#uji'));
                }
            }
        }

        $sorted = $items->sortBy(fn ($item) => $item['due'] ?? '0000-00-00')->values();

        return [
            'horizon_days' => self::ASSURANCE_HORIZON_DAYS,
            'counts' => [
                'overdue' => $sorted->where('overdue', true)->count(),
                'upcoming' => $sorted->where('overdue', false)->count(),
            ],
            'by_type' => $sorted->countBy('type')->all(),
            'items' => $sorted->take($limit)->all(),
        ];
    }

    /**
     * J: month-end posture history from risk_score_snapshots. Empty until the
     * first snapshot has been taken; history is never reconstructed.
     */
    public function trend(int $months = 12): array
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('risk_score_snapshots')) {
            return ['series' => [], 'first_period' => null];
        }

        $from = $this->now->startOfMonth()->subMonthsNoOverflow($months - 1)->toDateString();
        $rows = DB::table('risk_score_snapshots')
            ->whereIn('risk_id', $this->riskIds(false))
            ->where('period', '>=', $from)
            ->selectRaw(
                'period, COUNT(*) as risks, AVG(residual_score) as avg_residual,'
                . " SUM(CASE WHEN appetite_status = ? THEN 1 ELSE 0 END) as breaches,"
                . " SUM(CASE WHEN residual_level IN ('High', 'Moderate to High') THEN 1 ELSE 0 END) as high_risks",
                [RiskAppetiteSql::BREACH]
            )
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'first_period' => DB::table('risk_score_snapshots')->min('period'),
            'series' => $rows->map(fn ($row) => [
                'period' => (string) $row->period,
                'label' => CarbonImmutable::parse($row->period)->locale('id')->translatedFormat('M y'),
                'risks' => (int) $row->risks,
                'avg_residual' => $row->avg_residual !== null ? round((float) $row->avg_residual, 1) : null,
                'breaches' => (int) $row->breaches,
                'high_risks' => (int) $row->high_risks,
            ])->all(),
        ];
    }

    /**
     * Filter options limited to what the viewer can see.
     */
    public function filterOptions(): array
    {
        $owners = Risk::query()
            ->tap(fn ($q) => RiskAccess::applyVisibilityScope($q, $this->user, 'risks.risk_owner', 'risks.created_by'))
            ->whereNotNull('risk_owner')
            ->distinct()
            ->orderBy('risk_owner')
            ->pluck('risk_owner')
            ->all();

        return [
            'owners' => $owners,
            'categories' => RiskCategory::query()->orderBy('name')->get(['id', 'name'])->all(),
        ];
    }

    // ---------------------------------------------------------------------
    // Scoped base queries
    // ---------------------------------------------------------------------

    /**
     * Risks the viewer may see, narrowed by the dashboard filters. Closed risks
     * are excluded unless $activeOnly is false.
     */
    public function riskBase(bool $activeOnly = true): Builder
    {
        $query = Risk::query();
        RiskAccess::applyVisibilityScope($query, $this->user, 'risks.risk_owner', 'risks.created_by');

        if ($this->filters->owner !== null) {
            $query->where('risks.risk_owner', $this->filters->owner);
        }
        if ($this->filters->categoryId !== null) {
            $query->where('risks.risk_category_id', $this->filters->categoryId);
        }
        if ($activeOnly) {
            $query->whereRaw("LOWER(COALESCE(risks.status, 'open')) <> 'closed'");
        }

        return $query;
    }

    private function riskIds(bool $activeOnly = true): QueryBuilder
    {
        return $this->riskBase($activeOnly)->select('risks.id')->toBase();
    }

    private function appetiteRiskQuery(): Builder
    {
        return RiskAppetiteSql::joinSources($this->riskBase());
    }

    private function appetiteCounts(): array
    {
        $counts = $this->appetiteRiskQuery()
            ->selectRaw('(' . RiskAppetiteSql::statusExpression() . ') as appetite_status, COUNT(*) as total')
            ->groupBy('appetite_status')
            ->pluck('total', 'appetite_status');

        $breach = (int) ($counts[RiskAppetiteSql::BREACH] ?? 0);
        $warning = (int) ($counts[RiskAppetiteSql::WARNING] ?? 0);
        $within = (int) ($counts[RiskAppetiteSql::WITHIN] ?? 0);

        return [
            'breach' => $breach,
            'warning' => $warning,
            'within' => $within,
            'evaluated' => $breach + $warning + $within,
            'not_evaluated' => (int) ($counts[RiskAppetiteSql::QUALITATIVE] ?? 0) + (int) ($counts[RiskAppetiteSql::UNCONFIGURED] ?? 0),
        ];
    }

    /**
     * Incidents the viewer may see: linked to a visible risk, or unlinked and
     * owned by one of the viewer's units (or reported by the viewer).
     */
    private function incidentIds(): QueryBuilder
    {
        $query = RiskIncident::query()->select('risk_incidents.id');
        $seesAll = $this->user->isSuperadmin() || $this->user->isComplianceDivision();
        $allowedOwners = $seesAll ? [] : array_values(array_filter($this->user->allowedRiskOwners()));

        $query->where(function (Builder $scope) use ($seesAll, $allowedOwners) {
            $scope->whereIn('risk_incidents.risk_id', $this->riskIds(false));

            if ($this->filters->categoryId !== null) {
                return; // Unlinked incidents have no category.
            }

            $scope->orWhere(function (Builder $unlinked) use ($seesAll, $allowedOwners) {
                $unlinked->whereNull('risk_incidents.risk_id');
                if ($this->filters->owner !== null) {
                    $unlinked->where('risk_incidents.risk_owner', $this->filters->owner);
                }
                if (!$seesAll) {
                    $unlinked->where(function (Builder $own) use ($allowedOwners) {
                        $own->where('risk_incidents.reported_by', $this->user->id);
                        if (!empty($allowedOwners)) {
                            $own->orWhereIn('risk_incidents.risk_owner', $allowedOwners);
                        }
                    });
                }
            });
        });

        return $query->toBase();
    }

    private function decisionBase(): Builder
    {
        $seesAll = $this->user->isSuperadmin() || $this->user->isComplianceDivision();

        return RiskCommitteeDecision::query()->where(function (Builder $scope) use ($seesAll) {
            $scope->whereIn('risk_id', $this->riskIds(false));

            if ($this->filters->owner === null && $this->filters->categoryId === null) {
                $scope->orWhere(function (Builder $unlinked) use ($seesAll) {
                    $unlinked->whereNull('risk_id');
                    if (!$seesAll) {
                        $unlinked->where('created_by', $this->user->id);
                    }
                });
            }
        });
    }

    private function vendorBase(): QueryBuilder
    {
        $query = DB::table('vendors');
        $seesAll = $this->user->isSuperadmin() || $this->user->isComplianceDivision();

        if (!$seesAll || $this->filters->owner !== null || $this->filters->categoryId !== null) {
            $query->whereIn('id', $this->riskBase()->whereNotNull('risks.vendor_id')->select('risks.vendor_id')->toBase());
        }

        return $query;
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    private function lossBetween(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $row = RiskIncident::query()
            ->whereIn('id', $this->incidentIds())
            ->whereBetween('occurred_at', [$from, $to])
            ->selectRaw('COALESCE(SUM(loss_amount), 0) as amount, COUNT(*) as events')
            ->first();

        return ['amount' => (float) ($row->amount ?? 0), 'events' => (int) ($row->events ?? 0)];
    }

    /**
     * KRIs whose source has not refreshed within its expected interval. The
     * interval differs per KRI, so it is checked per distinct interval value
     * to keep the comparison portable across database drivers.
     */
    private function staleKriQuery(): Builder
    {
        $intervals = Kri::query()
            ->whereIn('risk_id', $this->riskIds())
            ->where('expected_refresh_minutes', '>', 0)
            ->distinct()
            ->pluck('expected_refresh_minutes');

        // Only KRIs that already have a reading can go stale; those without
        // one are reported separately as "no data yet".
        $query = Kri::query()->whereIn('kris.risk_id', $this->riskIds())->whereNotNull('kris.current_value');
        if ($intervals->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $outer) use ($intervals) {
            foreach ($intervals as $minutes) {
                $outer->orWhere(function (Builder $q) use ($minutes) {
                    $q->where('kris.expected_refresh_minutes', $minutes)
                        ->whereRaw('COALESCE(kris.source_updated_at, kris.updated_at) < ?', [$this->now->subMinutes((int) $minutes)->toDateTimeString()]);
                });
            }
        });
    }

    private function staleKriCount(): int
    {
        return $this->staleKriQuery()->count();
    }

    /**
     * Breach count from the latest monthly snapshot before the current month,
     * or null while no snapshot exists yet.
     */
    private function previousSnapshotBreaches(): ?int
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('risk_score_snapshots')) {
            return null;
        }

        $period = DB::table('risk_score_snapshots')
            ->where('period', '<', $this->now->startOfMonth()->toDateString())
            ->max('period');
        if ($period === null) {
            return null;
        }

        return DB::table('risk_score_snapshots')
            ->where('period', $period)
            ->whereIn('risk_id', $this->riskIds())
            ->where('appetite_status', RiskAppetiteSql::BREACH)
            ->count();
    }

    /**
     * @return array<int, array<int, string>> level per probability/impact cell
     */
    private function cellLevels(): array
    {
        $scores = RiskScoreMatrix::query()->get(['probability_score', 'impact_score', 'risk_score'])
            ->mapWithKeys(fn ($cell) => [$cell->probability_score . ':' . $cell->impact_score => (int) $cell->risk_score]);

        $levels = [];
        for ($p = 1; $p <= 5; $p++) {
            for ($i = 1; $i <= 5; $i++) {
                $levels[$p][$i] = RiskScoreMatrix::getRiskLevel($scores[$p . ':' . $i] ?? $p * $i);
            }
        }

        return $levels;
    }

    private function monthExpression(string $column): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', {$column})",
            'pgsql' => "to_char({$column}, 'YYYY-MM')",
            'sqlsrv' => "FORMAT({$column}, 'yyyy-MM')",
            default => "DATE_FORMAT({$column}, '%Y-%m')",
        };
    }

    private function attentionItem(string $type, string $severity, string $title, ?string $detail, ?string $owner, ?string $code, ?int $days, string $url): array
    {
        return compact('type', 'severity', 'title', 'detail', 'owner', 'code', 'days', 'url');
    }

    private function assuranceItem(string $type, string $label, string $subject, $due, string $url): array
    {
        $dueDate = $due ? CarbonImmutable::parse($due)->toDateString() : null;

        return [
            'type' => $type,
            'label' => $label,
            'subject' => $subject,
            'due' => $dueDate,
            'overdue' => $dueDate === null || $dueDate < $this->now->toDateString(),
            'url' => $url,
        ];
    }

    private function number($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');
    }

    private function rupiah(float $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    private function truncate(?string $text, int $length): string
    {
        return \Illuminate\Support\Str::limit((string) $text, $length);
    }
}
