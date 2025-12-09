<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ChatApiTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithProfile(): User
    {
        $user = User::factory()->create();
        Profile::factory()->for($user)->create();
        return $user->fresh();
    }

    public function test_can_create_and_list_conversations(): void
    {
        $userA = $this->createUserWithProfile();
        $userB = $this->createUserWithProfile();
        Sanctum::actingAs($userA);

        $resCreate = $this->postJson('/api/chat/conversations', [
            'profile_id_2' => $userB->profile->id,
        ]);
        $resCreate->assertCreated();

        $resList = $this->getJson('/api/chat/conversations');
        $resList->assertOk();
        $this->assertGreaterThanOrEqual(1, count($resList->json()));
    }

    public function test_can_send_and_list_messages(): void
    {
        $userA = $this->createUserWithProfile();
        $userB = $this->createUserWithProfile();
        Sanctum::actingAs($userA);

        $conv = $this->postJson('/api/chat/conversations', [
            'profile_id_2' => $userB->profile->id,
        ])->json();

        $conversationId = $conv['id'];

        $send = $this->postJson("/api/chat/conversations/{$conversationId}/messages", [
            'content' => 'Hola desde test',
        ]);
        $send->assertCreated();

        $list = $this->getJson("/api/chat/conversations/{$conversationId}/messages");
        $list->assertOk();
        $this->assertSame('Hola desde test', $list->json()[0]['content']);
    }

    public function test_can_mark_messages_as_read_and_delete_conversation(): void
    {
        $userA = $this->createUserWithProfile();
        $userB = $this->createUserWithProfile();
        Sanctum::actingAs($userA);

        $conv = $this->postJson('/api/chat/conversations', [
            'profile_id_2' => $userB->profile->id,
        ])->json();
        $conversationId = $conv['id'];

        $this->postJson("/api/chat/conversations/{$conversationId}/messages", [
            'content' => 'mensaje 1',
        ])->assertCreated();

        $this->postJson("/api/chat/conversations/{$conversationId}/read")
            ->assertOk()
            ->assertJson(['marked' => true]);

        $this->deleteJson("/api/chat/conversations/{$conversationId}")
            ->assertOk()
            ->assertJson(['deleted' => true]);
    }
}


