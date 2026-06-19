<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SoapAuditService
{
    private string $endpoint;
    private string $teamId;

    public function __construct()
    {
        $this->endpoint = env('SOAP_AUDIT_URL');
        $this->teamId   = env('CENTRAL_TEAM_API_KEY');
    }

    public function auditContract(array $contractData, string $bearerToken): ?string
    {
        $xmlEnvelope = $this->buildEnvelope($contractData);

        Log::debug('SOAP Request', ['xml' => $xmlEnvelope]); // ← tambah ini

        $response = Http::withHeaders([
            'Content-Type'  => 'text/xml; charset=utf-8',
            'Authorization' => 'Bearer ' . $bearerToken,
        ])->send('POST', $this->endpoint, ['body' => $xmlEnvelope]);

        Log::debug('SOAP Response', [ 
            'status' => $response->status(),
            'headers'  => $response->headers(),
            'body'   => $response->body(),
        ]);

        if ($response->failed()) {
            Log::error('SOAP Audit failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return null;
        }

        return $this->parseReceiptNumber($response->body());
    }

    private function buildEnvelope(array $data): string
    {
        $logContent = json_encode([
            'contract_id' => $data['contract_id'],
            'tenant_id'   => $data['tenant_id'],
            'listing_id'  => $data['listing_id'],
            'start_date'  => $data['start_date'],
            'end_date'    => $data['end_date'],
            'status'      => $data['status'] ?? 'DRAFT',
            'audited_at'  => now()->toIso8601String(),
        ]);

        return <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <soap:Envelope
            xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
            xmlns:iae="http://iae.central/audit">
            <soap:Body>
                <iae:AuditRequest>
                    <iae:TeamID>{$this->teamId}</iae:TeamID>
                    <iae:ActivityName>ContractCreated</iae:ActivityName>
                    <iae:LogContent><![CDATA[{$logContent}]]></iae:LogContent>
                </iae:AuditRequest>
            </soap:Body>
        </soap:Envelope>
        XML;
    }

    private function parseReceiptNumber(string $xmlResponse): ?string
    {
        libxml_use_internal_errors(true);

        $cleaned = preg_replace('/(<\/?)(\w+):/', '$1', $xmlResponse);
        $xml = simplexml_load_string($cleaned);

        if ($xml === false) {
            Log::error('Failed to parse SOAP response', ['raw' => $xmlResponse]);
            return null;
        }

        $result = $xml->xpath('//ReceiptNumber');

        Log::debug('SOAP parse result', ['receipt' => $result ? (string) $result[0] : null]);

        return $result ? (string) $result[0] : null;
    }
}   