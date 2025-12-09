<?php

namespace Tests\Feature;

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

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que el factory de User funciona correctamente
     */
    public function test_user_factory_creates_valid_user(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertContains($user->role, ['admin', 'users']);
        $this->assertIsBool($user->completed_onboarding);
    }

    /**
     * Test que el factory de Profile funciona correctamente
     */
    public function test_profile_factory_creates_valid_profile(): void
    {
        $profile = Profile::factory()->create();

        $this->assertNotNull($profile->firstName);
        $this->assertNotNull($profile->lastName);
        $this->assertContains($profile->user_type, ['buyer', 'seller', 'both']);
        $this->assertIsBool($profile->is_verified);
        $this->assertIsNumeric($profile->rating);
        $this->assertGreaterThanOrEqual(0, $profile->rating);
        $this->assertLessThanOrEqual(5, $profile->rating);
    }

    /**
     * Test que el factory de Ranch funciona correctamente
     */
    public function test_ranch_factory_creates_valid_ranch(): void
    {
        $ranch = Ranch::factory()->create();

        $this->assertNotNull($ranch->name);
        // legal_name puede ser null
        $this->assertIsNumeric($ranch->avg_rating);
        $this->assertGreaterThanOrEqual(0, $ranch->avg_rating);
        $this->assertLessThanOrEqual(5, $ranch->avg_rating);
    }

    /**
     * Test que el factory de Product funciona correctamente
     */
    public function test_product_factory_creates_valid_product(): void
    {
        $product = Product::factory()->create();

        $this->assertNotNull($product->title);
        $this->assertNotNull($product->description);
        $this->assertContains($product->type, ['engorde', 'lechero', 'padrote', 'equipment', 'feed', 'other']);
        if ($product->age !== null) {
            $this->assertIsNumeric($product->age);
            $this->assertGreaterThanOrEqual(0, $product->age);
        }
        $this->assertIsNumeric($product->quantity);
        $this->assertGreaterThan(0, $product->quantity);
        $this->assertIsNumeric($product->price);
        $this->assertGreaterThan(0, $product->price);
        $this->assertContains($product->currency, ['USD', 'VES']);
        $this->assertContains($product->sex, ['male', 'female', 'mixed']);
        // purpose puede ser null para productos que no son cattle
        if ($product->purpose !== null) {
            $this->assertContains($product->purpose, ['breeding', 'meat', 'dairy', 'mixed']);
        }
        $this->assertIsBool($product->is_vaccinated);
        $this->assertContains($product->delivery_method, ['pickup', 'delivery', 'both']);
        $this->assertIsBool($product->negotiable);
        $this->assertContains($product->status, ['active', 'paused', 'sold', 'expired']);
        $this->assertIsInt($product->views);
        $this->assertGreaterThanOrEqual(0, $product->views);
    }

    /**
     * Test que el factory de Product con estado vaccinated funciona
     */
    public function test_product_factory_vaccinated_state(): void
    {
        $product = Product::factory()->vaccinated()->create();

        $this->assertTrue($product->is_vaccinated);
        $this->assertNotNull($product->vaccines_applied);
    }

    /**
     * Test que el factory de Product con estado breeding funciona
     */
    public function test_product_factory_breeding_state(): void
    {
        $product = Product::factory()->breeding()->create();

        $this->assertEquals('breeding', $product->purpose);
        $this->assertNotNull($product->genetic_test_results);
    }

    /**
     * Test que el factory de Address funciona correctamente
     */
    public function test_address_factory_creates_valid_address(): void
    {
        $address = Address::factory()->create();

        $this->assertNotNull($address->adressses);
        $this->assertIsNumeric($address->latitude);
        $this->assertIsNumeric($address->longitude);
        $this->assertContains($address->status, ['completeData', 'incompleteData', 'notverified']);
    }

    /**
     * Test que el factory de Phone funciona correctamente
     */
    public function test_phone_factory_creates_valid_phone(): void
    {
        $phone = Phone::factory()->create();

        $this->assertNotNull($phone->number);
        $this->assertIsBool($phone->is_primary);
    }

    /**
     * Test que el factory de Category funciona correctamente
     */
    public function test_category_factory_creates_valid_category(): void
    {
        $category = Category::factory()->create();

        $this->assertNotNull($category->name);
        $this->assertIsBool($category->is_active);
    }

    /**
     * Test que el factory de OperatorCode funciona correctamente
     */
    public function test_operator_code_factory_creates_valid_operator_code(): void
    {
        $operatorCode = OperatorCode::factory()->create();

        $this->assertNotNull($operatorCode->code);
        $this->assertIsString($operatorCode->code);
        $this->assertEquals(3, strlen($operatorCode->code));
        $this->assertNotNull($operatorCode->name);
        $this->assertIsBool($operatorCode->is_active);
    }

    /**
     * Test que el factory de Country funciona correctamente
     */
    public function test_country_factory_creates_valid_country(): void
    {
        $country = Country::factory()->create();

        $this->assertNotNull($country->name);
        $this->assertNotNull($country->sortname);
        $this->assertIsNumeric($country->phonecode);
    }

    /**
     * Test que el factory de State funciona correctamente
     */
    public function test_state_factory_creates_valid_state(): void
    {
        $state = State::factory()->create();

        $this->assertNotNull($state->name);
        $this->assertNotNull($state->countries_id);
    }

    /**
     * Test que el factory de City funciona correctamente
     */
    public function test_city_factory_creates_valid_city(): void
    {
        $city = City::factory()->create();

        $this->assertNotNull($city->name);
        $this->assertNotNull($city->state_id);
    }

    /**
     * Test que los factories con estados específicos funcionan
     */
    public function test_factory_states_work_correctly(): void
    {
        // Test Profile con estado admin
        $adminProfile = Profile::factory()->admin()->create();
        $this->assertEquals('both', $adminProfile->user_type);
        $this->assertTrue($adminProfile->is_verified);

        // Test Ranch con estado venezuelan
        $venezuelanRanch = Ranch::factory()->venezuelan()->create();
        // Verificar que el nombre contiene palabras típicas de haciendas venezolanas
        $this->assertTrue(
            str_contains($venezuelanRanch->name, 'Hacienda') || 
            str_contains($venezuelanRanch->name, 'Rancho') || 
            str_contains($venezuelanRanch->name, 'Finca'),
            "El nombre del ranch no contiene palabras típicas venezolanas: {$venezuelanRanch->name}"
        );

        // Test Address con estado cattleStates
        $cattleAddress = Address::factory()->cattleStates()->create();
        $this->assertGreaterThanOrEqual(6, $cattleAddress->latitude);
        $this->assertLessThanOrEqual(12, $cattleAddress->latitude);
    }
}
