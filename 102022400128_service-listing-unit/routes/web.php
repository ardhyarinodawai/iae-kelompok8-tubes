<?php

use App\Http\Controllers\DocumentationController;
use App\Http\Controllers\GraphQlController;
use App\Http\Middleware\EnsureIaeApiKey;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/api/v1/listing-service/documentation'));

Route::get('/api/openapi.json', [DocumentationController::class, 'openApi']);
Route::get('/graphiql', [GraphQlController::class, 'graphiql']);
Route::get('/graphql-playground', [GraphQlController::class, 'playground']);
Route::match(['GET', 'POST'], '/graphql', [GraphQlController::class, 'handle'])
    ->middleware(EnsureIaeApiKey::class);
