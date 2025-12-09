<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Profile;
use App\Models\Ranch;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductFeedingTypeTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Profile $profile;
    protected Ranch $ranch;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear usuario y perfil completo
        $this->user = User::factory()->create();
        $this->profile = Profile::factory()->create([
            'user_id' => $this->user->id,
            'firstName' => 'Juan',
            'lastName' => 'Pérez',
            'date_of_birth' => '1990-01-01',
            'ci_number' => 'V-12345678',
            'sex' => 'M',
            'user_type' => 'seller',
            'photo_users' => 'https://example.com/photo.jpg',
            // KYC verificado para que las validaciones de formulario se ejecuten
            'kyc_status' => 'verified',
        ]);
        
        // Crear datos geográficos y dirección completa
        $country = \App\Models\Country::factory()->create();
        $state = \App\Models\State::create([
            'countries_id' => $country->id,
            'name' => 'Test State',
        ]);
        $city = \App\Models\City::create([
            'state_id' => $state->id,
            'name' => 'Test City',
        ]);
        $address = \App\Models\Address::create([
            'profile_id' => $this->profile->id,
            'city_id' => $city->id,
            'adressses' => 'Calle Principal',
            'latitude' => 10.0,
            'longitude' => -66.0,
            'status' => 'completeData',
            'level' => 'ranches',
        ]);
        
        $this->ranch = Ranch::factory()->create([
            'profile_id' => $this->profile->id,
            'address_id' => $address->id,
            'name' => 'Hacienda Test',
            'is_primary' => true,
        ]);
    }

    /**
     * Test: feeding_type es obligatorio al crear un producto
     */
    public function test_feeding_type_is_required_when_creating_product(): void
    {
        Sanctum::actingAs($this->user);

        $payload = [
            'ranch_id' => $this->ranch->id,
            'title' => 'Lote de novillos',
            'description' => 'Novillos de buena genética',
            'breed' => 'Brahman',
            'quantity' => 10,
            'price' => 500.00,
            'currency' => 'USD',
            'purpose' => 'meat',
            'delivery_method' => 'pickup',
            // feeding_type NO está presente
        ];

        $response = $this->postJson('/api/products', $payload);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['feeding_type']);
    }

    /**
     * Test: feeding_type acepta valores válidos
     */
    public function test_feeding_type_accepts_valid_values(): void
    {
        Sanctum::actingAs($this->user);

        $validValues = ['pastura_natural', 'pasto_corte', 'concentrado', 'mixto', 'otro'];

        foreach ($validValues as $feedingType) {
            $payload = [
                'ranch_id' => $this->ranch->id,
                'title' => "Producto con {$feedingType}",
                'description' => 'Descripción del producto',
                'breed' => 'Brahman',
                'quantity' => 10,
                'price' => 500.00,
                'currency' => 'USD',
                'purpose' => 'meat',
                'feeding_type' => $feedingType,
                'delivery_method' => 'pickup',
            ];

            $response = $this->postJson('/api/products', $payload);

            $response->assertStatus(201)
                    ->assertJson(['feeding_type' => $feedingType]);

            $this->assertDatabaseHas('products', [
                'title' => "Producto con {$feedingType}",
                'feeding_type' => $feedingType,
            ]);
        }
    }

    /**
     * Test: feeding_type rechaza valores inválidos
     */
    public function test_feeding_type_rejects_invalid_values(): void
    {
        Sanctum::actingAs($this->user);

        $invalidValues = ['invalid_value', 'pastura', 'concentrado_extra', ''];

        foreach ($invalidValues as $invalidValue) {
            $payload = [
                'ranch_id' => $this->ranch->id,
                'title' => 'Producto de prueba',
                'description' => 'Descripción del producto',
                'breed' => 'Brahman',
                'quantity' => 10,
                'price' => 500.00,
                'currency' => 'USD',
                'purpose' => 'meat',
                'feeding_type' => $invalidValue,
                'delivery_method' => 'pickup',
            ];

            $response = $this->postJson('/api/products', $payload);

            $response->assertStatus(422)
                    ->assertJsonValidationErrors(['feeding_type']);
        }
    }

    /**
     * Test: feeding_type se puede actualizar correctamente
     */
    public function test_feeding_type_can_be_updated(): void
    {
        Sanctum::actingAs($this->user);

        // Crear producto con feeding_type inicial
        $product = Product::factory()->create([
            'ranch_id' => $this->ranch->id,
            'feeding_type' => 'pastura_natural',
        ]);

        // Actualizar a otro valor válido
        $response = $this->putJson("/api/products/{$product->id}", [
            'feeding_type' => 'concentrado',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'feeding_type' => 'concentrado',
        ]);
    }

    /**
     * Test: purpose es obligatorio al crear un producto (sin type)
     */
    public function test_purpose_is_required_when_creating_product(): void
    {
        Sanctum::actingAs($this->user);

        $payload = [
            'ranch_id' => $this->ranch->id,
            'title' => 'Lote de novillos',
            'description' => 'Novillos de buena genética',
            'breed' => 'Brahman',
            'quantity' => 10,
            'price' => 500.00,
            'currency' => 'USD',
            'feeding_type' => 'pastura_natural',
            'delivery_method' => 'pickup',
            // purpose NO está presente
        ];

        $response = $this->postJson('/api/products', $payload);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['purpose']);
    }

    /**
     * Test: ProductFactory genera feeding_type correctamente
     */
    public function test_product_factory_generates_feeding_type(): void
    {
        $product = Product::factory()->make();

        $this->assertNotNull($product->feeding_type);
        $this->assertContains(
            $product->feeding_type,
            ['pastura_natural', 'pasto_corte', 'concentrado', 'mixto', 'otro']
        );
    }

    /**
     * Test: Endpoint de exchange-rate funciona (test básico)
     * Nota: Los tests completos están en ExchangeRateTest.php
     */
    public function test_exchange_rate_endpoint_returns_valid_structure(): void
    {
        // Mock de respuesta exitosa de dolarapi.com
        \Illuminate\Support\Facades\Http::fake([
            've.dolarapi.com/v1/dolares/oficial' => \Illuminate\Support\Facades\Http::response([
                'fuente' => 'oficial',
                'nombre' => 'Oficial',
                'promedio' => 247.3,
                'fechaActualizacion' => now()->toIso8601String(),
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
        $this->assertEquals('USD', $data['currency_from']);
        $this->assertEquals('VES', $data['currency_to']);
        // El source puede variar según qué método funcione
        $this->assertNotEmpty($data['source']);
    }
}
