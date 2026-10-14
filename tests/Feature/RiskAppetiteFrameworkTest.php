<?php

namespace Tests\Feature;

use App\Risk;
use App\RiskAppetite;
use App\RiskCategory;
use App\RiskResidualAnalysis;
use App\Support\RiskAppetiteEvaluator;
use App\Support\RiskAppetiteSql;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class RiskAppetiteFrameworkTest extends TestCase
{
    use RefreshDatabase;

    public function test_compliance_manages_appetites_and_others_see_guidance(): void
    {
        $compliance = $this->createUser('CO - COMPLIANCE');
        $viewer = $this->createUser('BT - DIGITAL BANKING');

        $this->actingAs($viewer)->get(route('risk-appetites.index'))
            ->assertOk()
            ->assertSee('Risk appetite belum ditetapkan')
            ->assertDontSee(route('risk-appetites.create'));
        $this->post(route('risk-appetites.store'), $this->payload())->assertForbidden();

        $this->actingAs($compliance)->get(route('risk-appetites.index'))
            ->assertOk()
            ->assertSee('Tambah appetite default');
        $this->post(route('risk-appetites.store'), $this->payload())->assertRedirect();
        $this->assertDatabaseHas('risk_appetites', ['name' => 'Default appetite', 'risk_category_id' => null, 'unit' => 'Skor']);
    }

    public function test_only_one_active_appetite_per_scope_and_approval_needs_reference_and_date(): void
    {
        $compliance = $this->createUser('CO - COMPLIANCE');
        $category = RiskCategory::create(['name' => 'Kepatuhan']);
        $this->actingAs($compliance);

        $this->post(route('risk-appetites.store'), $this->payload(['risk_category_id' => $category->id]))->assertRedirect();
        $this->post(route('risk-appetites.store'), $this->payload(['name' => 'Duplicate', 'risk_category_id' => $category->id]))
            ->assertSessionHasErrors('risk_category_id');
        $this->post(route('risk-appetites.store'), $this->payload(['name' => 'Inactive copy', 'risk_category_id' => $category->id, 'is_active' => '0']))
            ->assertRedirect();

        $this->post(route('risk-appetites.store'), $this->payload(['name' => 'Half approved', 'approval_reference' => 'SK 01/2026']))
            ->assertSessionHasErrors('approved_at');
    }

    public function test_category_appetite_applies_automatically_with_override_and_default_precedence(): void
    {
        $user = $this->createUser('CO - COMPLIANCE');
        $ops = RiskCategory::create(['name' => 'Operasional']);
        $compliance = RiskCategory::create(['name' => 'Kepatuhan']);

        $default = $this->appetite('Default', null, 12, 16);
        $categoryAppetite = $this->appetite('Kepatuhan ketat', $compliance->id, 4, 8);
        $override = $this->appetite('Override longgar', $ops->id, 20, 25, false);
        $this->appetite('Kepatuhan lama', $compliance->id, 1, 2, false); // inactive: ignored

        $cases = [
            [$compliance->id, null, 10, $categoryAppetite->id, 'breach'],  // category beats default
            [$ops->id, null, 14, $default->id, 'warning'],                 // no active ops appetite -> default
            [$ops->id, $override->id, 14, $override->id, 'within'],        // explicit override wins
        ];

        foreach ($cases as $index => [$categoryId, $overrideId, $score, $expectedId, $expectedStatus]) {
            $risk = Risk::create([
                'risk_id' => 'RA-' . $index, 'year' => 2026, 'risk_title' => 'Appetite case ' . $index,
                'risk_category_id' => $categoryId, 'risk_owner' => 'CO - COMPLIANCE', 'status' => 'Open',
                'created_by' => $user->id, 'risk_appetite_id' => $overrideId,
            ]);
            RiskResidualAnalysis::create(['risk_id' => $risk->id, 'probability_score' => 2, 'impact_score' => 2, 'residual_risk_score' => $score]);

            $this->assertSame($expectedId, RiskAppetite::effectiveFor($risk->fresh())->id, "case {$index}: PHP resolution");
            $this->assertSame($expectedStatus, RiskAppetiteSql::fromEvaluatorStatus(RiskAppetiteEvaluator::evaluate($risk->fresh())['status']), "case {$index}: PHP status");

            $row = RiskAppetiteSql::joinSources(Risk::query()->whereKey($risk->id))
                ->selectRaw('ra.id as appetite_id, (' . RiskAppetiteSql::statusExpression() . ') as appetite_status')
                ->first();
            $this->assertSame($expectedId, (int) $row->appetite_id, "case {$index}: SQL resolution");
            $this->assertSame($expectedStatus, $row->appetite_status, "case {$index}: SQL status");
        }

        $this->actingAs($user)->get(route('risk-appetites.index'))
            ->assertOk()
            ->assertSee('3 / 3')
            ->assertSee('1 melewati');
        $this->get(route('risk-appetites.show', $categoryAppetite))->assertOk()->assertSee('RA-0')->assertDontSee('RA-1');
    }

    public function test_sample_appetites_are_unapproved_drafts_that_respect_existing_ones(): void
    {
        RiskCategory::create(['name' => 'Kepatuhan']);
        $keuangan = RiskCategory::create(['name' => 'Keuangan']);
        $this->appetite('Existing finance appetite', $keuangan->id, 1, 2);

        $this->artisan('appetite:seed-sample')->assertSuccessful();
        $this->assertNotNull(RiskAppetite::query()->whereNull('risk_category_id')->first());
        $this->assertSame(1, RiskAppetite::query()->where('risk_category_id', $keuangan->id)->count()); // not duplicated
        $this->assertSame(0, RiskAppetite::query()->whereNotNull('approval_reference')->count());

        $this->artisan('appetite:seed-sample')->assertSuccessful(); // idempotent
        $this->assertSame(3, RiskAppetite::query()->count());

        $this->artisan('appetite:seed-sample', ['--rollback' => true])->assertSuccessful();
        $this->assertSame(['Existing finance appetite'], RiskAppetite::query()->pluck('name')->all());
    }

    private function appetite(string $name, ?int $categoryId, float $appetite, float $tolerance, bool $active = true): RiskAppetite
    {
        return RiskAppetite::create([
            'name' => $name,
            'risk_category_id' => $categoryId,
            'threshold_metric' => RiskAppetite::THRESHOLD_METRIC_RESIDUAL_SCORE,
            'appetite_statement' => $name . ' statement',
            'appetite_limit_value' => $appetite,
            'tolerance_limit_value' => $tolerance,
            'unit' => 'Skor',
            'is_active' => $active,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Default appetite',
            'risk_category_id' => '',
            'threshold_metric' => RiskAppetite::THRESHOLD_METRIC_RESIDUAL_SCORE,
            'appetite_statement' => 'Perusahaan menerima risiko residual rendah hingga sedang.',
            'appetite_limit_value' => 6,
            'tolerance_limit_value' => 12,
            'is_active' => '1',
        ], $overrides);
    }

    private function createUser(string $division): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Appetite User ' . $suffix,
            'username' => 'appetite_' . $suffix,
            'division' => $division,
            'division_verified_at' => now(),
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }
}
