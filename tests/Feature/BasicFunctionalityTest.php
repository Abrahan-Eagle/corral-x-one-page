<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use App\Models\Ranch;
use App\Models\Product;
use App\Models\Category;
use App\Models\OperatorCode;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Address;
use App\Models\Phone;

class BasicFunctionalityTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que se puede crear un usuario completo con todas sus relaciones
     */
    public function test_can_create_complete_user_with_relationships(): void
    {
        // Crear usuario
        $user = User::factory()->create([
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
            'role' => 'users'
        ]);

        // Crear perfil
        $profile = Profile::factory()->create([
            'user_id' => $user->id,
            'firstName' => 'Juan',
            'lastName' => 'Pérez',
            'user_type' => 'seller',
            'is_verified' => true,
            'rating' => 4.5
        ]);

        // Crear ranch
        $ranch = Ranch::factory()->create([
            'profile_id' => $profile->id,
            'name' => 'Hacienda El Paraíso',
            'legal_name' => 'Hacienda El Paraíso C.A.',
            'avg_rating' => 4.2
        ]);

        // Crear producto
        $product = Product::factory()->create([
            'ranch_id' => $ranch->id,
            'title' => 'Terneros Brahman de 12 meses',
            'description' => 'Terneros Brahman de excelente calidad, vacunados y con certificado sanitario',
            'type' => 'engorde',
            'breed' => 'Brahman',
            'age' => 12,
            'quantity' => 5,
            'price' => 1500.00,
            'currency' => 'USD',
            'weight_avg' => 300,
            'sex' => 'male',
            'purpose' => 'meat',
            'is_vaccinated' => true,
            'delivery_method' => 'pickup',
            'negotiable' => true,
            'status' => 'active'
        ]);

        // Verificar que todo se creó correctamente
        $this->assertDatabaseHas('users', ['email' => 'juan@example.com']);
        $this->assertDatabaseHas('profiles', ['firstName' => 'Juan', 'lastName' => 'Pérez']);
        $this->assertDatabaseHas('ranches', ['name' => 'Hacienda El Paraíso']);
        $this->assertDatabaseHas('products', ['title' => 'Terneros Brahman de 12 meses']);

        // Verificar relaciones
        $this->assertEquals($user->id, $profile->user_id);
        $this->assertEquals($profile->id, $ranch->profile_id);
        $this->assertEquals($ranch->id, $product->ranch_id);
    }

    /**
     * Test que se pueden crear múltiples productos para un ranch
     */
    public function test_can_create_multiple_products_for_ranch(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $ranch = Ranch::factory()->create(['profile_id' => $profile->id]);

        // Crear múltiples productos
        $products = Product::factory()->count(3)->create(['ranch_id' => $ranch->id]);

        $this->assertEquals(3, $ranch->products->count());
        $this->assertEquals(3, Product::where('ranch_id', $ranch->id)->count());
    }

    /**
     * Test que se pueden crear múltiples ranches para un perfil
     */
    public function test_can_create_multiple_ranches_for_profile(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        // Crear múltiples ranches
        $ranches = Ranch::factory()->count(2)->create(['profile_id' => $profile->id]);

        $this->assertEquals(2, $profile->ranches->count());
        $this->assertEquals(2, Ranch::where('profile_id', $profile->id)->count());
    }

    /**
     * Test que se pueden crear categorías y asociarlas a productos
     */
    public function test_can_create_categories_and_associate_with_products(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $ranch = Ranch::factory()->create(['profile_id' => $profile->id]);
        $product = Product::factory()->create(['ranch_id' => $ranch->id]);

        // Crear categorías
        $cattleCategory = Category::factory()->create(['name' => 'Ganado']);
        $equipmentCategory = Category::factory()->create(['name' => 'Equipos']);

        // Asociar categorías al producto
        $product->categories()->attach([$cattleCategory->id, $equipmentCategory->id]);

        $this->assertEquals(2, $product->categories->count());
        $this->assertTrue($product->categories->contains($cattleCategory));
        $this->assertTrue($product->categories->contains($equipmentCategory));
    }

    /**
     * Test que se pueden crear códigos de operadora y asociarlos a teléfonos
     */
    public function test_can_create_operator_codes_and_associate_with_phones(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        // Crear código de operadora
        $operatorCode = OperatorCode::factory()->create([
            'code' => '412',
            'name' => 'Digitel'
        ]);

        // Crear teléfono
        $phone = Phone::factory()->create([
            'profile_id' => $profile->id,
            'operator_code_id' => $operatorCode->id,
            'number' => '1234567'
        ]);

        $this->assertEquals($operatorCode->id, $phone->operator_code_id);
        $this->assertEquals('412', $phone->operatorCode->code);
        $this->assertEquals('Digitel', $phone->operatorCode->name);
    }

    /**
     * Test que se pueden crear ubicaciones geográficas completas
     */
    public function test_can_create_complete_geographic_locations(): void
    {
        // Crear país
        $country = Country::factory()->create([
            'name' => 'Venezuela',
            'sortname' => 'VE',
            'phonecode' => 58
        ]);

        // Crear estado
        $state = State::factory()->create([
            'name' => 'Zulia',
            'countries_id' => $country->id
        ]);

        // Crear ciudad
        $city = City::factory()->create([
            'name' => 'Maracaibo',
            'state_id' => $state->id
        ]);

        // Crear dirección
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $address = Address::factory()->create([
            'profile_id' => $profile->id,
            'city_id' => $city->id,
            'latitude' => 10.6427,
            'longitude' => -71.6125
        ]);

        // Verificar relaciones geográficas
        $this->assertEquals($country->id, $state->country->id);
        $this->assertEquals($state->id, $city->state->id);
        $this->assertEquals($city->id, $address->city->id);
        $this->assertEquals('Venezuela', $address->city->state->country->name);
    }

    /**
     * Test que se pueden crear productos con datos específicos de ganado venezolano
     */
    public function test_can_create_products_with_venezuelan_cattle_data(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $ranch = Ranch::factory()->create(['profile_id' => $profile->id]);

        $product = Product::factory()->create([
            'ranch_id' => $ranch->id,
            'type' => 'engorde',
            'breed' => 'Brahman',
            'sex' => 'male',
            'purpose' => 'breeding',
            'is_vaccinated' => true,
            'vaccines_applied' => json_encode([
                'Vacuna contra brucelosis',
                'Vacuna contra tuberculosis',
                'Vacuna contra fiebre aftosa'
            ]),
            'documentation_included' => json_encode([
                'Certificado sanitario',
                'Registro de vacunación',
                'Certificado de origen'
            ]),
            'genetic_test_results' => json_encode([
                'ADN paterno confirmado',
                'Prueba de paternidad'
            ])
        ]);

        // Verificar datos específicos de ganado
        $this->assertEquals('engorde', $product->type);
        $this->assertEquals('Brahman', $product->breed);
        $this->assertEquals('male', $product->sex);
        $this->assertEquals('breeding', $product->purpose);
        $this->assertTrue($product->is_vaccinated);

        // Verificar que los datos JSON se pueden decodificar
        $vaccines = json_decode($product->vaccines_applied, true);
        $this->assertIsArray($vaccines);
        $this->assertContains('Vacuna contra brucelosis', $vaccines);

        $documentation = json_decode($product->documentation_included, true);
        $this->assertIsArray($documentation);
        $this->assertContains('Certificado sanitario', $documentation);

        $geneticTests = json_decode($product->genetic_test_results, true);
        $this->assertIsArray($geneticTests);
        $this->assertContains('ADN paterno confirmado', $geneticTests);
    }
}
