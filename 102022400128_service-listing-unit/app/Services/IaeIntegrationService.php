<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IaeIntegrationService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $teamId;

    public function __construct()
    {
        $this->baseUrl = config('services.iae.sso_url', 'https://iae-sso.virtualfri.id');
        $this->apiKey = config('services.iae.api_key', 'KEY-MHS-173');
        $this->teamId = config('services.iae.team_id', 'TEAM-08');
    }

    public function getM2mToken(): string
    {
        return Cache::remember('iae_m2m_token', 55, function () {
            $response = Http::post("{$this->baseUrl}/api/v1/auth/token", [
                'api_key' => $this->apiKey,
            ]);

            if ($response->failed()) {
                throw new Exception('Failed to retrieve M2M token: ' . $response->body());
            }

            $data = $response->json();
            return $data['token'] ?? $data['access_token'] ?? throw new Exception('Token not found in response');
        });
    }

    public function sendAuditLog(string $activityName, array $logContent): ?string
    {
        try {
            $token = $this->getM2mToken();
            $logContentJson = json_encode($logContent);
            
            // Build rigid XML envelope
            $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:iae="http://iae.central/audit">
  <soap:Body>
    <iae:AuditRequest>
      <iae:TeamID>{$this->teamId}</iae:TeamID>
      <iae:ActivityName>{$activityName}</iae:ActivityName>
      <iae:LogContent><![CDATA[{$logContentJson}]]></iae:LogContent>
    </iae:AuditRequest>
  </soap:Body>
</soap:Envelope>
XML;

            $response = Http::withToken($token)
                ->withBody($xml, 'application/xml')
                ->post("{$this->baseUrl}/soap/v1/audit");

            if ($response->failed()) {
                Log::error('SOAP Audit Request Failed: ' . $response->body());
                return null;
            }

            $responseXml = $response->body();
            
            // Extract ReceiptNumber from XML response
            if (preg_match('/<iae:ReceiptNumber>(.*?)<\/iae:ReceiptNumber>/', $responseXml, $matches)) {
                return $matches[1];
            }

            return null;
        } catch (Exception $e) {
            Log::error('SOAP Audit Exception: ' . $e->getMessage());
            return null;
        }
    }

    public function publishEvent(string $topic, array $payload): void
    {
        try {
            $token = $this->getM2mToken();

            $response = Http::withToken($token)
                ->post("{$this->baseUrl}/api/v1/messages/publish", [
                    'topic' => $topic,
                    'payload' => $payload,
                ]);

            if ($response->failed()) {
                Log::error('AMQP Publish Failed: ' . $response->body());
            }
        } catch (Exception $e) {
            Log::error('AMQP Publish Exception: ' . $e->getMessage());
        }
    }
}
