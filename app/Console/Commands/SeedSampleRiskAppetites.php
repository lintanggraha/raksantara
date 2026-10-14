<?php

namespace App\Console\Commands;

use App\RiskAppetite;
use App\RiskCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Proposed (unapproved) risk appetite statements calibrated on the current
 * risk register and loss history. They are drafts for the risk management
 * function to review and for the Board to approve; nothing here is approved.
 */
class SeedSampleRiskAppetites extends Command
{
    protected $signature = 'appetite:seed-sample {--rollback : Remove sample appetites that are still unapproved and not used as an override}';

    protected $description = 'Create proposed risk appetite statements calibrated on the existing register';

    private const DRAFT_NOTE = ' Draf usulan, dikalibrasi dari register risk dan riwayat loss event; perlu ditinjau fungsi manajemen risiko dan disahkan Direksi.';

    /**
     * category name (null = default) => [name, metric, appetite, tolerance, unit, statement]
     * Score scale follows the risk matrix: Low < 6, Low to Moderate 6–11, Moderate 12–15, Moderate to High 16–19, High >= 20.
     */
    private function catalogue(): array
    {
        $score = RiskAppetite::THRESHOLD_METRIC_RESIDUAL_SCORE;
        $loss = RiskAppetite::THRESHOLD_METRIC_RESIDUAL_LOSS;

        return [
            null => ['Appetite umum perusahaan', $score, 5, 11, 'Skor',
                'Perusahaan menerima risiko residual pada tingkat Rendah. Risiko Rendah–Menengah masih ditoleransi sementara dengan rencana mitigasi bertenggat, sedangkan risiko Menengah ke atas tidak dapat diterima.'],
            'Kepatuhan' => ['Kepatuhan regulasi', $score, 3, 5, 'Skor',
                'Perusahaan tidak menoleransi pelanggaran ketentuan OJK, BI, dan PPATK yang dapat berujung sanksi. Seluruh risiko kepatuhan wajib berada pada tingkat Rendah.'],
            'Hukum/Regulasi' => ['Hukum & regulasi', $score, 3, 5, 'Skor',
                'Perusahaan tidak menoleransi eksposur hukum yang dapat menimbulkan sengketa material atau sanksi regulator terhadap bank klien.'],
            'Keamanan Informasi (ISO 27001)' => ['Keamanan informasi & siber', $score, 3, 5, 'Skor',
                'Sejalan dengan POJK 11/POJK.03/2022 dan SEOJK 29/SEOJK.03/2022, perusahaan memiliki appetite rendah terhadap insiden keamanan informasi dan siber yang dapat mengganggu layanan atau data nasabah bank klien.'],
            'Akses' => ['Pengendalian akses', $score, 3, 5, 'Skor',
                'Perusahaan memiliki appetite rendah terhadap akses tidak sah, penyalahgunaan akun istimewa, dan pengambilalihan akun. Riwayat fraud QRIS, RTOL, dan mobile banking menunjukkan dampak nyata hingga miliaran rupiah per kejadian.'],
            'Data' => ['Perlindungan data', $score, 3, 5, 'Skor',
                'Sejalan dengan UU 27/2022 tentang Pelindungan Data Pribadi, perusahaan tidak menoleransi kebocoran atau penyalahgunaan data pribadi nasabah.'],
            'Reputasi' => ['Reputasi', $score, 3, 6, 'Skor',
                'Perusahaan memiliki appetite rendah terhadap kejadian yang dapat menurunkan kepercayaan bank klien atau nasabahnya secara luas.'],
            'Strategis' => ['Strategis & inovasi', $score, 6, 12, 'Skor',
                'Perusahaan menerima risiko strategis hingga Rendah–Menengah untuk mendorong inovasi produk digital dan pertumbuhan, selama didukung analisis bisnis dan dipantau komite.'],
            'Keuangan' => ['Kerugian finansial per risiko', $loss, 250000000, 500000000, 'IDR',
                'Potensi kerugian residual per risiko keuangan ditargetkan di bawah Rp 250 juta dan tidak boleh melebihi Rp 500 juta. Batas ini perlu dikalibrasi terhadap materialitas laba tahunan.'],
            'Biaya' => ['Pembengkakan biaya', $loss, 250000000, 500000000, 'IDR',
                'Potensi pembengkakan biaya residual per risiko ditargetkan di bawah Rp 250 juta dan tidak boleh melebihi Rp 500 juta, termasuk biaya proyek dan penalti SLA.'],
            'Lingkup' => ['Lingkup & kelangsungan proyek', $loss, 250000000, 500000000, 'IDR',
                'Potensi kerugian residual akibat perubahan lingkup, kegagalan integrasi, atau gejolak eksternal ditargetkan di bawah Rp 250 juta dan tidak boleh melebihi Rp 500 juta per risiko.'],
        ];
    }

    public function handle(): int
    {
        if ($this->option('rollback')) {
            return $this->rollback();
        }

        $categories = RiskCategory::query()->pluck('id', 'name');
        $created = 0;
        $skipped = [];

        DB::transaction(function () use ($categories, &$created, &$skipped) {
            foreach ($this->catalogue() as $categoryName => [$name, $metric, $appetite, $tolerance, $unit, $statement]) {
                $categoryName = $categoryName === '' ? null : $categoryName;
                $categoryId = $categoryName === null ? null : ($categories[$categoryName] ?? null);
                if ($categoryName !== null && $categoryId === null) {
                    $skipped[] = $categoryName . ' (kategori tidak ditemukan)';
                    continue;
                }

                $activeExists = RiskAppetite::query()->where('is_active', true)
                    ->when($categoryId === null, fn ($q) => $q->whereNull('risk_category_id'), fn ($q) => $q->where('risk_category_id', $categoryId))
                    ->exists();
                if ($activeExists) {
                    $skipped[] = ($categoryName ?? 'Default') . ' (sudah ada appetite aktif)';
                    continue;
                }

                RiskAppetite::create([
                    'name' => $name,
                    'risk_category_id' => $categoryId,
                    'threshold_metric' => $metric,
                    'appetite_statement' => $statement . self::DRAFT_NOTE,
                    'appetite_limit_value' => $appetite,
                    'tolerance_limit_value' => $tolerance,
                    'unit' => $unit,
                    'is_active' => true,
                    'next_review_at' => now()->addMonths(3)->toDateString(),
                ]);
                $created++;
            }
        });

        $this->info("Sample risk appetite: {$created} created.");
        foreach ($skipped as $reason) {
            $this->line('  skipped: ' . $reason);
        }

        return self::SUCCESS;
    }

    private function rollback(): int
    {
        $names = array_column($this->catalogue(), 0);
        $removed = RiskAppetite::query()
            ->whereIn('name', $names)
            ->whereNull('approval_reference')
            ->doesntHave('risks')
            ->where('appetite_statement', 'like', '%Draf usulan%')
            ->delete();

        $this->info("Removed {$removed} unapproved sample appetite(s).");

        return self::SUCCESS;
    }
}
