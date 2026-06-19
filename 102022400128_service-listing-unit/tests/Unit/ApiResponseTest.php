<?php

namespace Tests\Unit;

use App\Support\ApiResponse;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    public function test_success_response_contains_standard_meta(): void
    {
        $response = ApiResponse::success(['id' => 1]);
        $payload = $response->getData(true);

        $this->assertSame('success', $payload['status']);
        $this->assertSame('Listing-Unit-Service', $payload['meta']['service_name']);
        $this->assertSame('v1', $payload['meta']['api_version']);
    }
}
