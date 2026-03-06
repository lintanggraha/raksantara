<?php

namespace Tests\Feature;

use App\ScheduledReport;
use App\Services\ReportDataService;
use App\Services\ScheduledReportDispatchService;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class ScheduledReportSecurityRegressionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_non_compliance_user_cannot_store_schedule_with_foreign_risk_owner_filter(): void
    {
        $user = $this->createUser('BT - DIGITAL BANKING', false);
        $this->actingAs($user);

        $response = $this->from(route('reports.scheduled.index'))->post(route('reports.scheduled.store'), [
            'name' => 'Foreign Owner Probe',
            'scope' => 'monitoring',
            'format' => 'pdf',
            'frequency' => 'daily',
            'send_time' => '08:00',
            'timezone' => 'Asia/Jakarta',
            'recipients' => $user->email,
            'risk_owner' => 'CO - COMPLIANCE',
        ]);

        $response->assertRedirect(route('reports.scheduled.index'));
        $response->assertSessionHasErrors(['risk_owner']);
    }

    public function test_schedule_validation_returns_field_errors_instead_of_generic_store_error(): void
    {
        $user = $this->createUser('CO - COMPLIANCE', false);
        $this->actingAs($user);

        $response = $this->from(route('reports.scheduled.index'))->post(route('reports.scheduled.store'), [
            'scope' => 'monitoring',
            'format' => 'pdf',
            'frequency' => 'daily',
            'send_time' => '25:99',
            'timezone' => 'Asia/Jakarta',
            'recipients' => $user->email,
        ]);

        $response->assertRedirect(route('reports.scheduled.index'));
        $response->assertSessionHasErrors(['name', 'send_time']);
        $response->assertSessionDoesntHaveErrors(['store']);
    }

    public function test_dispatch_uses_schedule_owner_as_report_viewer_scope(): void
    {
        Mail::fake();

        $user = $this->createUser('CO - COMPLIANCE', false);
        $schedule = ScheduledReport::query()->create([
            'user_id' => $user->id,
            'name' => 'Scope Propagation Probe',
            'scope' => 'monitoring',
            'format' => 'xlsx',
            'frequency' => 'daily',
            'send_time' => '06:00',
            'timezone' => 'UTC',
            'recipients' => [$user->email],
            'filters' => [],
            'is_active' => true,
            'next_run_at' => now()->subMinute(),
        ]);

        $spyService = new class extends ReportDataService {
            /** @var int */
            public $viewerId = 0;

            public function buildPayload(string $scope, ?int $year = null, array $filters = [], ?User $viewer = null): array
            {
                $this->viewerId = is_null($viewer) ? 0 : (int) $viewer->id;

                return [
                    'title' => 'Probe Payload',
                    'file' => 'probe-payload',
                    'sections' => [],
                ];
            }
        };

        $dispatchService = app(ScheduledReportDispatchService::class);
        $result = $dispatchService->dispatchSchedule($schedule->loadMissing('user'), $spyService, 'manual');

        $this->assertTrue((bool) ($result['success'] ?? false));
        $this->assertSame((int) $user->id, (int) $spyService->viewerId);
    }

    private function createUser(string $division, bool $isSuperadmin): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Scheduled Scope User ' . $suffix,
            'username' => 'scheduled_scope_' . $suffix,
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
