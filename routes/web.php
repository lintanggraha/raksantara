<?php

use App\ImpactScale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RiskController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;

Auth::routes([
    'register' => config('auth.allow_public_registration', false),
]);

Route::get('/', function () {
    return redirect()->route('risks.index');
})->middleware('auth');

Route::get('/home', function () {
    return redirect()->route('risks.index');
})->middleware('auth');

Route::get('/dashboard', function () {
    return redirect()->route('risks.index');
})->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/impact-scales/{id}', function ($id) {
        return ImpactScale::where('impact_category_id', $id)->get();
    })->whereNumber('id');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/change-password', [ProfileController::class, 'showChangePassword'])->name('change.password');
    Route::post('/change-password', [ProfileController::class, 'changePassword'])
        ->middleware('throttle:10,1')
        ->name('profile.changePassword');

    Route::get('/risk-heatmap', [RiskController::class, 'heatmap'])->name('risks.heatmap');
    Route::get('/risk-recap', [RiskController::class, 'recap'])->name('risks.recap');
    Route::view('/how-to', 'how-to')->name('howto');
    Route::middleware('compliance.division')->group(function () {
        Route::view('/compliance/internal-audit', 'compliance.internal-audit')->name('compliance.internal-audit');
        Route::view('/compliance', 'compliance.compliance')->name('compliance.compliance');
        Route::view('/compliance/risk-management', 'compliance.risk-management')->name('compliance.risk-management');
    });

    Route::get('/reports/{scope}/{format}', [ReportController::class, 'export'])
        ->where('scope', 'risks|heatmap|recap|overall')
        ->where('format', 'xls|pdf|pptx')
        ->name('reports.export');
    Route::resource('risks', RiskController::class);
});
