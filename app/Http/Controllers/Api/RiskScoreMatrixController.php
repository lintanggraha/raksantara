<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\RiskScoreMatrix;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RiskScoreMatrixController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'prob' => 'required|integer|between:1,5',
            'impact' => 'required|integer|between:1,5',
        ]);

        $probability = (int) $validated['prob'];
        $impact = (int) $validated['impact'];
        $score = RiskScoreMatrix::getScore($probability, $impact);

        return response()->json([
            'probability_score' => $probability,
            'impact_score' => $impact,
            'risk_score' => $score,
            'risk_level' => RiskScoreMatrix::getRiskLevel($score),
        ]);
    }
}
