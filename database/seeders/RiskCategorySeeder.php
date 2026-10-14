<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiskCategorySeeder extends Seeder
{
    public function run()
    {
        DB::table('risk_categories')->insert([

            ['name' => 'Biaya'],
            ['name' => 'Lingkup'],
            ['name' => 'Mutu'],
            ['name' => 'Waktu'],
            ['name' => 'Keuangan'],
            ['name' => 'Reputasi'],
            ['name' => 'Operasional'],
            ['name' => 'Kepatuhan'],
            ['name' => 'Teknis'],
            ['name' => 'Akses'],
            ['name' => 'Data'],
            ['name' => 'Fisik'],
            ['name' => 'Pihak Ketiga'],
            ['name' => 'Sumber Daya Manusia'],
            ['name' => 'Strategis'],
            ['name' => 'Hukum/Regulasi'],
            ['name' => 'Manajemen Mutu (ISO 9001)'],
            ['name' => 'Layanan TI (ISO 20000)'],
            ['name' => 'Keamanan Informasi (ISO 27001)'],
            ['name' => 'Lingkungan (ISO 14000)'],
            ['name' => 'Keamanan, Kesehatan dan Keselamatan Kerja (OHSAS)'],

        ]);
    }
}
