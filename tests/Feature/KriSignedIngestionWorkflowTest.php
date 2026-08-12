<?php

namespace Tests\Feature;

use App\Kri;
use App\Risk;
use App\RiskCategory;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class KriSignedIngestionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private string $secret = 'test-ingestion-secret';
    private string $source = 'external-risk-engine';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('kri_ingestion.enabled', true);
        config()->set('kri_ingestion.shared_secret', $this->secret);
        config()->set('kri_ingestion.allowed_sources', [$this->source]);
        config()->set('kri_ingestion.max_timestamp_skew_seconds', 300);
    }

    public function test_signed_external_event_updates_kri_and_is_idempotent(): void
    {
        $kri = $this->createKri();
        $payload = [
            'kri_id' => $kri->id,
            'value' => 17.5,
            'recorded_at' => now()->toDateTimeString(),
            'source_reference' => 'risk-engine/run-001',
            'notes' => 'Automated reading.',
        ];
        $eventId = 'event-001';

        $response = $this->signedRequest($payload, $eventId);
        $response->assertCreated()->assertJsonPath('status', 'processed');

        $this->assertDatabaseHas('kris', [
            'id' => $kri->id,
            'current_value' => 17.5,
            'data_source' => 'api',
            'source_identifier' => $this->source,
        ]);
        $this->assertDatabaseHas('kri_ingestion_events', [
            'source' => $this->source,
            'event_id' => $eventId,
            'status' => 'processed',
        ]);
        $this->assertDatabaseCount('kri_logs', 1);

        $this->signedRequest($payload, $eventId)
            ->assertStatus(202)
            ->assertJsonPath('status', 'duplicate');
        $this->assertDatabaseCount('kri_logs', 1);
    }

    public function test_invalid_signature_is_rejected_without_ingestion_event(): void
    {
        $kri = $this->createKri();
        $payload = json_encode(['kri_id' => $kri->id, 'value' => 10], JSON_UNESCAPED_SLASHES);

        $this->call('POST', route('api.kri.ingestion'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_KRI_SOURCE' => $this->source,
            'HTTP_X_KRI_EVENT_ID' => 'event-invalid',
            'HTTP_X_KRI_TIMESTAMP' => (string) time(),
            'HTTP_X_KRI_SIGNATURE' => 'sha256=invalid',
        ], $payload)->assertUnauthorized();

        $this->assertDatabaseCount('kri_ingestion_events', 0);
        $this->assertDatabaseCount('kri_logs', 0);
    }

    private function signedRequest(array $payload, string $eventId)
    {
        $rawPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp . '.' . $rawPayload, $this->secret);

        return $this->call('POST', route('api.kri.ingestion'), [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_KRI_SOURCE' => $this->source,
            'HTTP_X_KRI_EVENT_ID' => $eventId,
            'HTTP_X_KRI_TIMESTAMP' => (string) $timestamp,
            'HTTP_X_KRI_SIGNATURE' => 'sha256=' . $signature,
        ], $rawPayload);
    }

    private function createKri(): Kri
    {
        $suffix = Str::lower(Str::random(8));
        $user = User::create([
            'name' => 'Ingestion User ' . $suffix,
            'username' => 'ingestion_' . $suffix,
            'division' => 'BT - DIGITAL BANKING',
            'division_verified_at' => now(),
            'force_password_change' => false,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
        $category = RiskCategory::query()->firstOrCreate(['name' => 'Ingestion Category']);
        $risk = Risk::create([
            'risk_id' => 'ING-' . Str::upper(Str::random(7)),
            'year' => now()->year,
            'risk_title' => 'External KRI Ingestion Risk',
            'risk_category_id' => $category->id,
            'risk_owner' => 'BT - DIGITAL BANKING',
            'status' => 'Open',
            'created_by' => $user->id,
        ]);

        return Kri::create([
            'risk_id' => $risk->id,
            'metric_name' => 'External Transaction Failure Rate',
            'data_source' => 'api',
            'expected_refresh_minutes' => 60,
            'threshold_type' => 'lower_is_better',
            'threshold_warn' => 10,
            'threshold_critical' => 15,
            'status' => 'Green',
        ]);
    }
}
