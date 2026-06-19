<?php

namespace Tests\Feature;

use Tests\TestCase;

class DocumentationTest extends TestCase
{
    public function test_l5_swagger_ui_is_available(): void
    {
        $response = $this->get('/api/documentation');

        $response->assertOk()
            ->assertSee('swagger');
    }

    public function test_static_openapi_json_is_available(): void
    {
        $response = $this->getJson('/api/openapi.json');

        $response->assertOk()
            ->assertJsonPath('info.title', 'Listing Unit Service API');
    }
}
