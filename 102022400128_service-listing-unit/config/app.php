<?php

use Illuminate\Support\Facades\Facade;

return [
    'name' => env('APP_NAME', 'Listing Unit Service'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost:8001'),
    'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),
    'locale' => env('APP_LOCALE', 'en'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),
    'key' => env('APP_KEY'),
    'cipher' => 'AES-256-CBC',
    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],
    'service_name' => env('SERVICE_NAME', 'Listing-Unit-Service'),
    'api_version' => env('API_VERSION', 'v1'),
    'aliases' => Facade::defaultAliases()->merge([])->toArray(),
];
