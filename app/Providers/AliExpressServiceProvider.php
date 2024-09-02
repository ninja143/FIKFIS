<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AliExpressServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // $this->app->singleton('aliExpressClient', function ($app) {
        //     $apiUrl = env('ALIEXPRESS_API_URL');
        //     $appKey = env('ALIEXPRESS_APPKEY');
        //     $appSecret = env('ALIEXPRESS_APPSECRET');
        //     return new IopClientImpl($apiUrl, $appKey, $appSecret);
        // });

        // $this->app->singleton('aliExpressRequest', function () {
        //     return new IopRequest();
        // });
        // Include the SDK's Autoloader.php
        require_once app_path('Libraries/AliExpress/IopSdk.php');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        
        $this->publishes([
            __DIR__.'/../../config/aliexpress.php' => config_path('aliexpress.php'),
        ], 'config');
    }
}
