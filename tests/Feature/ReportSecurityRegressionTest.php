<?php

namespace Tests\Feature;

use App\Risk;
use App\RiskCategory;
use App\RiskCauseCategory;
use App\Services\ReportDataService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReportSecurityRegressionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_non_compliance_user_cannot_export_foreign_risk_owner_filter(): void
    {
        $this->seedScopeFixtures();

        $user = $this->createUser('BT - DIGITAL BANKING', false);
        $this->actingAs($user);

        $response = $this->getJson(route('reports.export', [
            'scope' => 'risks',
            'format' => 'pdf',
            'risk_owner' => 'CO - COMPLIANCE',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['risk_owner']);
    }

    public function test_report_payload_scopes_rows_to_viewer_allowed_owners(): void
    {
        $fixtures = $this->seedScopeFixtures();
        $user = $this->createUser('BT - DIGITAL BANKING', false);

        $service = app(ReportDataService::class);
        $payload = $service->buildPayload('risks', 2026, [], $user);

        $rows = data_get($payload, 'sections.0.rows', []);
        $riskIds = collect($rows)->map(function ($row) {
            return (string) ($row[0] ?? '');
        })->all();

        $this->assertContains($fixtures['bt_risk_id'], $riskIds);
        $this->assertNotContains($fixtures['compliance_risk_id'], $riskIds);
    }

    public function test_foreign_owner_filter_returns_empty_payload_for_non_compliance_user(): void
    {
        $this->seedScopeFixtures();
        $user = $this->createUser('BT - DIGITAL BANKING', false);

        $service = app(ReportDataService::class);
        $payload = $service->buildPayload('risks', 2026, ['risk_owner' => 'CO - COMPLIANCE'], $user);

        $rows = data_get($payload, 'sections.0.rows', []);
        $this->assertCount(0, $rows);
    }

    private function seedScopeFixtures(): array
    {
        $suffix = Str::upper(Str::random(4));
        $categoryId = (int) DB::table('risk_categories')->insertGetId([
            'name' => 'Scope Category ' . $suffix,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $causeCategoryId = (int) DB::table('risk_cause_categories')->insertGetId([
            'name' => 'Scope Cause ' . $suffix,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $category = RiskCategory::findOrFail($categoryId);
        $causeCategory = RiskCauseCategory::findOrFail($causeCategoryId);

        $complianceRiskId = 'RISK-2026-SC-' . $suffix;
        $btRiskId = 'RISK-2026-SB-' . $suffix;

        Risk::create([
            'risk_id' => $complianceRiskId,
            'year' => 2026,
            'risk_title' => 'Scope Compliance ' . $suffix,
            'risk_description' => 'Scope fixture compliance',
            'risk_category_id' => $category->id,
            'risk_cause_category_id' => $causeCategory->id,
            'risk_owner' => 'CO - COMPLIANCE',
            'status' => 'Open',
        ]);

        Risk::create([
            'risk_id' => $btRiskId,
            'year' => 2026,
            'risk_title' => 'Scope BT ' . $suffix,
            'risk_description' => 'Scope fixture bt',
            'risk_category_id' => $category->id,
            'risk_cause_category_id' => $causeCategory->id,
            'risk_owner' => 'BT - DIGITAL BANKING',
            'status' => 'Open',
        ]);

        return [
            'compliance_risk_id' => $complianceRiskId,
            'bt_risk_id' => $btRiskId,
        ];
    }

    private function createUser(string $division, bool $isSuperadmin): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Report Scope User ' . $suffix,
            'username' => 'report_scope_' . $suffix,
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
