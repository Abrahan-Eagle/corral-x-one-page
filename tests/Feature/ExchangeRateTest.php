<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ExchangeRateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Limpiar cache antes de cada test
        Cache::forget('bcv_usd_rate_data');
        Cache::forget('bcv_usd_rate_last');
    }

    /**
     * Test: El endpoint retorna una estructura válida con tasa de cambio
     */
    public function test_exchange_rate_endpoint_returns_valid_structure(): void
    {
        // Mock de respuesta exitosa de dolarapi.com
        Http::fake([
            've.dolarapi.com/v1/dolares/oficial' => Http::response([
                'fuente' => 'oficial',
                'nombre' => 'Oficial',
                'compra' => null,
                'venta' => null,
                'promedio' => 247.3,
                'fechaActualizacion' => '2025-12-01T21:02:38.969Z',
            ], 200),
        ]);

        $response = $this->getJson('/api/exchange-rate');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'rate',
                    'currency_from',
                    'currency_to',
                    'source',
                    'cached',
                    'last_updated',
                ]);

        $data = $response->json();
        $this->assertIsNumeric($data['rate']);
        $this->assertGreaterThan(0, $data['rate']);
        $this->assertLessThan(1000000, $data['rate']); // Validación razonable
        $this->assertEquals('USD', $data['currency_from']);
        $this->assertEquals('VES', $data['currency_to']);
        $this->assertStringContainsString('dolarapi.com', $data['source']);
    }

    /**
     * Test: El endpoint obtiene la tasa desde dolarapi.com (método principal)
     */
    public function test_exchange_rate_obtains_from_dolarapi_com(): void
    {
        $mockRate = 247.3;
        
        Http::fake([
            've.dolarapi.com/v1/dolares/oficial' => Http::response([
                'fuente' => 'oficial',
                'nombre' => 'Oficial',
                'promedio' => $mockRate,
                'fechaActualizacion' => now()->toIso8601String(),
            ], 200),
        ]);

        $response = $this->getJson('/api/exchange-rate');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals($mockRate, $data['rate']);
        $this->assertStringContainsString('dolarapi.com', $data['source']);
    }

    /**
     * Test: Si dolarapi.com falla, intenta con bcvapi.tech (método 2)
     */
    public function test_exchange_rate_falls_back_to_bcvapi_tech(): void
    {
        $mockRate = 248.5;
        
        Http::fake([
            've.dolarapi.com/v1/dolares/oficial' => Http::response([], 500), // Error
            'bcvapi.tech/api/v1/exchange-rate' => Http::response([
                'usd' => $mockRate,
            ], 200),
        ]);

        $response = $this->getJson('/api/exchange-rate');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals($mockRate, $data['rate']);
        $this->assertEquals('bcvapi.tech', $data['source']);
    }

    /**
     * Test: Si los métodos 1 y 2 fallan, intenta con pydolarve.org (método 3)
     */
    public function test_exchange_rate_falls_back_to_pydolarve_org(): void
    {
        $mockRate = 249.0;
        
        Http::fake([
            've.dolarapi.com/v1/dolares/oficial' => Http::response([], 500),
            'bcvapi.tech/api/v1/exchange-rate' => Http::response([], 500),
            'api.pydolarve.org/api/v1/dollar/rate/bcv' => Http::response([
                'precio' => $mockRate,
            ], 200),
        ]);

        $response = $this->getJson('/api/exchange-rate');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals($mockRate, $data['rate']);
        $this->assertEquals('pydolarve.org', $data['source']);
    }

    /**
     * Test: Si todos los métodos fallan pero hay cache, usa el último valor cacheado
     */
    public function test_exchange_rate_uses_cached_value_when_all_methods_fail(): void
    {
        $cachedRate = 250.0;
        
        // Guardar un valor en cache
        Cache::put('bcv_usd_rate_last', $cachedRate, 86400);
        
        Http::fake([
            've.dolarapi.com/v1/dolares/oficial' => Http::response([], 500),
            'bcvapi.tech/api/v1/exchange-rate' => Http::response([], 500),
            'api.pydolarve.org/api/v1/dollar/rate/bcv' => Http::response([], 500),
            'www.bcv.org.ve/*' => Http::response([], 500),
        ]);

        $response = $this->getJson('/api/exchange-rate');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals($cachedRate, $data['rate']);
        $this->assertStringContainsString('cached', $data['source']);
    }

    /**
     * Test: Si todos los métodos fallan y NO hay cache, retorna error 503
     */
    public function test_exchange_rate_returns_503_when_no_cache_and_all_methods_fail(): void
    {
        // Asegurar que no hay cache
        Cache::forget('bcv_usd_rate_last');
        Cache::forget('bcv_usd_rate_data');
        
        Http::fake([
            've.dolarapi.com/v1/dolares/oficial' => Http::response([], 500),
            'bcvapi.tech/api/v1/exchange-rate' => Http::response([], 500),
            'api.pydolarve.org/api/v1/dollar/rate/bcv' => Http::response([], 500),
            'www.bcv.org.ve/*' => Http::response([], 500),
        ]);

        $response = $this->getJson('/api/exchange-rate');

        $response->assertStatus(503)
                ->assertJsonStructure([
                    'error',
                    'message',
                ]);
        
        $data = $response->json();
        $this->assertStringContainsString('No se pudo obtener', $data['error']);
    }

    /**
     * Test: El sistema guarda el valor exitoso en cache para uso futuro
     */
    public function test_exchange_rate_saves_successful_rate_to_cache(): void
    {
        $mockRate = 247.3;
        
        Http::fake([
            've.dolarapi.com/v1/dolares/oficial' => Http::response([
                'promedio' => $mockRate,
            ], 200),
        ]);

        // Asegurar que no hay cache previo
        Cache::forget('bcv_usd_rate_last');

        $response = $this->getJson('/api/exchange-rate');
        $response->assertStatus(200);

        // Verificar que se guardó en cache
        $cachedRate = Cache::get('bcv_usd_rate_last');
        $this->assertEquals($mockRate, $cachedRate);
    }

    /**
     * Test: El endpoint maneja promedio null y usa método de respaldo
     */
    public function test_exchange_rate_handles_null_promedio_and_falls_back(): void
    {
        // Formato con promedio null (debe fallar y usar siguiente método)
        Http::fake([
            've.dolarapi.com/v1/dolares/oficial' => Http::response([
                'promedio' => null,
            ], 200),
            'bcvapi.tech/api/v1/exchange-rate' => Http::response([
                'usd' => 248.0,
            ], 200),
        ]);

        Cache::flush();
        
        $response = $this->getJson('/api/exchange-rate');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(248.0, $data['rate']); // Del método de respaldo
        $this->assertEquals('bcvapi.tech', $data['source']);
    }

    /**
     * Test: El cache funciona correctamente (no hace múltiples llamadas)
     */
    public function test_exchange_rate_cache_prevents_multiple_calls(): void
    {
        $mockRate = 247.3;
        
        Http::fake([
            've.dolarapi.com/v1/dolares/oficial' => Http::response([
                'promedio' => $mockRate,
            ], 200),
        ]);

        // Primera llamada
        $response1 = $this->getJson('/api/exchange-rate');
        $response1->assertStatus(200);
        
        // Segunda llamada (debe usar cache)
        $response2 = $this->getJson('/api/exchange-rate');
        $response2->assertStatus(200);
        $data2 = $response2->json();
        $this->assertTrue($data2['cached']); // Debe estar cacheado
        
        // Verificar que solo se hizo una llamada HTTP real
        Http::assertSentCount(1);
    }

    /**
     * Test: El endpoint valida que la tasa sea razonable
     */
    public function test_exchange_rate_validates_reasonable_values(): void
    {
        // Tasa inválida (negativa) - el código actual rechaza valores <= 0
        // y continúa con el siguiente método
        Http::fake([
            've.dolarapi.com/v1/dolares/oficial' => Http::response([
                'promedio' => -100, // Tasa inválida (negativa)
            ], 200),
            'bcvapi.tech/api/v1/exchange-rate' => Http::response([
                'usd' => 248.0,
            ], 200),
        ]);

        Cache::flush(); // Limpiar todo el cache
        
        $response = $this->getJson('/api/exchange-rate');
        
        // El código valida que rate > 0, por lo que si es negativo,
        // debe intentar el siguiente método (bcvapi.tech)
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(248.0, $data['rate']); // Del método de respaldo
        $this->assertEquals('bcvapi.tech', $data['source']);
    }
}

