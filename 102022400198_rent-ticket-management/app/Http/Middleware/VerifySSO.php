<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Illuminate\Support\Facades\Http;

class VerifySSO
{
    public function handle(Request $request, Closure $next)
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer')) {
            return response()->json(['error' => 'Unauthorized - No token'], 401);
        }

        $token = substr($authHeader, 7);

        try {
            // Ambil public key dari SSO dosen
            $jwksResponse = Http::get('https://iae-sso.virtualfri.id/api/v1/auth/jwks');
            $jwks = $jwksResponse->json();

            // Verifikasi JWT pakai RS256
            $decoded = JWT::decode($token, JWK::parseKeySet($jwks));

            // Simpan user payload ke request
            $request->merge(['sso_user' => (array) $decoded]);
            $request->merge(['sso_token' => $token]);

            // Map ke role lokal (opsional, sesuaikan dengan tabel kamu)
            $this->mapLocalRole($decoded);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthorized - ' . $e->getMessage()], 401);
        }

        return $next($request);
    }

    private function mapLocalRole($decoded)
    {
        // Sesuaikan dengan tabel users/roles lokal kamu
        $email = $decoded->email ?? null;
        if ($email) {
            $user = \App\Models\User::firstOrCreate(
                ['email' => $email],
                ['role' => 'tenant', 'name' => $decoded->name ?? $email]
            );
        }
    }
}