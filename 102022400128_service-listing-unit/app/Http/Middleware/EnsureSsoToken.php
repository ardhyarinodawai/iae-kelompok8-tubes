<?php

namespace App\Http\Middleware;

use App\Models\Role;
use App\Models\User;
use App\Support\ApiResponse;
use Closure;
use Exception;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class EnsureSsoToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return ApiResponse::error('Unauthorized: missing Bearer token', null, 401);
        }

        try {
            $jwksUrl = 'https://iae-sso.virtualfri.id/.well-known/jwks.json';

            // Cache JWKS for 60 minutes to prevent hitting SSO server on every request
            $jwks = Cache::remember('sso_jwks', 3600, function () use ($jwksUrl) {
                $response = Http::get($jwksUrl);
                if ($response->failed()) {
                    throw new Exception('Failed to fetch JWKS from SSO server');
                }
                return $response->json();
            });

            $keys = JWK::parseKeySet($jwks);
            $decoded = JWT::decode($token, $keys);

            // Sync user to database
            $email = $decoded->email ?? ($decoded->sub ?? null);
            if (! $email) {
                throw new Exception('Token does not contain email or sub claim');
            }

            $user = User::query()->firstOrCreate(
                ['email' => $email],
                ['name' => $decoded->name ?? explode('@', $email)[0]]
            );

            // Assuming roles are present in the JWT payload (e.g. 'roles' => ['dosen', 'warga'])
            $tokenRoles = $decoded->roles ?? [];
            if (is_string($tokenRoles)) {
                $tokenRoles = explode(',', $tokenRoles);
            }

            $roleIds = [];
            foreach ($tokenRoles as $roleName) {
                $role = Role::query()->firstOrCreate(['name' => trim($roleName)]);
                $roleIds[] = $role->id;
            }

            if (!empty($roleIds)) {
                $user->roles()->syncWithoutDetaching($roleIds);
            }

            // Bind user to request
            $request->merge(['auth_user' => $user]);

        } catch (Exception $e) {
            return ApiResponse::error('Unauthorized: invalid token. ' . $e->getMessage(), null, 401);
        }

        return $next($request);
    }
}
