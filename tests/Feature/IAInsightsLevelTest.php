<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IAInsightsLevelTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        Sanctum::actingAs($admin);

        return $admin;
    }

    private function createUserWithProfile(array $userAttributes = [], array $profileAttributes = []): User
    {
        $user = User::factory()->create($userAttributes);
        Profile::factory()
            ->for($user)
            ->create($profileAttributes);

        return $user->fresh();
    }

    public function test_admin_can_upgrade_user_to_premium(): void
    {
        $this->actingAsAdmin();

        $target = $this->createUserWithProfile(
            ['role' => 'users'],
            ['is_premium_seller' => false]
        );
        $this->assertTrue(
            Profile::where('user_id', $target->id)->exists(),
            'El perfil para el usuario objetivo debe existir'
        );

        $response = $this->postJson("/api/ia-insights/users/{$target->id}/level", [
            'level' => 'premium',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.level', 'premium');

        $updatedTarget = $target->fresh()->load('profile');
        $this->assertSame('users', $updatedTarget->role);
        $this->assertTrue($updatedTarget->profile->is_premium_seller);
    }

    public function test_admin_can_downgrade_user_to_free(): void
    {
        $this->actingAsAdmin();

        $target = $this->createUserWithProfile(
            ['role' => 'users'],
            ['is_premium_seller' => true]
        );

        $response = $this->postJson("/api/ia-insights/users/{$target->id}/level", [
            'level' => 'free',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.level', 'free');

        $freshTarget = $target->fresh()->load('profile');
        $this->assertSame('users', $freshTarget->role);
        $this->assertFalse($freshTarget->profile->is_premium_seller);
    }

    public function test_admin_can_promote_user_to_admin(): void
    {
        $this->actingAsAdmin();

        $target = $this->createUserWithProfile(
            ['role' => 'users'],
            ['is_premium_seller' => true]
        );

        $response = $this->postJson("/api/ia-insights/users/{$target->id}/level", [
            'level' => 'admin',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.level', 'admin');

        $freshTarget = $target->fresh()->load('profile');
        $this->assertSame('admin', $freshTarget->role);
        $this->assertFalse($freshTarget->profile->is_premium_seller);
    }

    public function test_non_admin_cannot_update_levels(): void
    {
        $user = $this->createUserWithProfile(['role' => 'users']);
        Sanctum::actingAs($user);

        $otherUser = $this->createUserWithProfile(['role' => 'users']);

        $response = $this->postJson("/api/ia-insights/users/{$otherUser->id}/level", [
            'level' => 'premium',
        ]);

        $response->assertForbidden();
    }
}

