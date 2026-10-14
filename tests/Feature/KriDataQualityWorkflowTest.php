<?php

namespace Tests\Feature;

use App\Kri;
use App\Risk;
use App\RiskCategory;
use App\RiskResidualAnalysis;
use App\Services\KriAutoSyncService;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class KriDataQualityWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_kri_log_marks_stale_source_reading(): void
    {
        $user = $this->createUser();
        $risk = $this->createRisk($user);

        $this->actingAs($user)
            ->post(route('kris.store', $risk->id), [
                'metric_name' => 'Service Availability',
                'data_source' => 'api',
                'source_identifier' => 'availability-api',
                'expected_refresh_minutes' => 5,
                'threshold_type' => 'higher_is_better',
                'threshold_warn' => 99,
                'threshold_critical' => 95,
            ])
            ->assertSessionHasNoErrors();

        $kri = Kri::query()->firstOrFail();

        $this->actingAs($user)
            ->post(route('kris.logValue', $kri->id), [
                'recorded_value' => 99.8,
                'source_recorded_at' => now()->subMinutes(10)->toDateTimeString(),
                'source_reference' => 'availability-api/run-123',
                'notes' => 'Imported from availability API.',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('kris', [
            'id' => $kri->id,
            'data_quality_status' => 'stale',
            'source_reference' => 'availability-api/run-123',
        ]);
        $this->assertDatabaseHas('kri_logs', [
            'kri_id' => $kri->id,
            'data_quality_status' => 'stale',
            'source_reference' => 'availability-api/run-123',
        ]);
    }

    public function test_auto_synced_kri_has_lineage_and_valid_quality(): void
    {
        $user = $this->createUser();
        $risk = $this->createRisk($user);
        RiskResidualAnalysis::create([
            'risk_id' => $risk->id,
            'residual_probability' => 3,
            'residual_impact' => 4,
            'residual_risk_score' => 12,
        ]);

        $stats = app(KriAutoSyncService::class)->syncByRiskIds([$risk->id], $user->id);

        $this->assertSame(1, $stats['created']);
        $this->assertDatabaseHas('kris', [
            'risk_id' => $risk->id,
            'data_source' => 'risk-register-import',
            'data_quality_status' => 'valid',
        ]);
    }

    private function createUser(): User
    {
        $suffix = Str::lower(Str::random(10));

        return User::create([
            'name' => 'KRI Quality User ' . $suffix,
            'username' => 'kri_quality_' . $suffix,
            'division' => 'BT - DIGITAL BANKING',
            'division_verified_at' => now(),
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }

    private function createRisk(User $user): Risk
    {
        $category = RiskCategory::query()->firstOrCreate(['name' => 'KRI Data Quality']);

        return Risk::create([
            'risk_id' => 'KRI-' . Str::upper(Str::random(8)),
            'year' => now()->year,
            'risk_title' => 'KRI Quality Risk',
            'risk_category_id' => $category->id,
            'risk_owner' => 'BT - DIGITAL BANKING',
            'status' => 'Open',
            'created_by' => $user->id,
        ]);
    }
}
