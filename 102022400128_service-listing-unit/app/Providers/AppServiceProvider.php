<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Listing Unit Service API',
    version: '1.0.0',
    description: 'API documentation for Listing Unit Service'
)]
#[OA\Server(
    url: 'http://localhost:8001',
    description: 'Local Development Server'
)]

#[OA\SecurityScheme(
    securityScheme: 'apiKeyAuth',
    type: 'apiKey',
    in: 'header',
    name: 'X-API-KEY',
    description: 'Input API Key here.'
)]

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}
