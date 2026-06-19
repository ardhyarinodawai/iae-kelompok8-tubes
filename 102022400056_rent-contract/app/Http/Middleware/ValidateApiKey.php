<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-KEY');

        $validKeys = array_filter([
            config('app.api_key'),
            102022400056, // Nim Saya
        ]);

        if (! $apiKey || ! in_array($apiKey, $validKeys)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or missing API Key',
                'data' => null,
            ], 401);
        }

        return $next($request);
    }
}
