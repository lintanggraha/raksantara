<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\RiskScoreMatrix;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Risk Score Matrix API
Route::get('/risk-score-matrix', function (Request $request) {
    $validated = $request->validate([
        'prob' => 'required|integer|between:1,5',
        'impact' => 'required|integer|between:1,5',
    ]);

    $prob = (int) $validated['prob'];
    $impact = (int) $validated['impact'];
    
    $score = RiskScoreMatrix::getScore($prob, $impact);
    $level = RiskScoreMatrix::getRiskLevel($score);
    
    return response()->json([
        'probability_score' => $prob,
        'impact_score' => $impact,
        'risk_score' => $score,
        'risk_level' => $level,
    ]);
});
