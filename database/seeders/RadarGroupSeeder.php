<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RadarGroupSeeder extends Seeder
{
    public function run()
    {
        $rows = [
            ['code' => 'S1', 'description' => 'S1 : Perubahan kondisi politik Client'],
            ['code' => 'S2', 'description' => 'S2 : Kurang dalam eksplorasi teknologi'],
            ['code' => 'S3', 'description' => 'S3 : Terlambat implementasi kebijakan'],
            ['code' => 'O1', 'description' => 'O1 : Hilangnya peluang penjualan produk'],
            ['code' => 'O2', 'description' => 'O2 : Gangguan operasional infrastruktur'],
            ['code' => 'O3', 'description' => 'O3 : Gagal dalam melakukan respon terhadap insiden'],
            ['code' => 'O4', 'description' => 'O4 : Gagal dalam memberikan solusi tepat waktu'],
            ['code' => 'O5', 'description' => 'O5 : Gagal dalam memberikan SLA'],
            ['code' => 'O6', 'description' => 'O6 : Kecukupan sumber daya'],
            ['code' => 'C1', 'description' => 'C1 : Lisensi / Hak atas kekayaan intelektual'],
            ['code' => 'C2', 'description' => 'C2 : Hambatan dalam implementasi Good Governance'],
            ['code' => 'F1', 'description' => 'F1 : Perhitungan biaya proyek tidak akurat'],
            ['code' => 'F2', 'description' => 'F2 : Beban penalty terhadap SLA (Project)'],
            ['code' => 'N/A', 'description' => 'None'],
        ];

        foreach ($rows as $row) {
            DB::table('radar_groups')->updateOrInsert(
                ['code' => $row['code']],
                [
                    'description' => $row['description'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
