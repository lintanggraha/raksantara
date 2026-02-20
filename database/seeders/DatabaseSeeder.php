<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\ImpactCategorySeeder;
use Database\Seeders\ImpactScaleSeeder;
use Database\Seeders\RadarGroupSeeder;
use Database\Seeders\RiskQuantProfileSeeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RiskCategorySeeder::class,
            RiskCauseCategorySeeder::class,
            ImpactCategorySeeder::class,
            ImpactScaleSeeder::class,
            RadarGroupSeeder::class,
            RiskQuantProfileSeeder::class,
        ]);
    }
}
