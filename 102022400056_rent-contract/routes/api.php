<?php

use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Auth\SsoController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/contract-service')->group(function () {

    Route::post('/auth/sso/login', [SsoController::class, 'login']);

    Route::middleware('api.key')->group(function () {
        Route::apiResource('tenants', TenantController::class);
        Route::apiResource('contracts', ContractController::class);
    });

    Route::middleware('jwt.verify')->group(function () {
        Route::get('/auth/sso/me', [SsoController::class, 'me']);
    });
});