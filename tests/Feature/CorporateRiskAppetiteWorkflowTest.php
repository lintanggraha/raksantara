<?php

namespace Tests\Feature;

use App\RadarGroup;
use App\Risk;
use App\RiskAlert;
use App\RiskAppetite;
use App\RiskCategory;
use App\RiskQuantProfile;
use App\RiskResidualAnalysis;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class CorporateRiskAppetiteWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_superadmin_user_can_view_but_cannot_manage_risk_appetite_frameworks(): void
    {
        $viewer = $this->createUser('BT - DIGITAL BANKING', false);
        $appetite = $this->createAppetite();

        $this->actingAs($viewer);

        $this->get(route('risk-appetites.index'))->assertOk();
        $this->get(route('risk-appetites.show', $appetite->id))->assertOk();
        $this->get(route('risk-appetites.create'))->assertForbidden();
        $this->post(route('risk-appetites.store'), $this->defaultAppetitePayload())->assertForbidden();
        $this->get(route('risk-appetites.edit', $appetite->id))->assertForbidden();
        $this->put(route('risk-appetites.update', $appetite->id), $this->defaultAppetitePayload())->assertForbidden();
        $this->delete(route('risk-appetites.destroy', $appetite->id))->assertForbidden();
    }

    public function test_risk_create_and_update_persist_selected_appetite(): void
    {
        $user = $this->createUser('BT - DIGITAL BANKING', false);
        $initialAppetite = $this->createAppetite([
            'name' => 'Initial Appetite',
        ]);
        $replacementAppetite = $this->createAppetite([
            'name' => 'Replacement Appetite',
            'threshold_metric' => RiskAppetite::THRESHOLD_METRIC_RESIDUAL_SCORE,
            'appetite_limit_value' => 10,
            'tolerance_limit_value' => 15,
            'unit' => 'Score',
        ]);

        $this->actingAs($user);

        $this->get(route('risks.create'))
            ->assertOk()
            ->assertSee('name="risk_appetite_id"', false);

        $storeResponse = $this->post(route('risks.store'), [
            'risk_title' => 'Corporate Appetite Persisted Risk',
            'risk_category_id' => $this->ensureRiskCategoryId(),
            'risk_owner' => 'BT - DIGITAL BANKING',
            'risk_appetite_id' => $initialAppetite->id,
        ]);

        $risk = Risk::query()->where('risk_title', 'Corporate Appetite Persisted Risk')->firstOrFail();

        $storeResponse->assertRedirect(route('risks.index'));
        $this->assertDatabaseHas('risks', [
            'id' => $risk->id,
            'risk_appetite_id' => $initialAppetite->id,
        ]);

        $updateResponse = $this->put(route('risks.update', $risk->id), [
            'risk_title' => 'Corporate Appetite Persisted Risk Updated',
            'risk_category_id' => $risk->risk_category_id,
            'risk_owner' => $risk->risk_owner,
            'risk_appetite_id' => $replacementAppetite->id,
            'status' => 'Open',
        ]);

        $updateResponse->assertRedirect(route('risks.index'));
        $this->assertDatabaseHas('risks', [
            'id' => $risk->id,
            'risk_title' => 'Corporate Appetite Persisted Risk Updated',
            'risk_appetite_id' => $replacementAppetite->id,
        ]);
    }

    public function test_scan_alerts_uses_linked_corporate_appetite_before_radar_fallback(): void
    {
        $owner = $this->createUser('CO - COMPLIANCE', false);
        $appetite = $this->createAppetite([
            'name' => 'Score Appetite',
            'threshold_metric' => RiskAppetite::THRESHOLD_METRIC_RESIDUAL_SCORE,
            'appetite_limit_value' => 10,
            'tolerance_limit_value' => 15,
            'unit' => 'Score',
        ]);
        $risk = $this->createRiskRecord($owner, 'CO - COMPLIANCE', 'Linked Appetite Threshold Risk', $appetite->id);
        $radarGroup = $this->createRadarGroupWithQuantProfile(20, null);
        $this->attachResidualAnalysis($risk, $radarGroup, 12, 0);

        $this->artisan('risk:scan-alerts')->assertExitCode(0);

        $alert = RiskAlert::query()
            ->where('risk_id', $risk->id)
            ->where('alert_type', 'risk_appetite_threshold_breach')
            ->first();

        $this->assertNotNull($alert);
        $this->assertSame('warning', $alert->severity);
        $this->assertSame('corporate_risk_appetite', $alert->meta['source'] ?? null);
        $this->assertSame(RiskAppetite::THRESHOLD_METRIC_RESIDUAL_SCORE, $alert->meta['metric'] ?? null);
    }

    public function test_scan_alerts_fall_back_to_radar_quant_profile_when_no_linked_appetite_exists(): void
    {
        $owner = $this->createUser('CO - COMPLIANCE', false);
        $risk = $this->createRiskRecord($owner, 'CO - COMPLIANCE', 'Fallback Threshold Risk');
        $radarGroup = $this->createRadarGroupWithQuantProfile(11, null);
        $this->attachResidualAnalysis($risk, $radarGroup, 13, 0);

        $this->artisan('risk:scan-alerts')->assertExitCode(0);

        $alert = RiskAlert::query()
            ->where('risk_id', $risk->id)
            ->where('alert_type', 'risk_appetite_threshold_breach')
            ->first();

        $this->assertNotNull($alert);
        $this->assertSame('critical', $alert->severity);
        $this->assertSame('radar_quant_profile', $alert->meta['source'] ?? null);
    }

    public function test_linked_appetite_cannot_be_deleted_and_should_be_deactivated_instead(): void
    {
        $superadmin = $this->createUser('CO - COMPLIANCE', true);
        $owner = $this->createUser('CO - COMPLIANCE', false);
        $appetite = $this->createAppetite([
            'name' => 'Protected Appetite',
        ]);

        $this->createRiskRecord($owner, 'CO - COMPLIANCE', 'Risk Linked To Appetite', $appetite->id);

        $this->actingAs($superadmin);

        $this->from(route('risk-appetites.edit', $appetite->id))
            ->delete(route('risk-appetites.destroy', $appetite->id))
            ->assertRedirect(route('risk-appetites.edit', $appetite->id));

        $this->assertDatabaseHas('risk_appetites', [
            'id' => $appetite->id,
        ]);
    }

    private function createAppetite(array $overrides = []): RiskAppetite
    {
        return RiskAppetite::create(array_merge([
            'name' => 'Appetite ' . Str::upper(Str::random(6)),
            'risk_category' => 'Operational',
            'threshold_metric' => RiskAppetite::THRESHOLD_METRIC_QUALITATIVE,
            'appetite_statement' => 'Default appetite statement',
            'appetite_limit_value' => null,
            'tolerance_limit_value' => null,
            'unit' => null,
            'is_active' => true,
            'created_by' => null,
        ], $overrides));
    }

    private function defaultAppetitePayload(): array
    {
        return [
            'name' => 'Unauthorized Appetite',
            'risk_category' => 'Operational',
            'threshold_metric' => RiskAppetite::THRESHOLD_METRIC_QUALITATIVE,
            'appetite_statement' => 'Unauthorized attempt',
            'is_active' => '1',
        ];
    }

    private function createRiskRecord(User $creator, string $riskOwner, string $title, ?int $riskAppetiteId = null): Risk
    {
        return Risk::create([
            'risk_id' => 'APP-' . Str::upper(Str::random(8)),
            'year' => (int) now()->year,
            'risk_title' => $title,
            'risk_category_id' => $this->ensureRiskCategoryId(),
            'risk_appetite_id' => $riskAppetiteId,
            'risk_owner' => $riskOwner,
            'status' => 'Open',
            'created_by' => $creator->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function attachResidualAnalysis(Risk $risk, RadarGroup $radarGroup, int $score, int $loss): RiskResidualAnalysis
    {
        return RiskResidualAnalysis::create([
            'risk_id' => $risk->id,
            'probability_score' => 3,
            'impact_score' => 4,
            'residual_risk_score' => $score,
            'risk_level' => 'Moderate',
            'radar_group_id' => $radarGroup->id,
            'radar_description' => $radarGroup->description,
            'radar_code' => $radarGroup->code,
            'potential_loss' => $loss,
        ]);
    }

    private function createRadarGroupWithQuantProfile(?int $thresholdScore, ?int $thresholdLoss): RadarGroup
    {
        $radarGroup = RadarGroup::create([
            'code' => 'RG' . Str::upper(Str::random(4)),
            'description' => 'Radar Group ' . Str::upper(Str::random(6)),
        ]);

        RiskQuantProfile::create([
            'radar_group_id' => $radarGroup->id,
            'potential_loss_inherent' => 0,
            'residual_loss_factor' => 0.3000,
            'risk_appetite_score' => $thresholdScore,
            'risk_appetite_loss' => $thresholdLoss,
        ]);

        return $radarGroup;
    }

    private function ensureRiskCategoryId(): int
    {
        $existingId = RiskCategory::query()->value('id');
        if (!is_null($existingId)) {
            return (int) $existingId;
        }

        return (int) RiskCategory::query()->insertGetId([
            'name' => 'Appetite Category ' . Str::random(6),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createUser(string $division, bool $isSuperadmin): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Appetite User ' . $suffix,
            'username' => 'appetite_' . $suffix,
            'division' => $division,
            'pending_division' => null,
            'division_verified_at' => now(),
            'is_superadmin' => $isSuperadmin,
            'force_password_change' => false,
            'email' => 'appetite_' . $suffix . '@example.test',
            'password' => Hash::make('password'),
        ]);
    }
}
