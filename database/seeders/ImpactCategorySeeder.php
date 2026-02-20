<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImpactCategorySeeder extends Seeder
{
    public function run()
    {
        DB::table('impact_categories')->insert([
            ['name' => 'Revenue'],
            ['name' => 'EBITDA'],
            ['name' => 'Finansial'],
            ['name' => 'Financial Siber'],
            ['name' => 'Tuntutan Hukum'],
            ['name' => 'Publikasi Negatif'],
            ['name' => 'SLA'],
            ['name' => 'Penyelesaian Proyek'],
            ['name' => 'Keselamatan Kerja'],
            ['name' => 'Keamanan Informasi'],
            ['name' => 'Reputasi'],
            ['name' => 'Operasional'],
            ['name' => 'Kepatuhan'],
        ]);
    }
}
