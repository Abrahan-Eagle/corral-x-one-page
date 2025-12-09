<?php

namespace Tests\Feature;

use App\Models\Advertisement;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Ranch;
use App\Models\User;
use App\Models\Profile;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Test completo del módulo de Anuncios/Publicidad
 * 
 * Este test cubre todos los aspectos del módulo de anuncios:
 * - Endpoints públicos (GET /advertisements/active, POST /advertisements/{id}/click)
 * - Endpoints protegidos admin (CRUD completo)
 * - Validaciones de entrada
 * - Autorización (solo admin)
 * - Validación de fechas (start_date, end_date)
 * - Validación de tipos (sponsored_product, external_ad)
 * - Tracking de clicks e impressions
 * - Desactivación automática por fecha
 */
class AdvertisementApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $adminUser;
    private User $regularUser;
    private Profile $adminProfile;
    private Profile $regularProfile;
    private Ranch $ranch;
    private Product $product;
    private array $validSponsoredProductData;
    private array $validExternalAdData;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear datos base para las pruebas
        $this->setupBaseData();
        $this->setupValidAdvertisementData();
    }

    /**
     * Configurar datos base necesarios para las pruebas
     */
    private function setupBaseData(): void
    {
        // Crear usuario admin
        $this->adminUser = User::factory()->admin()->create();
        $this->adminProfile = Profile::factory()->create(['user_id' => $this->adminUser->id]);
        
        // Crear usuario regular
        $this->regularUser = User::factory()->user()->create();
        $this->regularProfile = Profile::factory()->create(['user_id' => $this->regularUser->id]);
        
        // Crear datos geográficos
        $country = Country::factory()->create(['name' => 'Venezuela', 'sortname' => 'VE']);
        $state = State::factory()->create(['countries_id' => $country->id, 'name' => 'Carabobo']);
        $city = City::factory()->create(['state_id' => $state->id, 'name' => 'Valencia']);
        
        // Crear dirección
        $address = Address::factory()->create([
            'profile_id' => $this->regularProfile->id,
            'city_id' => $city->id,
            'adressses' => 'Av. Bolívar Norte',
            'latitude' => 10.1621,
            'longitude' => -68.0077,
            'status' => 'completeData'
        ]);
        
        // Crear ranch
        $this->ranch = Ranch::factory()->create([
            'profile_id' => $this->regularProfile->id,
            'address_id' => $address->id,
            'name' => 'Hacienda El Toro',
            'is_primary' => true
        ]);
        
        // Crear producto activo con imagen
        $this->product = Product::factory()->create([
            'ranch_id' => $this->ranch->id,
            'status' => 'active',
            'title' => 'Lote de Novillos Brahman',
            'description' => 'Lote de novillos Brahman de excelente calidad',
        ]);
        
        // Crear imagen principal para el producto (requerido para patrocinio)
        ProductImage::factory()->create([
            'product_id' => $this->product->id,
            'is_primary' => true,
            'file_type' => 'image',
            'file_url' => 'https://example.com/product-image.jpg',
        ]);
    }

    /**
     * Configurar datos válidos para anuncios
     */
    private function setupValidAdvertisementData(): void
    {
        // Para productos patrocinados: title, description, image_url son OPCIONALES
        // Se obtienen automáticamente del producto si no se proporcionan
        $this->validSponsoredProductData = [
            'type' => 'sponsored_product',
            // 'title' => NO REQUERIDO - se obtiene del producto
            // 'description' => NO REQUERIDO - se obtiene del producto
            // 'image_url' => NO REQUERIDO - se obtiene del producto
            'start_date' => now()->format('Y-m-d H:i:s'),
            'end_date' => now()->addDays(30)->format('Y-m-d H:i:s'),
            'is_active' => true,
            'priority' => 10,
            'product_id' => $this->product->id,
        ];

        $this->validExternalAdData = [
            'type' => 'external_ad',
            'title' => 'Publicidad Toyota',
            'description' => 'Promoción especial de Toyota',
            'image_url' => 'https://example.com/toyota.jpg',
            'target_url' => 'https://toyota.com.ve',
            'start_date' => now()->format('Y-m-d H:i:s'),
            'end_date' => now()->addDays(60)->format('Y-m-d H:i:s'),
            'is_active' => true,
            'priority' => 5,
            'advertiser_name' => 'Toyota de Venezuela',
        ];
    }

    // ============================================================
    // TESTS: Endpoints Públicos
    // ============================================================

    /**
     * Test: Obtener anuncios activos (público)
     */
    public function test_can_get_active_advertisements_public(): void
    {
        // Crear anuncios activos e inactivos
        $activeAd = Advertisement::factory()->create([
            'type' => 'sponsored_product',
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(30),
            'product_id' => $this->product->id,
            'created_by' => $this->adminUser->id,
        ]);

        $inactiveAd = Advertisement::factory()->create([
            'type' => 'external_ad',
            'is_active' => false,
            'advertiser_name' => 'Test Advertiser',
            'created_by' => $this->adminUser->id,
        ]);

        $expiredAd = Advertisement::factory()->create([
            'type' => 'external_ad',
            'is_active' => true,
            'start_date' => now()->subDays(60),
            'end_date' => now()->subDay(), // Expirado
            'advertiser_name' => 'Expired Advertiser',
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->getJson('/api/advertisements/active');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'type',
                        'title',
                        'image_url',
                        'is_active',
                        'start_date',
                        'end_date',
                    ]
                ],
                'count'
            ]);

        // Verificar que solo se devuelven anuncios activos
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($activeAd->id, $data[0]['id']);

        // Verificar que se incrementaron las impressions
        $activeAd->refresh();
        $initialImpressions = $activeAd->impressions;
        $this->assertGreaterThanOrEqual(1, $initialImpressions); // Puede ser 1 o más si el factory ya tenía un valor
    }

    /**
     * Test: Registrar click en anuncio (público)
     */
    public function test_can_register_click_on_advertisement_public(): void
    {
        $advertisement = Advertisement::factory()->create([
            'type' => 'sponsored_product',
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(30),
            'product_id' => $this->product->id,
            'created_by' => $this->adminUser->id,
            'clicks' => 0,
        ]);

        $response = $this->postJson("/api/advertisements/{$advertisement->id}/click");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'clicks'
            ])
            ->assertJson([
                'success' => true,
                'clicks' => 1,
            ]);

        $advertisement->refresh();
        $this->assertEquals(1, $advertisement->clicks);
    }

    /**
     * Test: No se puede registrar click en anuncio inactivo
     */
    public function test_cannot_register_click_on_inactive_advertisement(): void
    {
        $advertisement = Advertisement::factory()->create([
            'type' => 'external_ad',
            'is_active' => false,
            'advertiser_name' => 'Test Advertiser',
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->postJson("/api/advertisements/{$advertisement->id}/click");

        $response->assertStatus(404)
            ->assertJson([
                'error' => 'Anuncio no disponible',
            ]);
    }

    // ============================================================
    // TESTS: Endpoints Admin (CRUD)
    // ============================================================

    /**
     * Test: Admin puede crear anuncio tipo sponsored_product
     */
    public function test_admin_can_create_sponsored_product_advertisement(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/advertisements', $this->validSponsoredProductData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'type',
                    'title',
                    'product_id',
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'type' => 'sponsored_product',
                ],
            ]);

        // Verificar que el anuncio se creó correctamente
        // Con la nueva lógica, el título se obtiene del producto automáticamente
        $this->assertDatabaseHas('advertisements', [
            'type' => 'sponsored_product',
            'title' => $this->product->title, // El título debe ser el del producto
            'product_id' => $this->product->id,
            'created_by' => $this->adminUser->id,
        ]);
    }

    /**
     * Test: Crear producto patrocinado sin proporcionar title, description, image_url
     * (Nueva lógica Instagram-like)
     */
    public function test_sponsored_product_auto_fills_data_from_product(): void
    {
        $data = [
            'type' => 'sponsored_product',
            'product_id' => $this->product->id,
            'start_date' => now()->format('Y-m-d H:i:s'),
            'end_date' => now()->addDays(30)->format('Y-m-d H:i:s'),
            'priority' => 75,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/advertisements', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'type',
                    'title',
                    'description',
                    'image_url',
                    'product_id',
                    'product', // Debe incluir el producto
                ],
            ]);

        // Verificar que los datos se obtuvieron del producto
        $response->assertJson([
            'data' => [
                'title' => $this->product->title, // Debe usar el título del producto
                'description' => $this->product->description, // Debe usar la descripción del producto
                'product_id' => $this->product->id,
            ],
        ]);

        // Verificar en la base de datos
        $this->assertDatabaseHas('advertisements', [
            'title' => $this->product->title,
            'description' => $this->product->description,
            'product_id' => $this->product->id,
        ]);
    }

    /**
     * Test: Admin puede crear anuncio tipo external_ad
     */
    public function test_admin_can_create_external_ad_advertisement(): void
    {
        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/advertisements', $this->validExternalAdData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'type' => 'external_ad',
                ],
            ]);

        $this->assertDatabaseHas('advertisements', [
            'type' => 'external_ad',
            'title' => $this->validExternalAdData['title'],
            'advertiser_name' => 'Toyota de Venezuela',
            'created_by' => $this->adminUser->id,
        ]);
    }

    /**
     * Test: Usuario regular NO puede crear anuncio
     */
    public function test_regular_user_cannot_create_advertisement(): void
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->postJson('/api/advertisements', $this->validSponsoredProductData);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'No autorizado',
                'message' => 'Solo administradores pueden crear anuncios',
            ]);
    }

    /**
     * Test: No se puede crear anuncio sin autenticación
     */
    public function test_cannot_create_advertisement_without_authentication(): void
    {
        $response = $this->postJson('/api/advertisements', $this->validSponsoredProductData);

        $response->assertStatus(401);
    }

    /**
     * Test: Validación - sponsored_product requiere product_id válido
     */
    public function test_sponsored_product_requires_valid_product_id(): void
    {
        $data = $this->validSponsoredProductData;
        $data['product_id'] = 99999; // ID inexistente

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/advertisements', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }

    /**
     * Test: Validación - sponsored_product requiere producto activo
     */
    public function test_sponsored_product_requires_active_product(): void
    {
        // Crear producto inactivo
        $inactiveProduct = Product::factory()->create([
            'ranch_id' => $this->ranch->id,
            'status' => 'paused',
        ]);

        $data = $this->validSponsoredProductData;
        $data['product_id'] = $inactiveProduct->id;

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/advertisements', $data);

        $response->assertStatus(422)
            ->assertJson([
                'error' => 'Producto no activo',
                'message' => 'El producto debe estar activo para ser patrocinado',
            ]);
    }

    /**
     * Test: Validación - external_ad requiere advertiser_name
     */
    public function test_external_ad_requires_advertiser_name(): void
    {
        $data = $this->validExternalAdData;
        unset($data['advertiser_name']);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/advertisements', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['advertiser_name']);
    }

    /**
     * Test: Validación - end_date debe ser después de start_date
     */
    public function test_end_date_must_be_after_start_date(): void
    {
        $data = $this->validSponsoredProductData;
        $data['start_date'] = now()->addDays(30)->format('Y-m-d H:i:s');
        $data['end_date'] = now()->format('Y-m-d H:i:s'); // Antes de start_date

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->postJson('/api/advertisements', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    /**
     * Test: Admin puede listar todos los anuncios
     */
    public function test_admin_can_list_all_advertisements(): void
    {
        Advertisement::factory()->count(3)->create([
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson('/api/advertisements');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'type',
                        'title',
                    ]
                ]
            ]);
    }

    /**
     * Test: Usuario regular NO puede listar anuncios
     */
    public function test_regular_user_cannot_list_advertisements(): void
    {
        $response = $this->actingAs($this->regularUser, 'sanctum')
            ->getJson('/api/advertisements');

        $response->assertStatus(403);
    }

    /**
     * Test: Admin puede ver detalle de anuncio
     */
    public function test_admin_can_view_advertisement_details(): void
    {
        $advertisement = Advertisement::factory()->create([
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->getJson("/api/advertisements/{$advertisement->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $advertisement->id,
            ]);
    }

    /**
     * Test: Admin puede actualizar anuncio
     */
    public function test_admin_can_update_advertisement(): void
    {
        // Crear un anuncio de publicidad externa (más fácil de actualizar)
        $advertisement = Advertisement::factory()->create([
            'type' => 'external_ad',
            'created_by' => $this->adminUser->id,
        ]);

        $updateData = [
            'title' => 'Título Actualizado',
            'description' => 'Descripción actualizada',
            'image_url' => 'https://example.com/new-image.jpg', // Requerido para external_ad
            'advertiser_name' => $advertisement->advertiser_name ?? 'Publicidad Actualizada',
        ];

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->putJson("/api/advertisements/{$advertisement->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('advertisements', [
            'id' => $advertisement->id,
            'title' => 'Título Actualizado',
        ]);
    }

    /**
     * Test: Admin puede eliminar anuncio
     */
    public function test_admin_can_delete_advertisement(): void
    {
        $advertisement = Advertisement::factory()->create([
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->actingAs($this->adminUser, 'sanctum')
            ->deleteJson("/api/advertisements/{$advertisement->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('advertisements', [
            'id' => $advertisement->id,
        ]);
    }

    /**
     * Test: Validación automática de fechas - solo mostrar activos
     */
    public function test_only_active_advertisements_within_date_range_are_returned(): void
    {
        // Anuncio activo (dentro de rango)
        $activeAd = Advertisement::factory()->create([
            'type' => 'sponsored_product',
            'is_active' => true,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(30),
            'product_id' => $this->product->id,
            'created_by' => $this->adminUser->id,
        ]);

        // Anuncio con fecha futura (aún no activo)
        $futureAd = Advertisement::factory()->create([
            'type' => 'external_ad',
            'is_active' => true,
            'start_date' => now()->addDays(7),
            'end_date' => now()->addDays(37),
            'advertiser_name' => 'Future Advertiser',
            'created_by' => $this->adminUser->id,
        ]);

        // Anuncio expirado
        $expiredAd = Advertisement::factory()->create([
            'type' => 'external_ad',
            'is_active' => true,
            'start_date' => now()->subDays(60),
            'end_date' => now()->subDay(),
            'advertiser_name' => 'Expired Advertiser',
            'created_by' => $this->adminUser->id,
        ]);

        $response = $this->getJson('/api/advertisements/active');

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($activeAd->id, $data[0]['id']);
    }
}
