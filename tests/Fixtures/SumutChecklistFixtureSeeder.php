<?php

namespace Tests\Fixtures;

use App\Services\SumutEvidenceService;
use App\SumutChecklistEvidence;
use App\SumutChecklistItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/**
 * Generic, non-sensitive 27-point Sumut checklist used by the test suite,
 * with one sample evidence file on point 1. The real monitoring seeder
 * stays outside version control.
 */
class SumutChecklistFixtureSeeder extends Seeder
{
    public const SAMPLE_EVIDENCE = 'contoh-evidence.txt';

    public function run(): void
    {
        foreach (range(1, 27) as $point) {
            // Points 9 and 10 carry a searchable keyword; every third point is still on progress.
            $title = "Langkah pengamanan contoh {$point}" . (in_array($point, [9, 10], true) ? ' (Endpoint Detection)' : '');
            $item = SumutChecklistItem::updateOrCreate(
                ['point_number' => $point],
                [
                    'category' => $point <= 4 ? 'CBS & Integritas Transaksi' : 'Kategori Contoh ' . (intdiv($point - 5, 6) + 1),
                    'title' => $title,
                    'clean_title' => SumutChecklistItem::sanitizeTitle($title),
                    'progress' => '100%',
                    'progress_percent' => $point % 3 === 0 ? 50 : 100,
                    'target_date' => '31/01/2026',
                    'pic' => $point % 2 === 0 ? 'Div. TI' : 'Div. Keamanan Informasi',
                    'status_bs' => $point % 3 === 0 ? 'On Progress' : 'Done',
                    'progress_bs' => $point % 3 === 0 ? '50%' : '100%',
                    'status' => $point === 1 ? 'Ada' : 'Tidak Ada',
                    'notes' => 'Catatan evaluasi contoh.',
                    'recommendation' => 'Lampirkan Berita Acara verifikasi teknis.',
                    'sort_order' => $point,
                ]
            );

            if ($point === 1) {
                $this->createSampleEvidence($item);
            }
        }
    }

    private function createSampleEvidence(SumutChecklistItem $item): void
    {
        $folder = SumutEvidenceService::getBaseStoragePath() . DIRECTORY_SEPARATOR . '01';
        File::ensureDirectoryExists($folder);
        $path = $folder . DIRECTORY_SEPARATOR . self::SAMPLE_EVIDENCE;
        file_put_contents($path, 'Contoh evidence untuk pengujian.');

        SumutChecklistEvidence::updateOrCreate(
            ['checklist_item_id' => $item->id, 'file_name' => self::SAMPLE_EVIDENCE],
            [
                'folder_number' => '01',
                'original_filename' => self::SAMPLE_EVIDENCE,
                'file_path' => $path,
                'storage_location' => 'local_sumut',
                'file_size' => filesize($path),
                'file_extension' => 'txt',
                'mime_type' => 'text/plain',
                'notes' => 'Evidence contoh untuk pengujian.',
            ]
        );
    }
}
