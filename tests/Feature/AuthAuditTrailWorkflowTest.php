<?php

namespace Tests\Feature;

use App\AuditLog;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuthAuditTrailWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_and_logout_are_recorded_in_audit_trail(): void
    {
        $user = $this->createUser('CO - COMPLIANCE', false);

        $login = $this->post(route('login'), [
            'username' => $user->username,
            'password' => 'password123',
        ]);
        $login->assertStatus(302);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'auth.login.succeeded',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
        ]);

        $logout = $this->post(route('logout'));
        $logout->assertStatus(302);

        $this->assertDatabaseHas('audit_logs', [
            'event' => 'auth.logout',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
        ]);
    }

    public function test_failed_login_is_recorded_in_audit_trail(): void
    {
        $user = $this->createUser('CO - COMPLIANCE', false);

        $response = $this->from(route('login'))->post(route('login'), [
            'username' => $user->username,
            'password' => 'wrong-password',
        ]);
        $response->assertStatus(302);

        $failedLog = AuditLog::query()
            ->where('event', 'auth.login.failed')
            ->latest('id')
            ->first();

        $this->assertNotNull($failedLog);
        $this->assertSame($user->username, (string) data_get($failedLog->after_state, 'identifier'));
    }

    public function test_export_report_is_recorded_in_audit_trail(): void
    {
        $user = $this->createUser('CO - COMPLIANCE', false);
        $this->actingAs($user);

        $response = $this->get(route('reports.export', [
            'scope' => 'monitoring',
            'format' => 'pdf',
        ]));
        $response->assertOk();

        $exportLog = AuditLog::query()
            ->where('event', 'report.export.generated')
            ->latest('id')
            ->first();

        $this->assertNotNull($exportLog);
        $this->assertSame($user->id, (int) $exportLog->actor_id);
        $this->assertSame('monitoring', (string) data_get($exportLog->after_state, 'scope'));
        $this->assertSame('pdf', (string) data_get($exportLog->after_state, 'format'));
    }

    public function test_duplicate_export_requests_in_short_window_are_recorded_once(): void
    {
        $user = $this->createUser('CO - COMPLIANCE', false);
        $this->actingAs($user);

        $route = route('reports.export', [
            'scope' => 'board',
            'format' => 'pdf',
        ]);

        $first = $this->get($route);
        $first->assertOk();

        $second = $this->get($route);
        $second->assertOk();

        $matchedCount = AuditLog::query()
            ->where('event', 'report.export.generated')
            ->where('actor_id', $user->id)
            ->get()
            ->filter(function (AuditLog $log): bool {
                return (string) data_get($log->after_state, 'scope') === 'board'
                    && (string) data_get($log->after_state, 'format') === 'pdf';
            })
            ->count();

        $this->assertSame(1, $matchedCount);
    }

    private function createUser(string $division, bool $isSuperadmin): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Audit User ' . $suffix,
            'username' => 'audit_' . $suffix,
            'division' => $division,
            'pending_division' => null,
            'division_verified_at' => now(),
            'is_superadmin' => $isSuperadmin,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }
}
