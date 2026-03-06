<?php

namespace Tests\Feature;

use App\Http\Controllers\TrendAnalysisController;
use App\Risk;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use ReflectionMethod;
use Tests\TestCase;

class TrendAnalysisWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_trend_data_endpoint_handles_missing_risk_scores_without_crash(): void
    {
        $user = $this->createUser('CO - COMPLIANCE', false);
        $this->actingAs($user);

        Risk::create([
            'risk_id' => 'RISK-2026-991',
            'year' => 2026,
            'risk_title' => 'Risk without score analysis',
            'risk_description' => 'This risk intentionally has no inherent/residual analysis rows.',
            'risk_owner' => 'CO - COMPLIANCE',
            'status' => 'Open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson(route('trends.data', [
            'granularity' => 'monthly',
            'days' => 365,
        ]));

        $response->assertOk()
            ->assertJsonStructure([
                'trend',
                'statistics' => [
                    'avg_inherent',
                    'avg_residual',
                    'max_inherent',
                    'max_residual',
                    'total_risks',
                    'improvement_percentage',
                ],
                'categories',
                'status',
                'owners',
                'heatmap_trends',
            ]);
    }

    public function test_trend_statistics_handles_null_max_values_without_exception(): void
    {
        $controller = app(TrendAnalysisController::class);
        $method = new ReflectionMethod($controller, 'calculateTrendStatistics');
        $method->setAccessible(true);

        $statistics = $method->invoke($controller, [[
            'period' => 'Q1 2026',
            'risk_count' => 1,
            'inherent_avg' => null,
            'inherent_max' => null,
            'residual_avg' => null,
            'residual_max' => null,
            'status' => ['Open' => 1, 'Monitoring' => 0, 'Closed' => 0],
        ]]);

        $this->assertSame(0, $statistics['max_inherent']);
        $this->assertSame(0, $statistics['max_residual']);
        $this->assertSame(0, $statistics['avg_inherent']);
        $this->assertSame(0, $statistics['avg_residual']);
    }

    private function createUser(string $division, bool $isSuperadmin): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Trend User ' . $suffix,
            'username' => 'trend_' . $suffix,
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
