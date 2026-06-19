<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AmqpPublisherService
{
    public function __construct()
    {
    }

    public function publishViaHttp(string $routingKey, array $message, string $bearerToken): bool
    {
            $response = Http::withHeaders([
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $bearerToken,
                ])->post(config('services.rabbitmq.http_url'), [
                    'routing_key' => $routingKey,
                    'message'     => $message,
            ]);

            Log::info('[AMQP-HTTP] Publish result', [
                'status' => $response->status(),
                'body'   => $response->json(),
            ]);

            return $response->successful();
        }
    }