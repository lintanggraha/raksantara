<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthenticatedUserController;
use App\Http\Controllers\Api\RiskScoreMatrixController;
use App\Http\Controllers\Api\KriIngestionController;

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

Route::middleware('auth:api')->get('/user', AuthenticatedUserController::class);

// Risk Score Matrix API
Route::get('/risk-score-matrix', RiskScoreMatrixController::class);

Route::post('/kri/ingestion', [KriIngestionController::class, 'store'])
    ->middleware('throttle:120,1')
    ->name('api.kri.ingestion');
