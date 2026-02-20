<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RiskCauseCategorySeeder extends Seeder
{
    public function run()
    {
        DB::table('risk_cause_categories')->insert([

            ['name' => 'People'],
            ['name' => 'Process'],
            ['name' => 'Product'],
            ['name' => 'Nature'],

        ]);
    }
}
