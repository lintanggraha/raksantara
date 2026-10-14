<?php

namespace Tests\Feature;

use App\Kri;
use App\Risk;
use App\RiskCategory;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KriLibraryImportTest extends TestCase
{
    use RefreshDatabase;

    private string $path = 'storage/framework/testing/kri_library_test.json';

    protected function tearDown(): void
    {
        @unlink(base_path($this->path));
        parent::tearDown();
    }

    public function test_library_is_imported_idempotently_and_rolled_back_safely(): void
    {
        [$a, $b] = $this->createRisks(2);
        $this->writeLibrary([
            $this->item($a->risk_id, 'Durasi EOD melewati jadwal (menit)', 'lower_is_better', 15, 45),
            $this->item($b->risk_id, 'Ketersediaan layanan BI-FAST (%)', 'higher_is_better', 99.5, 99),
        ]);

        $this->artisan('kri:import-library', ['path' => $this->path])->assertSuccessful();
        $this->assertSame(2, Kri::count());
        $kri = Kri::where('risk_id', $a->id)->firstOrFail();
        $this->assertNull($kri->current_value);
        $this->assertSame('unverified', $kri->data_quality_status);
        $this->assertSame(1440, (int) $kri->expected_refresh_minutes);

        // Re-running updates in place and keeps user-created KRIs untouched.
        Kri::create(['risk_id' => $a->id, 'metric_name' => 'User KRI', 'threshold_type' => 'lower_is_better', 'status' => 'Green']);
        $this->writeLibrary([
            $this->item($a->risk_id, 'Durasi EOD melewati jadwal (menit)', 'lower_is_better', 10, 30),
            $this->item($b->risk_id, 'Ketersediaan layanan BI-FAST (%)', 'higher_is_better', 99.5, 99),
        ]);
        $this->artisan('kri:import-library', ['path' => $this->path])->assertSuccessful();
        $this->assertSame(3, Kri::count());
        $this->assertEquals(30, (float) $kri->fresh()->threshold_critical);

        // A library KRI with a reading survives rollback.
        Kri::where('risk_id', $b->id)->where('source_reference', 'kri-library-2026')->update(['current_value' => 99.9]);
        $this->artisan('kri:import-library', ['--rollback' => true])->assertSuccessful();
        $this->assertEqualsCanonicalizing(['User KRI', 'Ketersediaan layanan BI-FAST (%)'], Kri::pluck('metric_name')->all());
    }

    public function test_invalid_library_writes_nothing(): void
    {
        [$a] = $this->createRisks(1);
        $this->writeLibrary([
            $this->item($a->risk_id, 'Inverted thresholds (menit)', 'lower_is_better', 45, 15),
            $this->item('RISK-UNKNOWN', 'Unknown risk (unit)', 'lower_is_better', 1, 2),
        ]);

        $this->artisan('kri:import-library', ['path' => $this->path])->assertFailed();
        $this->assertSame(0, Kri::count());
    }

    private function item(string $code, string $name, string $type, float $warn, float $critical): array
    {
        return [
            'risk_code' => $code,
            'metric_name' => $name,
            'description' => 'Definisi dan rumus uji.',
            'threshold_type' => $type,
            'threshold_warn' => $warn,
            'threshold_critical' => $critical,
            'data_source' => 'api',
            'source_identifier' => 'Monitoring uji',
            'expected_refresh_minutes' => 1440,
        ];
    }

    private function writeLibrary(array $items): void
    {
        @mkdir(dirname(base_path($this->path)), 0755, true);
        file_put_contents(base_path($this->path), json_encode($items));
    }

    private function createRisks(int $count): array
    {
        $user = User::create([
            'name' => 'KRI Importer', 'username' => 'kri_importer', 'division' => 'CO - COMPLIANCE',
            'division_verified_at' => now(), 'force_password_change' => false,
            'email' => 'kri-importer@example.test', 'password' => Hash::make('password123'),
        ]);
        $category = RiskCategory::query()->value('id') ?? RiskCategory::query()->insertGetId(['name' => 'KRI Category', 'created_at' => now(), 'updated_at' => now()]);

        return array_map(fn ($i) => Risk::create([
            'risk_id' => 'KRI-TEST-' . $i, 'year' => 2026, 'risk_title' => 'Risk ' . $i,
            'risk_category_id' => $category, 'risk_owner' => 'CO - COMPLIANCE', 'status' => 'Open', 'created_by' => $user->id,
        ]), range(1, $count));
    }
}
