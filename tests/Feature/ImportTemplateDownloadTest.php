<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ImportTemplateDownloadTest extends TestCase
{
    use DatabaseTransactions;

    public function test_compliance_user_can_download_import_templates(): void
    {
        $user = $this->createUser('CO - COMPLIANCE', false);
        $this->actingAs($user);

        $templates = [
            'risk-register' => 'risk-register-import-template.xlsx',
            'audit-dashboard' => 'audit-dashboard-import-template.xlsx',
            'ojk-mitigation' => 'ojk-mitigation-import-template.xlsx',
        ];

        foreach ($templates as $template => $filename) {
            $response = $this->get(route('import-templates.download', $template));

            $response->assertOk();
            $response->assertDownload($filename);
            $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            $this->assertNotEmpty($response->streamedContent());
        }
    }

    public function test_superadmin_cannot_download_import_templates(): void
    {
        $user = $this->createUser('CO - COMPLIANCE', true);
        $this->actingAs($user);

        $this->get(route('import-templates.download', 'risk-register'))
            ->assertForbidden();
    }

    private function createUser(string $division, bool $isSuperadmin): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Template User ' . $suffix,
            'username' => 'template_' . $suffix,
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
