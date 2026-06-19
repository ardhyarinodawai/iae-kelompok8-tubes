<?php

use App\Http\Controllers\ListingController;
use App\Http\Middleware\EnsureIaeApiKey;
use App\Http\Middleware\EnsureSsoToken;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/listing-service')
    ->group(function (): void {
        Route::get('/listings', [ListingController::class, 'index']);
        Route::get('/listings/{id}', [ListingController::class, 'show'])->whereNumber('id');
        Route::post('/listings', [ListingController::class, 'store']);
    });
