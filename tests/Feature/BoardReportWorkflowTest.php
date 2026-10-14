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

class BoardReportWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_board_payload_is_executive_focused_and_excludes_risk_register_section(): void
    {
        $categoryId = (int) DB::table('risk_categories')->insertGetId([
            'name' => 'Technology',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $causeCategoryId = (int) DB::table('risk_cause_categories')->insertGetId([
            'name' => 'Process',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $risk = Risk::create([
            'risk_id' => 'RISK-2026-811',
            'year' => 2026,
            'risk_title' => 'Core System Outage',
            'risk_description' => 'Potential prolonged outage on critical system.',
            'risk_category_id' => $categoryId,
            'risk_cause_category_id' => $causeCategoryId,
            'risk_owner' => 'CO - COMPLIANCE',
            'status' => 'Monitoring',
        ]);

        RiskInherentAnalysis::create([
            'risk_id' => $risk->id,
            'probability_score' => 4,
            'impact_score' => 5,
            'inherent_risk_score' => 20,
            'risk_level' => 'High',
        ]);
        RiskResidualAnalysis::create([
            'risk_id' => $risk->id,
            'probability_score' => 3,
            'impact_score' => 4,
            'residual_risk_score' => 12,
            'risk_level' => 'Moderate',
            'radar_code' => 'S1',
        ]);
        RiskTreatment::create([
            'risk_id' => $risk->id,
            'option' => 'Mitigate',
            'description' => 'Improve HA and DR controls.',
            'pic' => 'CO',
            'deadline' => now()->addDays(10)->format('Y-m-d'),
            'effectiveness' => 'High',
            'effectiveness_score' => 1,
        ]);

        $service = app(ReportDataService::class);
        $payload = $service->buildPayload('board');

        $sectionTitles = collect($payload['sections'] ?? [])->pluck('title')->all();
        $sectionTypes = collect($payload['sections'] ?? [])->pluck('type')->all();

        $this->assertContains('Board Executive Snapshot', $sectionTitles);
        $this->assertContains('board_status_chart', $sectionTypes);
        $this->assertContains('board_category_chart', $sectionTypes);
        $this->assertContains('board_trend_chart', $sectionTypes);
        $this->assertContains('board_priority_table', $sectionTypes);
        $this->assertContains('board_narrative', $sectionTypes);
        $this->assertNotContains('Risk Register', $sectionTitles);
    }

    public function test_compliance_user_can_export_board_report_as_pdf_and_pptx(): void
    {
        $user = $this->createUser('CO - COMPLIANCE', false);
        $this->actingAs($user);

        $pdf = $this->get(route('reports.export', [
            'scope' => 'board',
            'format' => 'pdf',
        ]));
        $pdf->assertOk();
        $pdf->assertHeader('Content-Type', 'application/pdf');

        $ppt = $this->get(route('reports.export', [
            'scope' => 'board',
            'format' => 'pptx',
        ]));
        $ppt->assertOk();
        $ppt->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.presentationml.presentation');
    }

    private function createUser(string $division, bool $isSuperadmin): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Board User ' . $suffix,
            'username' => 'board_' . $suffix,
            'division' => $division,
            'pending_division' => null,
            'division_verified_at' => now(),
            'is_superadmin' => $isSuperadmin,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }
}
