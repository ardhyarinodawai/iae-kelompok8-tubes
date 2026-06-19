<?php

namespace Tests\Feature;

use App\Models\Listing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_listing_collection(): void
    {
        Listing::query()->create($this->listingPayload());

        $response = $this->withHeaders($this->headers())->getJson('/api/v1/listings');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('meta.service_name', 'Listing-Unit-Service')
            ->assertJsonCount(1, 'data');
    }

    public function test_can_get_listing_detail(): void
    {
        $listing = Listing::query()->create($this->listingPayload());

        $response = $this->withHeaders($this->headers())->getJson("/api/v1/listings/{$listing->id}");

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.unit_code', 'APT-T-0101');
    }

    public function test_can_create_listing(): void
    {
        $response = $this->withHeaders($this->headers())->postJson('/api/v1/listings', $this->listingPayload([
            'unit_code' => 'APT-C-0910',
            'room_number' => '910',
        ]));

        $response->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.unit_code', 'APT-C-0910');
    }

    public function test_404_and_422_and_401_responses_are_available(): void
    {
        $this->withHeaders($this->headers())
            ->getJson('/api/v1/listings/999')
            ->assertNotFound()
            ->assertJsonPath('status', 'error');

        $this->withHeaders($this->headers())
            ->postJson('/api/v1/listings', ['status' => 'invalid'])
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error');

        $this->getJson('/api/v1/listings')
            ->assertUnauthorized()
            ->assertJsonPath('status', 'error');
    }

    private function headers(): array
    {
        return ['X-IAE-KEY' => '102022400128'];
    }

    private function listingPayload(array $overrides = []): array
    {
        return array_merge([
            'unit_code' => 'APT-T-0101',
            'unit_name' => 'Apartemen Tower T 101',
            'tower' => 'T',
            'floor' => 1,
            'room_number' => '101',
            'unit_type' => 'apartment',
            'status' => 'occupied',
            'tenant_name' => 'Rafsan Tenant',
            'tenant_phone' => '081111111111',
        ], $overrides);
    }
}
