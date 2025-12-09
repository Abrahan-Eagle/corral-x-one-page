<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     * Prueba que la API del backend móvil responde correctamente.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Probar la ruta del API, no la ruta web
        $response = $this->get('/api/ping');

        $response->assertStatus(200);
        $response->assertJson(['message' => 'API funcionando']);
    }
}
