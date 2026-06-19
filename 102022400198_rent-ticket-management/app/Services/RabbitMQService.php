<?php
 
namespace App\Services;
 
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
 
class RabbitMQService
{
    private string $publishUrl;
    private SsoM2MService $ssoM2M;
 
    public function __construct(SsoM2MService $ssoM2M)
    {
        $this->publishUrl = env('IAE_SSO_BASE_URL', 'https://iae-sso.virtualfri.id') . '/api/v1/messages/publish';
        $this->ssoM2M     = $ssoM2M;
    }
 
    public function publishTicketCreated(array $ticketData): bool
    {
        $m2mToken = $this->ssoM2M->getToken();
 
        // Payload yang dibutuhkan: routing_key + message (object/string)
        $payload = [
            'routing_key' => 'ticket.created',
            'message'     => [
                'event'   => 'ticket.created',
                'service' => 'manajemen-tiket-tenant',
                'data'    => [
                    'ticket_id'    => $ticketData['id'],
                    'listing_id'   => $ticketData['listing_id'],
                    'contract_id'  => $ticketData['contract_id'],
                    'tenant_name'  => $ticketData['tenant_name'],
                    'tenant_email' => $ticketData['tenant_email'],
                    'description'  => $ticketData['description'],
                    'timestamp'    => now()->toISOString(),
                ],
            ],
        ];
 
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $m2mToken,
                'Content-Type'  => 'application/json',
            ])->post($this->publishUrl, $payload);
 
            if ($response->successful()) {
                Log::info('[RabbitMQ] Publish berhasil', ['event' => 'ticket.created']);
                return true;
            }
 
            Log::warning('[RabbitMQ] Publish gagal', ['response' => $response->body()]);
            return false;
 
        } catch (\Exception $e) {
            Log::error('[RabbitMQ] Error: ' . $e->getMessage());
            return false;
        }
    }
}