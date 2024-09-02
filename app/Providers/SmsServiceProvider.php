<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SmsService;

class SmsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(SmsService::class, function ($app) {
            return new SmsService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
