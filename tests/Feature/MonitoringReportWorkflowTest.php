<?php

namespace Tests\Feature;

use App\Risk;
use App\RiskCategory;
use App\RiskCauseCategory;
use App\RiskInherentAnalysis;
use App\RiskResidualAnalysis;
use App\RiskTreatment;
use App\Services\ReportDataService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class MonitoringReportWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_overall_payload_includes_monitoring_sections_and_supports_filters(): void
    {
        $categoryAId = (int) DB::table('risk_categories')->insertGetId([
            'name' => 'Tech',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $categoryBId = (int) DB::table('risk_categories')->insertGetId([
            'name' => 'Ops',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $causeCategoryId = (int) DB::table('risk_cause_categories')->insertGetId([
            'name' => 'Process',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $categoryA = RiskCategory::findOrFail($categoryAId);
        $categoryB = RiskCategory::findOrFail($categoryBId);
        $causeCategory = RiskCauseCategory::findOrFail($causeCategoryId);

        $riskA = Risk::create([
            'risk_id' => 'RISK-2026-901',
            'year' => 2026,
            'risk_title' => 'Category A Risk',
            'risk_description' => 'Risk for category A',
            'risk_category_id' => $categoryA->id,
            'risk_cause_category_id' => $causeCategory->id,
            'risk_owner' => 'CO - COMPLIANCE',
            'status' => 'Monitoring',
        ]);

        RiskInherentAnalysis::create([
            'risk_id' => $riskA->id,
            'probability_score' => 4,
            'impact_score' => 4,
            'inherent_risk_score' => 16,
            'risk_level' => 'Moderate to High',
        ]);
        RiskResidualAnalysis::create([
            'risk_id' => $riskA->id,
            'probability_score' => 2,
            'impact_score' => 3,
            'residual_risk_score' => 6,
            'risk_level' => 'Low to Moderate',
            'radar_code' => 'S1',
        ]);
        RiskTreatment::create([
            'risk_id' => $riskA->id,
            'option' => 'Mitigate',
            'description' => 'Do mitigation A',
            'pic' => 'CO',
            'deadline' => now()->addDays(5)->format('Y-m-d'),
            'effectiveness' => 'High',
            'effectiveness_score' => 1,
        ]);

        $riskB = Risk::create([
            'risk_id' => 'RISK-2026-902',
            'year' => 2026,
            'risk_title' => 'Category B Risk',
            'risk_description' => 'Risk for category B',
            'risk_category_id' => $categoryB->id,
            'risk_cause_category_id' => $causeCategory->id,
            'risk_owner' => 'BT - DIGITAL BANKING',
            'status' => 'Open',
        ]);

        RiskInherentAnalysis::create([
            'risk_id' => $riskB->id,
            'probability_score' => 3,
            'impact_score' => 3,
            'inherent_risk_score' => 9,
            'risk_level' => 'Moderate',
        ]);
        RiskResidualAnalysis::create([
            'risk_id' => $riskB->id,
            'probability_score' => 2,
            'impact_score' => 2,
            'residual_risk_score' => 4,
            'risk_level' => 'Low',
            'radar_code' => 'O1',
        ]);

        $service = app(ReportDataService::class);

        $overall = $service->buildPayload('overall');
        $overallSectionTitles = collect($overall['sections'] ?? [])->pluck('title')->all();

        $this->assertContains('Monitoring Overview', $overallSectionTitles);
        $this->assertContains('Risk Register', $overallSectionTitles);
        $this->assertContains('Heatmap Dashboard', $overallSectionTitles);

        $monitoring = $service->buildPayload('monitoring', null, [
            'risk_category_id' => $categoryA->id,
        ]);

        $overviewSection = collect($monitoring['sections'] ?? [])->firstWhere('title', 'Monitoring Overview');
        $this->assertNotNull($overviewSection);

        $metricMap = collect($overviewSection['rows'] ?? [])
            ->filter(function ($row) {
                return is_array($row) && count($row) >= 2;
            })
            ->mapWithKeys(function ($row) {
                return [(string) $row[0] => (string) $row[1]];
            });

        $this->assertSame('1', $metricMap->get('Total Risks'));
        $this->assertSame('1', $metricMap->get('Monitoring Risks'));
        $this->assertSame('0', $metricMap->get('Open Risks'));
    }

    public function test_compliance_user_can_export_monitoring_pdf_report(): void
    {
        $user = $this->createUser('CO - COMPLIANCE', false);
        $this->actingAs($user);

        $response = $this->get(route('reports.export', [
            'scope' => 'monitoring',
            'format' => 'pdf',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_compliance_user_can_export_monitoring_ppt_report(): void
    {
        $user = $this->createUser('CO - COMPLIANCE', false);
        $this->actingAs($user);

        $response = $this->get(route('reports.export', [
            'scope' => 'monitoring',
            'format' => 'pptx',
        ]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.presentationml.presentation');
    }

    private function createUser(string $division, bool $isSuperadmin): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Report User ' . $suffix,
            'username' => 'report_' . $suffix,
            'division' => $division,
            'pending_division' => null,
            'division_verified_at' => now(),
            'is_superadmin' => $isSuperadmin,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }
}
