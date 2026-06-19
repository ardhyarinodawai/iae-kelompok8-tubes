<?php

namespace App\Http\Middleware;

use App\Services\SsoService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyJwtToken
{
    public function __construct(private SsoService $ssoService) {}

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // 1. Ambil token dari header Authorization: Bearer <token>
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak ditemukan. Harap login via SSO terlebih dahulu.',
            ], 401);
        }

        // 2. Verify dan decode token menggunakan public key JWKS
        try {
            $payload = $this->ssoService->decodeAndVerify($token);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid atau sudah expired.',
                'error'   => $e->getMessage(),
            ], 401);
        }

        // 3. Petakan ke SsoUser lokal (atau ambil yang sudah ada)
        $ssoUser = $this->ssoService->mapToLocalRole($token, $payload);

        // 4. Cek role jika middleware dipanggil dengan parameter
        //    Contoh: Route::middleware('jwt.verify:admin,owner')
        if (! empty($roles) && ! in_array($ssoUser->localRole->name, $roles)) {
            return response()->json([
                'success' => false,
                'message' => "Akses ditolak. Role '{$ssoUser->localRole->name}' tidak memiliki izin.",
                'required_roles' => $roles,
            ], 403);
        }

        // 5. Simpan data user di request agar bisa diakses di controller
        $request->merge(['sso_user' => $ssoUser]);
        $request->setUserResolver(fn () => $ssoUser);

        return $next($request);
    }
}