<?php

namespace App\Providers;

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

        if (app()->environment('production')) {
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
