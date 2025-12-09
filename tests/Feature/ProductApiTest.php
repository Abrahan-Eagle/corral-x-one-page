<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Ranch;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products(): void
    {
        Product::factory()->count(3)->create(['status' => 'active']);
        Product::factory()->count(2)->create(['status' => 'paused']);

        $res = $this->getJson('/api/products');
        $res->assertOk();
        $this->assertGreaterThanOrEqual(3, $res->json('total'));
    }

    public function test_can_filter_products_by_breed_and_sex(): void
    {
        Product::factory()->create(['breed' => 'Brahman', 'sex' => 'male', 'status' => 'active']);
        Product::factory()->create(['breed' => 'Girolando', 'sex' => 'female', 'status' => 'active']);

        $res = $this->getJson('/api/products?breed=Brahman&sex=male');
        $res->assertOk();
        $items = $res->json('data');
        $this->assertNotEmpty($items);
        foreach ($items as $item) {
            $this->assertSame('Brahman', $item['breed']);
            $this->assertSame('male', $item['sex']);
        }
    }

    public function test_can_show_product_and_increments_views(): void
    {
        $product = Product::factory()->create(['status' => 'active', 'views' => 0]);

        $show1 = $this->getJson('/api/products/'.$product->id)->assertOk();
        $this->assertEquals(1, Product::find($product->id)->views);

        $show2 = $this->getJson('/api/products/'.$product->id)->assertOk();
        $this->assertEquals(2, Product::find($product->id)->views);
    }

    public function test_requires_auth_to_create_product(): void
    {
        $payload = [];
        $this->postJson('/api/products', $payload)->assertStatus(401);
    }

    public function test_authenticated_user_can_create_product_with_minimum_fields(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'firstName' => 'Juan',
            'lastName' => 'Pérez',
            'date_of_birth' => '1990-01-01',
            'ci_number' => 'V-12345678',
            'sex' => 'M',
            'user_type' => 'seller',
            'photo_users' => 'https://example.com/photo.jpg',
            // KYC verificado para poder publicar
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
            'profile_id' => $profile->id,
            'city_id' => $city->id,
            'adressses' => 'Calle Principal',
            'latitude' => 10.0,
            'longitude' => -66.0,
            'status' => 'completeData',
            'level' => 'ranches',
        ]);
        
        $ranch = Ranch::factory()->create([
            'profile_id' => $profile->id,
            'address_id' => $address->id,
            'name' => 'Hacienda Test',
            'is_primary' => true,
        ]);

        Sanctum::actingAs($user);

        $payload = [
            'ranch_id' => $ranch->id,
            'title' => 'Lote de novillos',
            'description' => 'Novillos de buena genética',
            'breed' => 'Brahman',
            'quantity' => 10,
            'price' => 500.00,
            'currency' => 'USD',
            'purpose' => 'meat',
            'feeding_type' => 'pastura_natural',
            'delivery_method' => 'pickup',
        ];

        $res = $this->postJson('/api/products', $payload);
        $res->assertCreated();
        $res->assertJsonFragment(['title' => 'Lote de novillos']);
        $this->assertDatabaseHas('products', ['title' => 'Lote de novillos', 'ranch_id' => $ranch->id]);
    }

    public function test_cannot_create_product_if_kyc_not_verified(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'firstName' => 'Juan',
            'lastName' => 'Pérez',
            'date_of_birth' => '1990-01-01',
            'ci_number' => 'V-12345678',
            'sex' => 'M',
            'user_type' => 'seller',
            'photo_users' => 'https://example.com/photo.jpg',
            // KYC NO verificado
            'kyc_status' => 'no_verified',
        ]);

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
            'profile_id' => $profile->id,
            'city_id' => $city->id,
            'adressses' => 'Calle Principal',
            'latitude' => 10.0,
            'longitude' => -66.0,
            'status' => 'completeData',
            'level' => 'ranches',
        ]);

        $ranch = Ranch::factory()->create([
            'profile_id' => $profile->id,
            'address_id' => $address->id,
            'name' => 'Hacienda Test',
            'is_primary' => true,
        ]);

        Sanctum::actingAs($user);

        $payload = [
            'ranch_id' => $ranch->id,
            'title' => 'Lote bloqueado por KYC',
            'description' => 'No debería crearse porque el KYC no está verificado',
            'breed' => 'Brahman',
            'quantity' => 5,
            'price' => 300.00,
            'currency' => 'USD',
            'purpose' => 'meat',
            'feeding_type' => 'pastura_natural',
            'delivery_method' => 'pickup',
        ];

        $res = $this->postJson('/api/products', $payload);
        $res->assertStatus(422)
            ->assertJsonFragment([
                'error' => 'kyc_incomplete',
            ]);

        $this->assertDatabaseMissing('products', ['title' => 'Lote bloqueado por KYC']);
    }

    public function test_authenticated_user_can_update_product(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'firstName' => 'Juan',
            'lastName' => 'Pérez',
            'date_of_birth' => '1990-01-01',
            'ci_number' => 'V-12345678',
            'sex' => 'M',
            'user_type' => 'seller',
            'photo_users' => 'https://example.com/photo.jpg',
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
            'profile_id' => $profile->id,
            'city_id' => $city->id,
            'adressses' => 'Calle Principal',
            'latitude' => 10.0,
            'longitude' => -66.0,
            'status' => 'completeData',
            'level' => 'ranches',
        ]);
        
        $ranch = Ranch::factory()->create([
            'profile_id' => $profile->id,
            'address_id' => $address->id,
            'name' => 'Hacienda Test',
            'is_primary' => true,
        ]);
        $product = Product::factory()->create(['ranch_id' => $ranch->id, 'title' => 'Titulo original']);

        Sanctum::actingAs($user);

        $res = $this->putJson('/api/products/'.$product->id, [
            'title' => 'Titulo cambiado',
            'price' => 600,
        ]);

        $res->assertOk();
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'title' => 'Titulo cambiado',
            'price' => 600,
        ]);
    }

    public function test_authenticated_user_can_delete_product(): void
    {
        $user = User::factory()->create();
        Profile::factory()->for($user)->create([
            'kyc_status' => 'verified',
        ]);
        $profile = $user->fresh()->profile;
        $ranch = Ranch::factory()->create(['profile_id' => $profile->id]);
        $product = Product::factory()->create(['ranch_id' => $ranch->id]);

        Sanctum::actingAs($user);

        $this->deleteJson('/api/products/'.$product->id)->assertOk();
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}


