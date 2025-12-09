<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use App\Models\Ranch;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function can_get_my_profile_when_authenticated()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id', 'user_id', 'firstName', 'lastName', 'bio',
                'rating', 'is_verified', 'user', 'ranches', 'addresses'
            ])
            ->assertJson(['id' => $profile->id]);
    }

    /** @test */
    public function cannot_get_profile_when_not_authenticated()
    {
        $response = $this->getJson('/api/profile');

        $response->assertStatus(401);
    }

    /** @test */
    public function returns_404_when_user_has_no_profile()
    {
        $user = User::factory()->create();
        // No crear perfil

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/profile');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Perfil no encontrado']);
    }

    /** @test */
    public function can_update_my_profile()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/profile', [
                'firstName' => 'Juan',
                'lastName' => 'Pérez',
                'bio' => 'Soy un ganadero con 20 años de experiencia',
                'maritalStatus' => 'married',
                'sex' => 'M',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'firstName' => 'Juan',
                'lastName' => 'Pérez',
                'bio' => 'Soy un ganadero con 20 años de experiencia',
            ]);

        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
            'firstName' => 'Juan',
            'bio' => 'Soy un ganadero con 20 años de experiencia',
        ]);
    }

    /** @test */
    public function bio_cannot_exceed_500_characters()
    {
        $user = User::factory()->create();
        Profile::factory()->create(['user_id' => $user->id]);

        $longBio = str_repeat('a', 501);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/profile', [
                'bio' => $longBio,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bio']);
    }

    /** @test */
    public function can_upload_profile_photo()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        $file = UploadedFile::fake()->image('profile.jpg', 500, 500);

        $response = $this->actingAs($user, 'sanctum')
            ->post('/api/profile/photo', [
                'photo_users' => $file,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['photo_users']);

        $this->assertNotNull($profile->fresh()->photo_users);
        Storage::disk('public')->assertExists('profile_images/' . $file->hashName());
    }

    /** @test */
    public function photo_upload_requires_authentication()
    {
        $file = UploadedFile::fake()->image('profile.jpg');

        $response = $this->postJson('/api/profile/photo', [
            'photo_users' => $file,
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function photo_must_be_valid_image()
    {
        $user = User::factory()->create();
        Profile::factory()->create(['user_id' => $user->id]);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($user, 'sanctum')
            ->post('/api/profile/photo', [
                'photo_users' => $file,
            ]);

        // Laravel puede retornar 302 o 422 dependiendo de configuración
        $this->assertTrue(
            in_array($response->status(), [302, 422]),
            "Expected 302 or 422, got {$response->status()}"
        );
    }

    /** @test */
    public function can_get_public_profile_by_id()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/profiles/{$profile->id}");

        $response->assertStatus(200)
            ->assertJson(['id' => $profile->id])
            ->assertJsonStructure(['user', 'ranches', 'addresses']);
    }

    /** @test */
    public function returns_404_for_nonexistent_public_profile()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/profiles/99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function can_get_my_products()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $ranch = Ranch::factory()->create(['profile_id' => $profile->id]);
        
        // Productos del usuario
        Product::factory()->count(3)->create(['ranch_id' => $ranch->id]);
        
        // Productos de otro usuario
        $otherRanch = Ranch::factory()->create();
        Product::factory()->count(2)->create(['ranch_id' => $otherRanch->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/me/products');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function my_products_returns_empty_array_when_no_products()
    {
        $user = User::factory()->create();
        Profile::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/me/products');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    /** @test */
    public function can_get_my_ranches()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        
        Ranch::factory()->create([
            'profile_id' => $profile->id,
            'name' => 'Hacienda Principal',
            'is_primary' => true,
        ]);
        
        Ranch::factory()->create([
            'profile_id' => $profile->id,
            'name' => 'Hacienda Secundaria',
            'is_primary' => false,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/me/ranches');

        $response->assertStatus(200)
            ->assertJsonCount(2)
            ->assertJsonPath('0.name', 'Hacienda Principal'); // Primary first
    }

    /** @test */
    public function can_get_my_metrics()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $ranch = Ranch::factory()->create(['profile_id' => $profile->id]);
        
        Product::factory()->count(5)->create([
            'ranch_id' => $ranch->id,
            'status' => 'active',
        ]);
        
        Product::factory()->count(2)->create([
            'ranch_id' => $ranch->id,
            'status' => 'sold',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/me/metrics');

        $response->assertStatus(200)
            ->assertJson([
                'total_products' => 7,
                'active_products' => 5,
                'sold_products' => 2,
                'total_ranches' => 1,
            ]);
    }

    /** @test */
    public function metrics_returns_zeros_when_no_data()
    {
        $user = User::factory()->create();
        Profile::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/me/metrics');

        $response->assertStatus(200)
            ->assertJson([
                'total_products' => 0,
                'active_products' => 0,
                'sold_products' => 0,
                'total_views' => 0,
                'total_favorites' => 0,
            ]);
    }

    /** @test */
    public function can_get_ranches_by_profile_id()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        
        Ranch::factory()->count(3)->create(['profile_id' => $profile->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/profiles/{$profile->id}/ranches");

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /** @test */
    public function ranches_by_profile_returns_empty_when_none()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/profiles/{$profile->id}/ranches");

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }
}

