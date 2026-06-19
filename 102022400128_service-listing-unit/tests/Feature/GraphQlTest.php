<?php

namespace Tests\Feature;

use App\Models\Listing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GraphQlTest extends TestCase
{
    use RefreshDatabase;

    public function test_graphql_unit_query_returns_selected_fields(): void
    {
        $listing = Listing::query()->create([
            'unit_code' => 'APT-G-0202',
            'unit_name' => 'Apartemen Tower G 202',
            'tower' => 'G',
            'floor' => 2,
            'room_number' => '202',
            'unit_type' => 'apartment',
            'status' => 'occupied',
            'tenant_name' => 'Graph Tenant',
            'tenant_phone' => '082222222222',
        ]);

        $response = $this->withHeaders($this->headers())->postJson('/graphql', [
            'query' => "query { unit(id: {$listing->id}) { id unit_code unit_name } }",
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.unit.unit_code', 'APT-G-0202');

        $this->assertArrayNotHasKey('tenant_phone', $response->json('data.unit'));
    }

    public function test_graphql_requires_api_key(): void
    {
        $this->postJson('/graphql', [
            'query' => 'query { unit(id: 1) { id unit_code } }',
        ])->assertUnauthorized()
            ->assertJsonPath('status', 'error');
    }

    public function test_graphql_playground_page_is_available(): void
    {
        $this->get('/graphql-playground')
            ->assertOk()
            ->assertSee('GraphQL Playground');
    }

    private function headers(): array
    {
        return ['X-IAE-KEY' => '102022400128'];
    }
}
