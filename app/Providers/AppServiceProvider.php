<?php

namespace App\Providers;

use App\Observers\RiskAnalyticsObserver;
use App\Risk;
use App\RiskInherentAnalysis;
use App\RiskResidualAnalysis;
use App\RiskTreatment;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        Risk::observe(RiskAnalyticsObserver::class);
        RiskTreatment::observe(RiskAnalyticsObserver::class);
        RiskInherentAnalysis::observe(RiskAnalyticsObserver::class);
        RiskResidualAnalysis::observe(RiskAnalyticsObserver::class);
        // Dashboard sections also aggregate these records.
        foreach ([
            \App\Kri::class,
            \App\KriLog::class,
            \App\RiskIncident::class,
            \App\RiskIncidentAction::class,
            \App\RiskCommitteeDecision::class,
            \App\BusinessService::class,
            \App\ServiceBia::class,
            \App\ServiceBcp::class,
            \App\ServiceBcpTest::class,
            \App\RiskAppetite::class,
        ] as $model) {
            $model::observe(RiskAnalyticsObserver::class);
        }

        if (app()->environment('production') || str_contains((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // Secure DOMPDF configuration - disable remote file inclusion to prevent XXE attacks
        $dompdfOptions = [
            'isRemoteEnabled' => false,  // Prevent loading remote URLs
            'isSvgEnabled' => false,     // Disable SVG processing if not needed
            'logOutputFile' => storage_path('logs/dompdf.log'),
            'tempDir' => storage_path('framework/cache'),
        ];
        
        if (function_exists('config_dompdf')) {
            @ini_set('auto_prepend_file', null);
        }
    }
}
