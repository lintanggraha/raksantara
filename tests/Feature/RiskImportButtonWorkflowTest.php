<?php

namespace Tests\Feature;

use App\AuditLog;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class RiskImportButtonWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_compliance_user_can_trigger_import_via_web_endpoint(): void
    {
        Storage::fake('local');

        $user = $this->createUser('CO - COMPLIANCE', false);
        $this->actingAs($user);

        Artisan::shouldReceive('call')
            ->once()
            ->andReturn(0);
        Artisan::shouldReceive('output')
            ->once()
            ->andReturn("Import selesai.\nRows imported: 10");

        $response = $this->post(route('risks.import.store'), [
            'file' => UploadedFile::fake()->create('rm02-template.xls', 50, 'application/vnd.ms-excel'),
            'year' => (int) now()->year,
            'dry_run' => 1,
        ]);

        $response->assertRedirect(route('risks.index'));
        $response->assertSessionHas('success');
        $response->assertSessionHas('import_output');

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $user->id,
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'event' => 'risk.import.completed',
        ]);

        $log = AuditLog::query()
            ->where('event', 'risk.import.completed')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('rm02-template.xls', data_get($log->after_state, 'file_name'));
        $this->assertTrue((bool) data_get($log->after_state, 'dry_run'));
    }

    public function test_superadmin_cannot_trigger_import_via_web_endpoint(): void
    {
        $superadmin = $this->createUser('CO - COMPLIANCE', true);
        $this->actingAs($superadmin);

        $this->post(route('risks.import.store'), [
            'file' => UploadedFile::fake()->create('rm02-template.xls', 50, 'application/vnd.ms-excel'),
        ])->assertForbidden();
    }

    public function test_failed_import_is_recorded_in_audit_trail(): void
    {
        Storage::fake('local');

        $user = $this->createUser('CO - COMPLIANCE', false);
        $this->actingAs($user);

        Artisan::shouldReceive('call')
            ->once()
            ->andReturn(1);
        Artisan::shouldReceive('output')
            ->once()
            ->andReturn("Import gagal.\nRows imported: 0");

        $response = $this->post(route('risks.import.store'), [
            'file' => UploadedFile::fake()->create('broken-template.xls', 50, 'application/vnd.ms-excel'),
        ]);

        $response->assertRedirect(route('risks.index'));
        $response->assertSessionHas('error');
        $response->assertSessionHas('import_output');

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $user->id,
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'event' => 'risk.import.failed',
        ]);

        $log = AuditLog::query()
            ->where('event', 'risk.import.failed')
            ->latest('id')
            ->first();

        $this->assertNotNull($log);
        $this->assertSame('command_exit_non_zero', data_get($log->after_state, 'reason'));
        $this->assertSame('broken-template.xls', data_get($log->after_state, 'file_name'));
    }

    private function createUser(string $division, bool $isSuperadmin): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Import User ' . $suffix,
            'username' => 'import_' . $suffix,
            'division' => $division,
            'pending_division' => null,
            'division_verified_at' => now(),
            'is_superadmin' => $isSuperadmin,
            'email' => $suffix . '@example.test',
            'password' => Hash::make('password123'),
        ]);
    }
}
