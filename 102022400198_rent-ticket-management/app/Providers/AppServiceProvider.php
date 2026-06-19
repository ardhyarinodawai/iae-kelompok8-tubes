<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Mendefinisikan konstanta HOST global agar bisa dibaca oleh L5-Swagger secara dinamis
        if (!defined('L5_SWAGGER_CONST_HOST')) {
            define('L5_SWAGGER_CONST_HOST', config('app.url', 'http://localhost:8002'));
        }
    }
}
