<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use App\Models\Ranch;
use App\Models\Product;
use App\Models\Address;
use App\Models\Phone;
use App\Models\Category;
use App\Models\OperatorCode;
use App\Models\Country;
use App\Models\State;
use App\Models\City;

class ModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test relaciones del modelo User
     */
    public function test_user_relationships(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Profile::class, $user->profile);
        $this->assertEquals($user->id, $user->profile->user_id);
    }

    /**
     * Test relaciones del modelo Profile
     */
    public function test_profile_relationships(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $ranch = Ranch::factory()->create(['profile_id' => $profile->id]);
        $address = Address::factory()->create(['profile_id' => $profile->id]);
        $phone = Phone::factory()->create(['profile_id' => $profile->id]);

        // Test relación con User
        $this->assertInstanceOf(User::class, $profile->user);
        $this->assertEquals($user->id, $profile->user->id);

        // Test relación con Ranches
        $this->assertTrue($profile->ranches->contains($ranch));
        $this->assertEquals(1, $profile->ranches->count());

        // Test relación con Addresses
        $this->assertTrue($profile->addresses->contains($address));
        $this->assertEquals(1, $profile->addresses->count());

        // Test relación con Phones
        $this->assertTrue($profile->phones->contains($phone));
        $this->assertEquals(1, $profile->phones->count());
    }

    /**
     * Test relaciones del modelo Ranch
     */
    public function test_ranch_relationships(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $address = Address::factory()->create(['profile_id' => $profile->id]);
        $ranch = Ranch::factory()->create([
            'profile_id' => $profile->id,
            'address_id' => $address->id
        ]);
        $product = Product::factory()->create(['ranch_id' => $ranch->id]);
        $phone = Phone::factory()->create(['ranch_id' => $ranch->id]);

        // Test relación con Profile
        $this->assertInstanceOf(Profile::class, $ranch->profile);
        $this->assertEquals($profile->id, $ranch->profile->id);

        // Test relación con Products
        $this->assertTrue($ranch->products->contains($product));
        $this->assertEquals(1, $ranch->products->count());

        // Test relación con Address
        $this->assertInstanceOf(Address::class, $ranch->address);
        $this->assertEquals($address->id, $ranch->address->id);

        // Test relación con Phone
        $this->assertInstanceOf(Phone::class, $ranch->phone);
        $this->assertEquals($phone->id, $ranch->phone->id);
    }

    /**
     * Test relaciones del modelo Product
     */
    public function test_product_relationships(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $ranch = Ranch::factory()->create(['profile_id' => $profile->id]);
        $product = Product::factory()->create(['ranch_id' => $ranch->id]);
        $category = Category::factory()->create();

        // Test relación con Ranch
        $this->assertInstanceOf(Ranch::class, $product->ranch);
        $this->assertEquals($ranch->id, $product->ranch->id);

        // Test relación muchos a muchos con Categories
        $product->categories()->attach($category->id);
        $this->assertTrue($product->categories->contains($category));
        $this->assertEquals(1, $product->categories->count());
    }

    /**
     * Test relaciones del modelo Address
     */
    public function test_address_relationships(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $ranch = Ranch::factory()->create(['profile_id' => $profile->id]);
        $city = City::factory()->create();
        $address = Address::factory()->create([
            'profile_id' => $profile->id,
            'ranch_id' => $ranch->id,
            'city_id' => $city->id
        ]);

        // Test relación con Profile
        $this->assertInstanceOf(Profile::class, $address->profile);
        $this->assertEquals($profile->id, $address->profile->id);

        // Test relación con Ranch
        $this->assertInstanceOf(Ranch::class, $address->ranch);
        $this->assertEquals($ranch->id, $address->ranch->id);

        // Test relación con City
        $this->assertInstanceOf(City::class, $address->city);
        $this->assertEquals($city->id, $address->city->id);
    }

    /**
     * Test relaciones del modelo Phone
     */
    public function test_phone_relationships(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $ranch = Ranch::factory()->create(['profile_id' => $profile->id]);
        $operatorCode = OperatorCode::factory()->create();
        $phone = Phone::factory()->create([
            'profile_id' => $profile->id,
            'ranch_id' => $ranch->id,
            'operator_code_id' => $operatorCode->id
        ]);

        // Test relación con Profile
        $this->assertInstanceOf(Profile::class, $phone->profile);
        $this->assertEquals($profile->id, $phone->profile->id);

        // Test relación con Ranch
        $this->assertInstanceOf(Ranch::class, $phone->ranch);
        $this->assertEquals($ranch->id, $phone->ranch->id);

        // Test relación con OperatorCode
        $this->assertInstanceOf(OperatorCode::class, $phone->operatorCode);
        $this->assertEquals($operatorCode->id, $phone->operatorCode->id);
    }

    /**
     * Test relaciones del modelo State
     */
    public function test_state_relationships(): void
    {
        $country = Country::factory()->create();
        $state = State::factory()->create(['countries_id' => $country->id]);
        $city = City::factory()->create(['state_id' => $state->id]);

        // Test relación con Country
        $this->assertInstanceOf(Country::class, $state->country);
        $this->assertEquals($country->id, $state->country->id);

        // Test relación con Cities
        $this->assertTrue($state->cities->contains($city));
        $this->assertEquals(1, $state->cities->count());
    }

    /**
     * Test relaciones del modelo City
     */
    public function test_city_relationships(): void
    {
        $country = Country::factory()->create();
        $state = State::factory()->create(['countries_id' => $country->id]);
        $city = City::factory()->create(['state_id' => $state->id]);

        // Test relación con State
        $this->assertInstanceOf(State::class, $city->state);
        $this->assertEquals($state->id, $city->state->id);
    }

    /**
     * Test métodos de conveniencia del modelo Profile
     */
    public function test_profile_convenience_methods(): void
    {
        $profile = Profile::factory()->create(['is_verified' => true]);
        $this->assertTrue($profile->isVerified());

        $profile = Profile::factory()->create(['is_verified' => false]);
        $this->assertFalse($profile->isVerified());
    }

    /**
     * Test que los modelos tienen timestamps
     */
    public function test_models_have_timestamps(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create();
        $ranch = Ranch::factory()->create();
        $product = Product::factory()->create();

        $this->assertNotNull($user->created_at);
        $this->assertNotNull($user->updated_at);
        $this->assertNotNull($profile->created_at);
        $this->assertNotNull($profile->updated_at);
        $this->assertNotNull($ranch->created_at);
        $this->assertNotNull($ranch->updated_at);
        $this->assertNotNull($product->created_at);
        $this->assertNotNull($product->updated_at);
    }

    /**
     * Test que el modelo User tiene soft deletes
     */
    public function test_user_has_soft_deletes(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $user->delete();
        $this->assertSoftDeleted('users', ['id' => $userId]);

        $restoredUser = User::withTrashed()->find($userId);
        $this->assertNotNull($restoredUser);
        $this->assertNotNull($restoredUser->deleted_at);
    }
}
