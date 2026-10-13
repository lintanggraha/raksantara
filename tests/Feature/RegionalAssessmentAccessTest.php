<?php

namespace Tests\Feature;

use App\Services\SumutEvidenceService;
use App\SumutChecklistEvidence;
use App\SumutChecklistItem;
use App\User;
use Tests\Fixtures\SumutChecklistFixtureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class RegionalAssessmentAccessTest extends TestCase
{
    use RefreshDatabase;

    private string $evidenceDir;

    protected function setUp(): void
    {
        parent::setUp();
        // Point Sumut evidence at the storage fallback so the test never touches a real D: folder.
        config()->set('regional_assessment.sumut.evidence_path', '');
        $this->evidenceDir = SumutEvidenceService::getBaseStoragePath() . DIRECTORY_SEPARATOR . 'regional-access-test';
        File::ensureDirectoryExists($this->evidenceDir);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->evidenceDir);
        parent::tearDown();
    }

    public function test_non_compliance_user_is_denied_regional_assessment_routes(): void
    {
        $this->seed(SumutChecklistFixtureSeeder::class);
        $user = $this->createUser('BT - BANKING APPLICATIONS');

        foreach ([
            route('sulteng.dashboard'),
            route('sulteng.items'),
            route('sulteng.export-excel'),
            route('sumut.dashboard'),
            route('sumut.items'),
            route('sumut.export-excel'),
            route('assessment.sumut'),
            route('assessment.sulteng'),
        ] as $url) {
            $this->actingAs($user)->get($url)->assertForbidden();
        }

        $item = SumutChecklistItem::query()->firstOrFail();
        $this->actingAs($user)
            ->post(route('sumut.items.update', $item), ['status' => 'Diterima'])
            ->assertForbidden();
    }

    public function test_non_compliance_user_does_not_see_assessment_menu(): void
    {
        $user = $this->createUser('BT - BANKING APPLICATIONS');

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('data-assessment-menu', false);
    }

    public function test_compliance_user_and_superadmin_can_open_regional_assessment(): void
    {
        $this->actingAs($this->createUser('CO - COMPLIANCE'))
            ->get(route('sumut.dashboard'))
            ->assertOk();

        $this->actingAs($this->createUser('BT - BANKING APPLICATIONS', true))
            ->get(route('sulteng.dashboard'))
            ->assertOk();
    }

    public function test_sumut_preview_forces_download_for_active_content(): void
    {
        $evidence = $this->createSumutEvidence('payload.html', '<script>alert(1)</script>', 'text/html');

        $response = $this->actingAs($this->createUser('CO - COMPLIANCE'))
            ->get(route('sumut.evidence.preview', $evidence));

        $response->assertOk();
        $this->assertStringStartsWith('attachment', (string) $response->headers->get('content-disposition'));
        $this->assertSame('application/octet-stream', $response->headers->get('content-type'));
    }

    public function test_sumut_preview_renders_safe_types_inline_in_a_sandbox(): void
    {
        $evidence = $this->createSumutEvidence('notes.txt', 'evidence notes', 'text/html');

        $response = $this->actingAs($this->createUser('CO - COMPLIANCE'))
            ->get(route('sumut.evidence.preview', $evidence));

        $response->assertOk();
        $this->assertStringStartsWith('inline', (string) $response->headers->get('content-disposition'));
        $this->assertStringContainsString('text/plain', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('sandbox', (string) $response->headers->get('content-security-policy'));
    }

    public function test_sumut_path_check_rejects_sibling_folder_with_same_prefix(): void
    {
        $base = rtrim(SumutEvidenceService::getBaseStoragePath(), '\\/');
        $sibling = $base . '_sibling';
        File::ensureDirectoryExists($sibling);
        file_put_contents($sibling . DIRECTORY_SEPARATOR . 'secret.txt', 'secret');

        try {
            $this->assertFalse(SumutEvidenceService::isPathSafe($sibling . DIRECTORY_SEPARATOR . 'secret.txt'));
            $this->assertTrue(SumutEvidenceService::isPathSafe($this->evidenceDir));
        } finally {
            File::deleteDirectory($sibling);
        }
    }

    private function createSumutEvidence(string $fileName, string $contents, string $mimeType): SumutChecklistEvidence
    {
        $this->seed(SumutChecklistFixtureSeeder::class);
        $item = SumutChecklistItem::query()->firstOrFail();
        $path = $this->evidenceDir . DIRECTORY_SEPARATOR . $fileName;
        file_put_contents($path, $contents);

        return SumutChecklistEvidence::create([
            'checklist_item_id' => $item->id,
            'folder_number' => sprintf('%02d', $item->point_number),
            'file_name' => $fileName,
            'original_filename' => $fileName,
            'file_path' => $path,
            'file_size' => strlen($contents),
            'mime_type' => $mimeType,
            'file_extension' => pathinfo($fileName, PATHINFO_EXTENSION),
        ]);
    }

    private function createUser(string $division, bool $isSuperadmin = false): User
    {
        $suffix = Str::lower(Str::random(8));

        return User::create([
            'name' => 'Regional ' . $suffix,
            'username' => 'regional_' . $suffix,
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
