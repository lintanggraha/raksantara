<?php

namespace Tests\Feature;

use App\Mail\ScheduledReportDelivered;
use App\ScheduledReport;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class ScheduledReportWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_can_create_scheduled_report(): void
    {
        $user = $this->createUser('CO - COMPLIANCE', false);
        $this->actingAs($user);

        $response = $this->post(route('reports.scheduled.store'), [
            'name' => 'Weekly Board Briefing',
            'scope' => 'board',
            'format' => 'pdf',
            'frequency' => 'weekly',
            'send_time' => '08:30',
            'day_of_week' => 1,
            'timezone' => 'Asia/Jakarta',
            'recipients' => $user->email,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('scheduled_reports', [
            'user_id' => $user->id,
            'name' => 'Weekly Board Briefing',
            'scope' => 'board',
            'format' => 'pdf',
            'frequency' => 'weekly',
            'timezone' => 'Asia/Jakarta',
            'is_active' => true,
        ]);
    }

    public function test_due_schedule_is_dispatched_by_command(): void
    {
        Mail::fake();

        $user = $this->createUser('CO - COMPLIANCE', false);
        $schedule = ScheduledReport::query()->create([
            'user_id' => $user->id,
            'name' => 'Daily Monitoring Snapshot',
            'scope' => 'monitoring',
            'format' => 'xlsx',
            'frequency' => 'daily',
            'send_time' => '06:00',
            'timezone' => 'UTC',
            'recipients' => [$user->email],
            'filters' => [],
            'is_active' => true,
            'next_run_at' => now()->subMinutes(5),
        ]);

        $this->artisan('reports:dispatch-scheduled', [
            '--id' => $schedule->id,
        ])->assertExitCode(0);

        Mail::assertSent(ScheduledReportDelivered::class, function (ScheduledReportDelivered $mail) use ($schedule) {
            return (int) $mail->scheduledReport->id === (int) $schedule->id;
        });

        $schedule->refresh();
        $this->assertNotNull($schedule->last_run_at);
        $this->assertNotNull($schedule->next_run_at);
        $this->assertTrue($schedule->next_run_at->greaterThan(now()));
    }

    private function createUser(string $division, bool $isSuperadmin): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Scheduled User ' . $suffix,
            'username' => 'scheduled_' . $suffix,
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
