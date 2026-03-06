<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardEnterpriseWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dashboard_summary_supports_trend_granularity_and_benchmark(): void
    {
        $user = $this->createUser('CO - COMPLIANCE', false);
        $this->actingAs($user);

        $response = $this->getJson(route('risks.dashboard-summary', [
            'trend_granularity' => 'yearly',
        ]));

        $response->assertOk()
            ->assertJson([
                'trend_granularity' => 'yearly',
            ])
            ->assertJsonStructure([
                'trend_granularity',
                'trend_monthly',
                'trend_quarterly',
                'trend_yearly',
                'benchmark_scoring' => [
                    'industry_baseline_score',
                    'target_score',
                    'internal_average_score',
                    'gap_vs_industry',
                    'gap_vs_target',
                    'position',
                    'position_label',
                    'source_name',
                    'source_reference',
                    'source_year',
                    'score_scale_min',
                    'score_scale_max',
                    'methodology',
                    'position_reason',
                ],
                'kpi_dashboard' => [
                    'avg_response_time_days',
                    'response_sla_days',
                    'response_sample_count',
                    'response_within_sla_count',
                    'response_within_sla_percent',
                    'treatment_effectiveness_rate',
                    'risk_treatment_coverage_rate',
                    'closed_risk_rate',
                    'overdue_treatment_rate',
                ],
                'department_performance' => [
                    'rows',
                    'average_score',
                    'top_division',
                ],
                'root_cause_analysis' => [
                    'top_categories',
                    'top_keywords',
                    'patterns',
                ],
            ]);
    }

    public function test_dashboard_preferences_can_be_saved_and_loaded(): void
    {
        $user = $this->createUser('CO - COMPLIANCE', false);
        $this->actingAs($user);

        $save = $this->putJson(route('dashboard-preferences.update'), [
            'widget_visibility' => [
                'status_metrics' => true,
                'trend_scores' => false,
                'benchmark_scoring' => true,
            ],
            'widget_order' => [
                'benchmark_scoring',
                'status_metrics',
                'trend_scores',
            ],
            'trend_granularity' => 'monthly',
        ]);
        $save->assertOk();

        $load = $this->getJson(route('dashboard-preferences.show'));
        $load->assertOk()
            ->assertJson([
                'trend_granularity' => 'monthly',
                'widget_order' => [
                    'benchmark_scoring',
                    'status_metrics',
                    'trend_scores',
                ],
            ]);

        $this->assertDatabaseHas('user_dashboard_preferences', [
            'user_id' => $user->id,
            'trend_granularity' => 'monthly',
        ]);
    }

    private function createUser(string $division, bool $isSuperadmin): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Dashboard User ' . $suffix,
            'username' => 'dashboard_' . $suffix,
            'division' => $division,
            'pending_division' => null,
            'division_verified_at' => now(),
            'is_superadmin' => $isSuperadmin,
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }
}
