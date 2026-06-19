<?php
 
namespace App\Services;
 
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
 
class SsoM2MService
{
    private string $baseUrl;
    private string $apiKey;
    private string $nim;
    
 
    public function __construct()
    {
        $this->baseUrl = env('IAE_SSO_BASE_URL', 'https://iae-sso.virtualfri.id');
        $this->apiKey  = env('IAE_API_KEY', 'KEY-MHS-280');
    }
    
    /**
     * Login M2M ke SSO dosen pakai API Key
     * Token di-cache selama 1 jam supaya tidak login berulang
     */
    public function getToken(): string
    {
        // Cek cache dulu, kalau ada pakai yang cached
        $cached = Cache::get('iae_m2m_token');
        if ($cached) {
            return $cached;
        }
 
        // Kalau tidak ada di cache, login ulang
        $this->nim = env('IAE_NIM', '102022400198');

        $response = Http::post("{$this->baseUrl}/api/v1/auth/token", [
        'api_key' => $this->apiKey,
        'nim'     => $this->nim,   
        ]);
 
        if (!$response->successful()) {
            Log::error('[SSO M2M] Login gagal', ['response' => $response->body()]);
            throw new \RuntimeException('SSO M2M login gagal: ' . $response->body());
        }
 
        $token = $response->json('token');
 
        if (!$token) {
            throw new \RuntimeException('Token tidak ditemukan di response SSO');
        }
 
        // Simpan ke cache selama 55 menit (kurang dari 1 jam untuk safety)
        Cache::put('iae_m2m_token', $token, 3300);
 
        Log::info('[SSO M2M] Login berhasil', ['api_key' => $this->apiKey]);
 
        return $token;
    }
}
 