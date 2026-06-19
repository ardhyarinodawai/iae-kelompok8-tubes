<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Rent Contract API',
    version: '1.0.0',
    description: 'API documentation for Rent Contract Service'
)]
#[OA\Server(
    url: 'http://localhost:8080',
    description: 'Local Development Server'
)]
// #[OA\SecurityScheme(
//     securityScheme: 'bearerAuth',
//     type: 'http',
//     scheme: 'bearer',
//     bearerFormat: 'JWT'
// )]
#[OA\SecurityScheme(
    securityScheme: 'apiKeyAuth',
    type: 'apiKey',
    in: 'header',
    name: 'X-API-KEY',
    description: 'Input API Key here.'
)]
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
        //
    }
}
