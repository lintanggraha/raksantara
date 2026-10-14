<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiskScoreMatrixTable extends Migration
{
    public function up()
    {
        Schema::create('risk_score_matrix', function (Blueprint $table) {
            $table->id();
            $table->integer('probability_score');
            $table->integer('impact_score');
            $table->integer('risk_score');
            $table->timestamps();

            $table->unique(['probability_score', 'impact_score']);
        });

        // Populate the matrix
        $matrix = [
            [1, 1, 1], [1, 2, 5], [1, 3, 10], [1, 4, 15], [1, 5, 20],
            [2, 1, 2], [2, 2, 6], [2, 3, 11], [2, 4, 16], [2, 5, 21],
            [3, 1, 3], [3, 2, 8], [3, 3, 13], [3, 4, 18], [3, 5, 23],
            [4, 1, 4], [4, 2, 9], [4, 3, 14], [4, 4, 19], [4, 5, 24],
            [5, 1, 7], [5, 2, 12], [5, 3, 17], [5, 4, 22], [5, 5, 25],
        ];

        foreach ($matrix as [$prob, $impact, $risk]) {
            DB::table('risk_score_matrix')->insert([
                'probability_score' => $prob,
                'impact_score' => $impact,
                'risk_score' => $risk,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('risk_score_matrix');
    }
}
