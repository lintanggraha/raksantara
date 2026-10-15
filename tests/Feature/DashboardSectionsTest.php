<?php

namespace Tests\Feature;

use App\Kri;
use App\RadarGroup;
use App\Risk;
use App\RiskAppetite;
use App\RiskCategory;
use App\RiskIncident;
use App\RiskInherentAnalysis;
use App\RiskResidualAnalysis;
use App\RiskTreatment;
use App\Support\RiskAppetiteEvaluator;
use App\Support\RiskAppetiteSql;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardSectionsTest extends TestCase
{
    use RefreshDatabase;

    private const OWN = 'BT - DIGITAL BANKING';
    private const FOREIGN = 'CO - COMPLIANCE';

    public function test_every_section_responds_with_data(): void
    {
        $viewer = $this->createUser(self::OWN);
        $this->createRisk($viewer, self::OWN, 'Section Smoke Risk');

        foreach (['summary', 'portfolio', 'attention', 'heatmap', 'profile', 'top-risks', 'kris', 'losses', 'actions', 'assurance', 'trend'] as $section) {
            $this->actingAs($viewer)
                ->getJson(route('dashboard.sections', $section))
                ->assertOk()
                ->assertJsonPath('section', $section)
                ->assertJsonStructure(['section', 'generated_at', 'data']);
        }

        $this->actingAs($viewer)->getJson(route('dashboard.sections', 'unknown'))->assertNotFound();
    }

    public function test_sections_only_aggregate_records_the_viewer_may_see(): void
    {
        $viewer = $this->createUser(self::OWN);
        $foreignUser = $this->createUser(self::FOREIGN);
        $own = $this->createRisk($viewer, self::OWN, 'Own Risk', residual: [4, 4, 16]);
        $foreign = $this->createRisk($foreignUser, self::FOREIGN, 'Foreign Risk', residual: [5, 5, 25]);

        $this->createKri($own, 'Red');
        $this->createKri($foreign, 'Red');
        $this->createKri($foreign, 'Red');
        $this->createIncident($viewer, $own, 1_000_000);
        $this->createIncident($foreignUser, $foreign, 9_000_000);

        $this->actingAs($viewer);

        $summary = $this->getJson(route('dashboard.sections', 'summary'))->json('data');
        $this->assertSame(1, $summary['kri']['red']);
        $this->assertEquals(1_000_000, $summary['loss']['amount']);
        $this->assertSame(1, $summary['data_quality']['active_risks']);

        $heatmap = $this->getJson(route('dashboard.sections', 'heatmap'))->json('data');
        $this->assertSame(1, $heatmap['residual']['total']);

        $attentionCodes = collect($this->getJson(route('dashboard.sections', 'attention'))->json('data.items'))->pluck('code')->filter()->all();
        $this->assertContains($own->risk_id, $attentionCodes);
        $this->assertNotContains($foreign->risk_id, $attentionCodes);

        $topCodes = collect($this->getJson(route('dashboard.sections', 'top-risks'))->json('data.items'))->pluck('code')->all();
        $this->assertSame([$own->risk_id], $topCodes);

        $cell = $this->getJson(route('dashboard.heatmap-cell', ['kind' => 'residual', 'p' => 5, 'i' => 5]))->json('data.items');
        $this->assertSame([], $cell);
    }

    public function test_counts_are_not_capped_by_list_limits(): void
    {
        $viewer = $this->createUser(self::OWN);
        $risk = $this->createRisk($viewer, self::OWN, 'Many KRIs');
        foreach (range(1, 8) as $index) {
            $this->createKri($risk, 'Red');
        }

        $this->actingAs($viewer)
            ->getJson(route('dashboard.sections', 'summary'))
            ->assertJsonPath('data.kri.red', 8);
    }

    public function test_empty_portfolio_reports_zeros_instead_of_placeholder_figures(): void
    {
        $viewer = $this->createUser(self::OWN);

        $summary = $this->actingAs($viewer)->getJson(route('dashboard.sections', 'summary'))->json('data');

        $this->assertEquals(0, $summary['loss']['amount']);
        $this->assertSame(0, $summary['data_quality']['active_risks']);
        $this->assertSame(0, $summary['overdue']['total']);
        $this->assertSame(0, $summary['appetite']['breach']);
        $this->assertNull($summary['appetite']['previous_breach']);

        $losses = $this->getJson(route('dashboard.sections', 'losses'))->json('data');
        $this->assertCount(12, $losses['series']);
        $this->assertEquals(0, $losses['total_amount']);
    }

    public function test_owner_filter_and_overdue_actions(): void
    {
        $viewer = $this->createUser(self::FOREIGN); // Compliance sees every unit.
        $a = $this->createRisk($viewer, self::OWN, 'Unit A Risk');
        $b = $this->createRisk($viewer, self::FOREIGN, 'Unit B Risk');
        $this->createTreatment($a, now()->subDays(40)->toDateString(), 'In Progress');
        $this->createTreatment($a, now()->addDays(10)->toDateString(), 'Not Started');
        $this->createTreatment($b, now()->subDays(2)->toDateString(), 'Completed');

        $this->actingAs($viewer);
        $all = $this->getJson(route('dashboard.sections', 'summary'))->json('data');
        $this->assertSame(1, $all['overdue']['treatments']);
        $this->assertSame(2, $all['data_quality']['active_risks']);

        $filtered = $this->getJson(route('dashboard.sections', ['section' => 'summary', 'owner' => self::FOREIGN]))->json('data');
        $this->assertSame(0, $filtered['overdue']['treatments']);
        $this->assertSame(1, $filtered['data_quality']['active_risks']);

        $actions = $this->getJson(route('dashboard.sections', 'actions'))->json('data');
        $this->assertSame(1, $actions['totals']['overdue']);
        $this->assertSame(1, $actions['totals']['completed']);
        $this->assertSame(1, $actions['totals']['on_track']);
    }

    public function test_sql_appetite_status_matches_the_php_evaluator(): void
    {
        $user = $this->createUser(self::OWN);
        $scoreAppetite = $this->createAppetite(RiskAppetite::THRESHOLD_METRIC_RESIDUAL_SCORE, 10, 15);
        $lossAppetite = $this->createAppetite(RiskAppetite::THRESHOLD_METRIC_RESIDUAL_LOSS, 1_000_000, null);
        $qualitative = $this->createAppetite(RiskAppetite::THRESHOLD_METRIC_QUALITATIVE, null, null);
        $noThresholds = $this->createAppetite(RiskAppetite::THRESHOLD_METRIC_RESIDUAL_SCORE, null, null);

        $radar = RadarGroup::create(['code' => 'TQ' . Str::upper(Str::random(3)), 'description' => 'Test radar']);
        DB::table('risk_quant_profiles')->insert([
            'radar_group_id' => $radar->id,
            'potential_loss_inherent' => 0,
            'risk_appetite_score' => 12,
            'risk_appetite_loss' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $scenarios = [
            [$scoreAppetite, 20, 0, null],          // breach (score above tolerance)
            [$scoreAppetite, 12, 0, null],          // warning
            [$scoreAppetite, 8, 0, null],           // within
            [$scoreAppetite, null, null, null],     // no residual
            [$lossAppetite, 3, 2_000_000, null],    // breach on loss, tolerance falls back to appetite limit
            [$lossAppetite, 3, 500_000, null],      // within
            [$qualitative, 25, 0, null],            // qualitative only
            [$noThresholds, 20, 0, $radar->id],     // linked thresholds empty -> radar fallback breach
            [null, 10, 0, $radar->id],              // radar fallback within
            [null, 10, 0, null],                    // nothing configured
        ];

        foreach ($scenarios as $index => [$appetite, $score, $loss, $radarId]) {
            $risk = $this->createRisk($user, self::OWN, 'Appetite case ' . $index);
            $risk->update(['risk_appetite_id' => optional($appetite)->id]);
            if ($score !== null) {
                RiskResidualAnalysis::create([
                    'risk_id' => $risk->id,
                    'probability_score' => 3,
                    'impact_score' => 3,
                    'residual_risk_score' => $score,
                    'potential_loss' => $loss,
                    'radar_group_id' => $radarId,
                ]);
            }

            $expected = RiskAppetiteSql::fromEvaluatorStatus(RiskAppetiteEvaluator::evaluate($risk->fresh())['status']);
            $actual = RiskAppetiteSql::joinSources(Risk::query()->whereKey($risk->id))
                ->selectRaw('(' . RiskAppetiteSql::statusExpression() . ') as appetite_status')
                ->value('appetite_status');

            $this->assertSame($expected, $actual, "Scenario {$index} differs between PHP and SQL appetite evaluation.");
        }
    }

    public function test_monthly_snapshots_feed_the_trend_and_previous_month_comparison(): void
    {
        $viewer = $this->createUser(self::OWN);
        $foreignUser = $this->createUser(self::FOREIGN);
        $appetite = $this->createAppetite(RiskAppetite::THRESHOLD_METRIC_RESIDUAL_SCORE, 10, 15);
        $own = $this->createRisk($viewer, self::OWN, 'Snapshot Own', residual: [5, 4, 20]);
        $own->update(['risk_appetite_id' => $appetite->id]);
        $foreign = $this->createRisk($foreignUser, self::FOREIGN, 'Snapshot Foreign', residual: [5, 5, 25]);
        $foreign->update(['risk_appetite_id' => $appetite->id]);

        $previousMonth = now()->subMonthNoOverflow()->format('Y-m');
        $this->artisan('risks:snapshot-scores', ['--period' => $previousMonth])->assertSuccessful();
        $this->artisan('risks:snapshot-scores')->assertSuccessful();
        $this->artisan('risks:snapshot-scores')->assertSuccessful(); // idempotent per month

        $this->assertSame(4, DB::table('risk_score_snapshots')->count());
        $this->assertSame(RiskAppetiteSql::BREACH, DB::table('risk_score_snapshots')->where('risk_id', $own->id)->value('appetite_status'));

        $this->actingAs($viewer);
        $this->getJson(route('dashboard.sections', 'summary'))
            ->assertJsonPath('data.appetite.breach', 1)
            ->assertJsonPath('data.appetite.previous_breach', 1);

        $trend = $this->getJson(route('dashboard.sections', 'trend'))->json('data');
        $this->assertCount(2, $trend['series']);
        $this->assertSame(1, $trend['series'][0]['risks']); // only the viewer's own risk
        $this->assertSame(1, $trend['series'][1]['breaches']);

        $this->artisan('risks:snapshot-scores', ['--period' => '2026/10'])->assertExitCode(2);
    }

    public function test_chart_payloads_for_tiles_top_risks_aging_and_level_migration(): void
    {
        $viewer = $this->createUser(self::OWN);
        $appetite = $this->createAppetite(RiskAppetite::THRESHOLD_METRIC_RESIDUAL_SCORE, 10, 15);
        $worse = $this->createRisk($viewer, self::OWN, 'Level went up', residual: [5, 4, 20]);
        $worse->update(['risk_appetite_id' => $appetite->id]);
        $same = $this->createRisk($viewer, self::OWN, 'Level unchanged', residual: [4, 4, 16]);
        RiskInherentAnalysis::create(['risk_id' => $worse->id, 'probability_score' => 3, 'impact_score' => 3, 'inherent_risk_score' => 9, 'risk_level' => 'Moderate']);
        RiskInherentAnalysis::create(['risk_id' => $same->id, 'probability_score' => 5, 'impact_score' => 5, 'inherent_risk_score' => 25, 'risk_level' => 'High']);
        $this->createTreatment($worse, now()->subDays(10)->toDateString(), 'In Progress');
        $this->createTreatment($worse, now()->subDays(100)->toDateString(), 'Not Started');

        $this->actingAs($viewer);

        $history = $this->getJson(route('dashboard.sections', 'summary'))->json('data.history');
        $this->assertCount(12, $history['loss']);
        $this->assertCount(12, $history['incidents']);
        $this->assertSame([], $history['breaches']); // no snapshot yet

        $top = collect($this->getJson(route('dashboard.sections', 'top-risks'))->json('data.items'))->firstWhere('title', 'Level went up');
        $this->assertEquals(10, $top['appetite_limit']);
        $this->assertEquals(15, $top['tolerance_limit']);

        $aging = $this->getJson(route('dashboard.sections', 'actions'))->json('data.aging');
        $this->assertSame(self::OWN, $aging['units'][0]['owner']);
        $this->assertSame(['d30' => 1, 'd60' => 0, 'd90' => 0, 'd90_plus' => 1], $aging['units'][0]['counts']);
        $this->assertSame(2, array_sum($aging['totals']));

        $this->get(route('dashboard', ['view' => 'analytical']))
            ->assertOk()
            ->assertViewHas('levelMigration', function (array $migration) {
                return $migration['total'] === 2
                    && $migration['same'] === 1
                    && $migration['up'] === 1
                    && $migration['matrix']['Moderate']['High'] === 1;
            });
    }

    public function test_portfolio_counts_risks_per_division_and_incidents_per_client(): void
    {
        $viewer = $this->createUser(self::FOREIGN); // Compliance sees every unit.
        $own = $this->createRisk($viewer, self::OWN, 'Portfolio Own');
        $this->createRisk($viewer, self::OWN, 'Portfolio Own Closed')->update(['status' => 'Closed']);
        $this->createRisk($viewer, self::FOREIGN, 'Portfolio Foreign');
        $this->createIncident($viewer, $own, 0)->update(['affected_client' => 'BANK A']);
        $this->createIncident($viewer, $own, 0)->update(['affected_client' => 'BANK A', 'status' => 'Closed']);
        $this->createIncident($viewer, $own, 0);

        $this->actingAs($viewer);
        $data = $this->getJson(route('dashboard.sections', 'portfolio'))->json('data');

        $this->assertSame(3, $data['risks']['total']);
        $this->assertSame(['active' => 2, 'closed' => 1], $data['risks']['totals']);
        $this->assertSame(['name' => self::OWN, 'counts' => ['active' => 1, 'closed' => 1], 'total' => 2], $data['risks']['rows'][0]);

        $this->assertSame(3, $data['incidents']['total']);
        $clients = collect($data['incidents']['rows'])->keyBy('name');
        $this->assertSame(['open' => 1, 'closed' => 1], $clients['BANK A']['counts']);
        $this->assertSame(['open' => 1, 'closed' => 0], $clients['Belum diisi']['counts']);

        // The owner filter narrows both cards.
        $filtered = $this->getJson(route('dashboard.sections', ['section' => 'portfolio', 'owner' => self::FOREIGN]))->json('data');
        $this->assertSame(1, $filtered['risks']['total']);
        $this->assertSame(0, $filtered['incidents']['total']);
    }

    public function test_portfolio_folds_small_groups_into_other(): void
    {
        $viewer = $this->createUser(self::FOREIGN);
        foreach (range(1, 10) as $i) {
            $this->createRisk($viewer, 'UNIT ' . $i, 'Fold ' . $i);
        }

        $this->actingAs($viewer);
        $risks = $this->getJson(route('dashboard.sections', 'portfolio'))->json('data.risks');

        $this->assertCount(9, $risks['rows']); // 8 units + "Lainnya"
        $this->assertSame('Lainnya (2)', $risks['rows'][8]['name']);
        $this->assertSame(2, $risks['rows'][8]['total']);
        $this->assertSame(10, $risks['groups_total']);
    }

    // ---------------------------------------------------------------------

    private function createUser(string $division): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Dashboard User ' . $suffix,
            'username' => 'dash_' . $suffix,
            'division' => $division,
            'division_verified_at' => now(),
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }

    private function createRisk(User $creator, string $owner, string $title, ?array $residual = null): Risk
    {
        $risk = Risk::create([
            'risk_id' => 'DSH-' . Str::upper(Str::random(8)),
            'year' => (int) now()->year,
            'risk_title' => $title,
            'risk_category_id' => $this->categoryId(),
            'risk_owner' => $owner,
            'status' => 'Open',
            'created_by' => $creator->id,
        ]);

        if ($residual !== null) {
            [$probability, $impact, $score] = $residual;
            RiskResidualAnalysis::create([
                'risk_id' => $risk->id,
                'probability_score' => $probability,
                'impact_score' => $impact,
                'residual_risk_score' => $score,
                'risk_level' => 'High',
            ]);
        }

        return $risk;
    }

    private function createKri(Risk $risk, string $status): Kri
    {
        return Kri::create([
            'risk_id' => $risk->id,
            'metric_name' => 'Metric ' . Str::random(5),
            'threshold_type' => 'higher_is_worse',
            'threshold_warn' => 5,
            'threshold_critical' => 10,
            'current_value' => 12,
            'status' => $status,
        ]);
    }

    private function createIncident(User $reporter, Risk $risk, float $loss): RiskIncident
    {
        return RiskIncident::createWithGeneratedIncidentCode([
            'risk_id' => $risk->id,
            'risk_owner' => $risk->risk_owner,
            'reported_by' => $reporter->id,
            'last_updated_by' => $reporter->id,
            'title' => 'Incident for ' . $risk->risk_title,
            'incident_description' => 'Dashboard test incident',
            'occurred_at' => now(),
            'severity' => 'High',
            'status' => 'Open',
            'mitigation_verdict' => 'Not Tested',
            'loss_amount' => $loss,
        ]);
    }

    private function createTreatment(Risk $risk, string $deadline, string $status): RiskTreatment
    {
        return RiskTreatment::create([
            'risk_id' => $risk->id,
            'option' => 'Mitigation',
            'description' => 'Treatment ' . Str::random(5),
            'pic' => $risk->risk_owner,
            'status' => $status,
            'deadline' => $deadline,
            'effectiveness' => 'Medium',
            'effectiveness_score' => 0.5,
        ]);
    }

    private function createAppetite(string $metric, ?float $appetiteLimit, ?float $toleranceLimit): RiskAppetite
    {
        return RiskAppetite::create([
            'name' => 'Appetite ' . Str::random(5),
            'threshold_metric' => $metric,
            'appetite_statement' => 'Test appetite statement',
            'appetite_limit_value' => $appetiteLimit,
            'tolerance_limit_value' => $toleranceLimit,
            'is_active' => true,
        ]);
    }

    private function categoryId(): int
    {
        return (int) (RiskCategory::query()->value('id') ?? RiskCategory::query()->insertGetId([
            'name' => 'Dashboard Category',
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }
}
