<?php

namespace Tests\Feature\Auth;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use App\Models\Profile;
use App\Models\Ranch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeleteAccountTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_delete_account_and_related_data(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $profile = Profile::factory()->for($user)->create([
            'photo_users' => $this->storageUrl('profile_images/avatar.jpg'),
        ]);
        Storage::disk('public')->put('profile_images/avatar.jpg', 'fake-avatar');

        $ranch = Ranch::factory()->for($profile)->create([
            'business_license_url' => $this->storageUrl('ranch_docs/license.pdf'),
            'address_id' => null,
        ]);
        Storage::disk('public')->put('ranch_docs/license.pdf', 'fake-license');

        $document = $ranch->documents()->create([
            'certification_type' => 'SENASA',
            'document_url' => $this->storageUrl('ranch_documents/doc.pdf'),
            'original_filename' => 'doc.pdf',
            'file_size' => 1024,
            'order' => 0,
        ]);
        Storage::disk('public')->put('ranch_documents/doc.pdf', 'fake-doc');

        $product = Product::factory()->for($ranch)->create([
            'health_certificate_url' => $this->storageUrl('health/health.pdf'),
        ]);
        Storage::disk('public')->put('health/health.pdf', 'fake-health');

        $image = $product->images()->create([
            'file_url' => $this->storageUrl('product_images/photo.jpg'),
            'file_type' => 'image',
            'is_primary' => true,
            'sort_order' => 0,
            'format' => 'jpg',
        ]);
        Storage::disk('public')->put('product_images/photo.jpg', 'fake-photo');

        $otherProfile = Profile::factory()->create();

        $conversation = Conversation::factory()->create([
            'profile_id_1' => $profile->id,
            'profile_id_2' => $otherProfile->id,
            'product_id' => $product->id,
            'ranch_id' => $ranch->id,
        ]);

        $message = Message::factory()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $profile->id,
            'message_type' => 'document',
            'attachment_url' => $this->storageUrl('chat/attachment.pdf'),
        ]);
        Storage::disk('public')->put('chat/attachment.pdf', 'fake-attachment');

        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/auth/account');

        $response->assertOk()->assertJson([
            'success' => true,
            'message' => 'Cuenta eliminada permanentemente',
        ]);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('profiles', ['id' => $profile->id]);
        $this->assertDatabaseMissing('ranches', ['id' => $ranch->id]);
        $this->assertDatabaseMissing('ranch_documents', ['id' => $document->id]);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
        $this->assertDatabaseMissing('conversations', ['id' => $conversation->id]);
        $this->assertDatabaseMissing('messages', ['id' => $message->id]);

        Storage::disk('public')->assertMissing('profile_images/avatar.jpg');
        Storage::disk('public')->assertMissing('ranch_docs/license.pdf');
        Storage::disk('public')->assertMissing('ranch_documents/doc.pdf');
        Storage::disk('public')->assertMissing('product_images/photo.jpg');
        Storage::disk('public')->assertMissing('health/health.pdf');
        Storage::disk('public')->assertMissing('chat/attachment.pdf');
    }

    /** @test */
    public function guest_cannot_delete_account(): void
    {
        $response = $this->deleteJson('/api/auth/account');

        $response->assertStatus(401);
    }

    private function storageUrl(string $path): string
    {
        $base = rtrim(config('app.url'), '/');

        return $base . '/storage/' . ltrim($path, '/');
    }
}


