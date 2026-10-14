<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthCheckWorkflowTest extends TestCase
{
    public function test_liveness_endpoint_returns_ok(): void
    {
        $response = $this->get(route('health.live'));

        $response->assertOk()
            ->assertJson([
                'status' => 'ok',
            ])
            ->assertJsonStructure([
                'status',
                'timestamp',
            ]);
    }

    public function test_readiness_endpoint_returns_operational_checks(): void
    {
        $response = $this->get(route('health.ready'));

        $response->assertOk()
            ->assertJson([
                'status' => 'ready',
            ])
            ->assertJsonStructure([
                'status',
                'timestamp',
                'checks' => [
                    'database' => ['ok', 'message'],
                    'cache' => ['ok', 'message'],
                    'storage' => ['ok', 'message'],
                ],
            ]);
    }
}
