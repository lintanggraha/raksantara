<?php

namespace Database\Seeders;

use App\RadarGroup;
use App\RiskQuantProfile;
use Illuminate\Database\Seeder;

class RiskQuantProfileSeeder extends Seeder
{
    public function run()
    {
        $defaults = [
            'S1' => ['potential_loss_inherent' => 6713000000, 'residual_loss_factor' => 0.3],
            'S2' => ['potential_loss_inherent' => 2740000000, 'residual_loss_factor' => 0.3],
            'S3' => ['potential_loss_inherent' => 6713000000, 'residual_loss_factor' => 0.5],
            'O1' => ['potential_loss_inherent' => 2740000000, 'residual_loss_factor' => 0.3],
            'O2' => ['potential_loss_inherent' => 12330000000, 'residual_loss_factor' => 0.3],
            'O3' => ['potential_loss_inherent' => 6713000000, 'residual_loss_factor' => 0.3],
            'O4' => ['potential_loss_inherent' => 253666666667, 'residual_loss_factor' => 0.3],
            'O5' => ['potential_loss_inherent' => 6713000000, 'residual_loss_factor' => 0.3],
            'O6' => ['potential_loss_inherent' => 2740000000, 'residual_loss_factor' => 0.3],
            'C1' => ['potential_loss_inherent' => 0, 'residual_loss_factor' => 0.3],
            'C2' => ['potential_loss_inherent' => 2740000000, 'residual_loss_factor' => 0.3],
            'F1' => ['potential_loss_inherent' => 2740000000, 'residual_loss_factor' => 0.3],
            'F2' => ['potential_loss_inherent' => 2740000000, 'residual_loss_factor' => 0.5],
        ];

        foreach ($defaults as $code => $profile) {
            $radar = RadarGroup::where('code', $code)->first();

            if (!$radar) {
                continue;
            }

            RiskQuantProfile::updateOrCreate(
                ['radar_group_id' => $radar->id],
                $profile
            );
        }
    }
}

