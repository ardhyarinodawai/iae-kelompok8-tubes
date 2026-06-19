<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIaeApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $providedKey = $request->header('X-IAE-KEY');
        $expectedKey = config('services.iae.api_key', '102022400128');

        if ($providedKey !== $expectedKey) {
            return ApiResponse::error('Unauthorized: invalid or missing API key', null, 401);
        }

        return $next($request);
    }
}
