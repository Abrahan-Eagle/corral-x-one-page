<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use App\Models\Ranch;
use App\Models\Address;
use App\Models\City;
use App\Models\State;
use App\Models\Country;

class ProfileCompletenessTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $profile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->profile = Profile::factory()->create(['user_id' => $this->user->id]);
    }

    /** @test */
    public function completeness_endpoint_returns_401_for_unauthenticated_user(): void
    {
        $response = $this->getJson('/api/me/completeness');
        $response->assertStatus(401);
    }

    /** @test */
    public function completeness_endpoint_returns_404_when_profile_not_found(): void
    {
        // Crear usuario sin perfil
        $userWithoutProfile = User::factory()->create();
        
        $response = $this->actingAs($userWithoutProfile)->getJson('/api/me/completeness');
        $response->assertStatus(404)
                ->assertJson([
                    'profile_complete' => false,
                    'ranch_complete' => false,
                    'can_publish' => false,
                ]);
    }

    /** @test */
    public function completeness_endpoint_returns_false_when_profile_incomplete(): void
    {
        // Perfil sin campos obligatorios (usar strings vacíos, pero mantener valores válidos para campos que no permiten null)
        $this->profile->update([
            'firstName' => '',
            'middleName' => '',
            'lastName' => '',
            'secondLastName' => '',
            'date_of_birth' => null,
            'ci_number' => '',
            'sex' => null, // Puede ser null
            'user_type' => 'buyer', // Mantener valor válido pero no completo
            'photo_users' => '',
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/me/completeness');
        
        $response->assertStatus(200)
                ->assertJson([
                    'profile_complete' => false,
                    'can_publish' => false,
                ])
                ->assertJsonStructure([
                    'profile_complete',
                    'ranch_complete',
                    'can_publish',
                    'missing_profile_fields',
                    'missing_ranch_fields',
                    'message',
                ]);

        $data = $response->json();
        $this->assertNotEmpty($data['missing_profile_fields']);
    }

    /** @test */
    public function completeness_endpoint_returns_false_when_ranch_incomplete(): void
    {
        // Perfil completo
        $this->profile->update([
            'firstName' => 'Juan',
            'middleName' => 'Carlos',
            'lastName' => 'Pérez',
            'secondLastName' => 'González',
            'date_of_birth' => '1990-01-01',
            'ci_number' => '12345678',
            'sex' => 'M',
            'user_type' => 'seller',
            'photo_users' => 'https://example.com/photo.jpg',
        ]);

        // Sin hacienda principal
        $response = $this->actingAs($this->user)->getJson('/api/me/completeness');
        
        $response->assertStatus(200)
                ->assertJson([
                    'profile_complete' => true,
                    'ranch_complete' => false,
                    'can_publish' => false,
                ]);

        $data = $response->json();
        $this->assertContains('ranch', $data['missing_ranch_fields']);
    }

    /** @test */
    public function completeness_endpoint_returns_false_when_ranch_missing_name(): void
    {
        // Perfil completo
        $this->profile->update([
            'firstName' => 'Juan',
            'middleName' => 'Carlos',
            'lastName' => 'Pérez',
            'secondLastName' => 'González',
            'date_of_birth' => '1990-01-01',
            'ci_number' => '12345678',
            'sex' => 'M',
            'user_type' => 'seller',
            'photo_users' => 'https://example.com/photo.jpg',
        ]);

        // Crear país, estado y ciudad para la dirección
        $country = Country::factory()->create();
        $state = State::factory()->create(['countries_id' => $country->id]);
        $city = City::factory()->create(['state_id' => $state->id]);

        // Crear dirección (usar create directamente)
        $address = Address::create([
            'city_id' => $city->id,
            'adressses' => 'Calle Principal',
            'profile_id' => $this->profile->id,
            'latitude' => 10.0,
            'longitude' => -66.0,
            'status' => 'completeData',
            'level' => 'ranches',
        ]);

        // Hacienda sin nombre (usar string vacío en lugar de null)
        $ranch = Ranch::create([
            'profile_id' => $this->profile->id,
            'name' => '',
            'address_id' => $address->id,
            'is_primary' => true,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/me/completeness');
        
        $response->assertStatus(200)
                ->assertJson([
                    'profile_complete' => true,
                    'ranch_complete' => false,
                    'can_publish' => false,
                ]);

        $data = $response->json();
        $this->assertContains('name', $data['missing_ranch_fields']);
    }

    /** @test */
    public function completeness_endpoint_returns_false_when_ranch_missing_address(): void
    {
        // Perfil completo
        $this->profile->update([
            'firstName' => 'Juan',
            'middleName' => 'Carlos',
            'lastName' => 'Pérez',
            'secondLastName' => 'González',
            'date_of_birth' => '1990-01-01',
            'ci_number' => '12345678',
            'sex' => 'M',
            'user_type' => 'seller',
            'photo_users' => 'https://example.com/photo.jpg',
        ]);

        // Hacienda sin dirección
        $ranch = Ranch::factory()->create([
            'profile_id' => $this->profile->id,
            'name' => 'Hacienda Test',
            'address_id' => null,
            'is_primary' => true,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/me/completeness');
        
        $response->assertStatus(200)
                ->assertJson([
                    'profile_complete' => true,
                    'ranch_complete' => false,
                    'can_publish' => false,
                ]);

        $data = $response->json();
        $this->assertContains('address', $data['missing_ranch_fields']);
    }

    /** @test */
    public function completeness_endpoint_returns_false_when_address_missing_city_or_street(): void
    {
        // Perfil completo
        $this->profile->update([
            'firstName' => 'Juan',
            'middleName' => 'Carlos',
            'lastName' => 'Pérez',
            'secondLastName' => 'González',
            'date_of_birth' => '1990-01-01',
            'ci_number' => '12345678',
            'sex' => 'M',
            'user_type' => 'seller',
            'photo_users' => 'https://example.com/photo.jpg',
        ]);

        // Crear país, estado y ciudad (usar create directamente para evitar problemas con factory)
        $country = Country::factory()->create();
        $state = State::create([
            'countries_id' => $country->id,
            'name' => 'Test State',
        ]);
        $city = City::create([
            'state_id' => $state->id,
            'name' => 'Test City',
        ]);

        // Dirección sin ciudad - crear ciudad primero pero luego no usarla en la validación
        // Como city_id no puede ser null, creamos una dirección válida pero luego verificamos que el test funcione
        // En realidad, el test debería verificar que si la dirección no tiene city_id válido, falle
        // Pero como city_id es required, mejor crear una dirección válida y luego verificar otro campo
        $country = Country::factory()->create();
        $state = State::create([
            'countries_id' => $country->id,
            'name' => 'Test State',
        ]);
        $city = City::create([
            'state_id' => $state->id,
            'name' => 'Test City',
        ]);
        
        // Dirección sin adressses (campo que sí puede estar vacío)
        $address = Address::create([
            'city_id' => $city->id, // Requerido, no puede ser null
            'adressses' => '', // Este sí puede estar vacío
            'profile_id' => $this->profile->id,
            'latitude' => 10.0,
            'longitude' => -66.0,
            'status' => 'completeData',
            'level' => 'ranches',
        ]);

        $ranch = Ranch::factory()->create([
            'profile_id' => $this->profile->id,
            'name' => 'Hacienda Test',
            'address_id' => $address->id,
            'is_primary' => true,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/me/completeness');
        
        $response->assertStatus(200)
                ->assertJson([
                    'profile_complete' => true,
                    'ranch_complete' => false,
                    'can_publish' => false,
                ]);

        $data = $response->json();
        // Verificar que falta adressses (dirección vacía)
        $this->assertContains('address.adressses', $data['missing_ranch_fields']);
    }

    /** @test */
    public function completeness_endpoint_returns_true_when_all_complete(): void
    {
        // Perfil completo
        $this->profile->update([
            'firstName' => 'Juan',
            'middleName' => 'Carlos',
            'lastName' => 'Pérez',
            'secondLastName' => 'González',
            'date_of_birth' => '1990-01-01',
            'ci_number' => '12345678',
            'sex' => 'M',
            'user_type' => 'seller',
            'photo_users' => 'https://example.com/photo.jpg',
        ]);

        // Crear país, estado y ciudad (usar create directamente para evitar problemas con factory)
        $country = Country::factory()->create();
        $state = State::create([
            'countries_id' => $country->id,
            'name' => 'Test State',
        ]);
        $city = City::create([
            'state_id' => $state->id,
            'name' => 'Test City',
        ]);

        // Dirección completa (usar create directamente para evitar problemas con factory)
        $address = Address::create([
            'city_id' => $city->id,
            'adressses' => 'Calle Principal',
            'profile_id' => $this->profile->id,
            'latitude' => 10.0,
            'longitude' => -66.0,
            'status' => 'completeData',
            'level' => 'ranches',
        ]);

        // Hacienda completa
        $ranch = Ranch::factory()->create([
            'profile_id' => $this->profile->id,
            'name' => 'Hacienda Test',
            'address_id' => $address->id,
            'is_primary' => true,
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/me/completeness');
        
        $response->assertStatus(200)
                ->assertJson([
                    'profile_complete' => true,
                    'ranch_complete' => true,
                    'can_publish' => true,
                ]);

        $data = $response->json();
        $this->assertEmpty($data['missing_profile_fields']);
        $this->assertEmpty($data['missing_ranch_fields']);
    }

    /** @test */
    public function product_creation_rejects_when_profile_incomplete(): void
    {
        // Perfil incompleto (usar string vacío en lugar de null)
        $this->profile->update([
            'firstName' => '',
            'lastName' => 'Pérez',
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/products', [
            'ranch_id' => 1,
            'title' => 'Test Product',
            'description' => 'Test Description',
            'breed' => 'Brahman',
            'quantity' => 5,
            'price' => 1000,
            'currency' => 'USD',
            'purpose' => 'meat',
            'feeding_type' => 'pastura_natural',
            'delivery_method' => 'pickup',
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'error' => 'profile_incomplete',
                ])
                ->assertJsonStructure([
                    'message',
                    'error',
                    'missing_fields',
                ]);
    }

    /** @test */
    public function product_creation_rejects_when_ranch_incomplete(): void
    {
        // Perfil completo
        $this->profile->update([
            'firstName' => 'Juan',
            'middleName' => 'Carlos',
            'lastName' => 'Pérez',
            'secondLastName' => 'González',
            'date_of_birth' => '1990-01-01',
            'ci_number' => '12345678',
            'sex' => 'M',
            'user_type' => 'seller',
            'photo_users' => 'https://example.com/photo.jpg',
        ]);

        // Sin hacienda principal
        $response = $this->actingAs($this->user)->postJson('/api/products', [
            'ranch_id' => 1,
            'title' => 'Test Product',
            'description' => 'Test Description',
            'breed' => 'Brahman',
            'quantity' => 5,
            'price' => 1000,
            'currency' => 'USD',
            'purpose' => 'meat',
            'feeding_type' => 'pastura_natural',
            'delivery_method' => 'pickup',
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    'error' => 'ranch_incomplete',
                ])
                ->assertJsonStructure([
                    'message',
                    'error',
                    'missing_fields',
                ]);
    }
}

