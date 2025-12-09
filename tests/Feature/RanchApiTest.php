<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Profile;
use App\Models\Ranch;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RanchApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function can_update_my_ranch()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $ranch = Ranch::factory()->create([
            'profile_id' => $profile->id,
            'name' => 'Hacienda Original',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/ranches/{$ranch->id}", [
                'name' => 'Hacienda Actualizada',
                'legal_name' => 'Nueva Razón Social',
                'business_description' => 'Descripción actualizada',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ranch updated successfully',
            ])
            ->assertJsonPath('data.name', 'Hacienda Actualizada');

        $this->assertDatabaseHas('ranches', [
            'id' => $ranch->id,
            'name' => 'Hacienda Actualizada',
        ]);
    }

    /** @test */
    public function cannot_update_ranch_i_do_not_own()
    {
        $user = User::factory()->create();
        Profile::factory()->create(['user_id' => $user->id]);
        
        // Ranch de otro usuario
        $otherRanch = Ranch::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/ranches/{$otherRanch->id}", [
                'name' => 'Intento de Hack',
            ]);

        $response->assertStatus(403)
            ->assertJson(['success' => false]);
    }

    /** @test */
    public function update_requires_authentication()
    {
        $ranch = Ranch::factory()->create();

        $response = $this->putJson("/api/ranches/{$ranch->id}", [
            'name' => 'Test',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function marking_ranch_as_primary_unmarks_others()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        
        $ranch1 = Ranch::factory()->create([
            'profile_id' => $profile->id,
            'is_primary' => true,
        ]);
        
        $ranch2 = Ranch::factory()->create([
            'profile_id' => $profile->id,
            'is_primary' => false,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/ranches/{$ranch2->id}", [
                'is_primary' => true,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('ranches', [
            'id' => $ranch1->id,
            'is_primary' => false, // Ya no es primary
        ]);

        $this->assertDatabaseHas('ranches', [
            'id' => $ranch2->id,
            'is_primary' => true, // Ahora es primary
        ]);
    }

    /** @test */
    public function can_delete_ranch_without_active_products()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        
        // Crear 2 ranches (mínimo para poder eliminar uno)
        Ranch::factory()->create(['profile_id' => $profile->id, 'is_primary' => true]);
        $ranch2 = Ranch::factory()->create(['profile_id' => $profile->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/ranches/{$ranch2->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Ranch deleted successfully',
            ]);

        $this->assertSoftDeleted('ranches', ['id' => $ranch2->id]);
    }

    /** @test */
    public function cannot_delete_ranch_with_active_products()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        
        Ranch::factory()->create(['profile_id' => $profile->id, 'is_primary' => true]);
        $ranch2 = Ranch::factory()->create(['profile_id' => $profile->id]);
        
        // Producto activo en ranch2
        Product::factory()->create([
            'ranch_id' => $ranch2->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/ranches/{$ranch2->id}");

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot delete ranch with active products',
            ]);

        $this->assertDatabaseHas('ranches', ['id' => $ranch2->id]);
    }

    /** @test */
    public function cannot_delete_the_only_ranch()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        $ranch = Ranch::factory()->create(['profile_id' => $profile->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/ranches/{$ranch->id}");

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Cannot delete the only ranch. At least one ranch is required',
            ]);

        $this->assertDatabaseHas('ranches', ['id' => $ranch->id]);
    }

    /** @test */
    public function cannot_delete_ranch_i_do_not_own()
    {
        $user = User::factory()->create();
        Profile::factory()->create(['user_id' => $user->id]);
        
        $otherRanch = Ranch::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/ranches/{$otherRanch->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function delete_requires_authentication()
    {
        $ranch = Ranch::factory()->create();

        $response = $this->deleteJson("/api/ranches/{$ranch->id}");

        $response->assertStatus(401);
    }

    /** @test */
    public function deleting_primary_ranch_promotes_another()
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->create(['user_id' => $user->id]);
        
        $ranch1 = Ranch::factory()->create([
            'profile_id' => $profile->id,
            'is_primary' => true,
        ]);
        
        $ranch2 = Ranch::factory()->create([
            'profile_id' => $profile->id,
            'is_primary' => false,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson("/api/ranches/{$ranch1->id}");

        $response->assertStatus(200);

        // Ranch2 ahora debe ser primary
        $this->assertDatabaseHas('ranches', [
            'id' => $ranch2->id,
            'is_primary' => true,
        ]);
    }
}

