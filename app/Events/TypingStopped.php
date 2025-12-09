<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento cuando un usuario deja de escribir
 */
class TypingStopped implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $userId;
    public int $conversationId;

    public function __construct(int $userId, int $conversationId)
    {
        $this->userId = $userId;
        $this->conversationId = $conversationId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("conversation.{$this->conversationId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'TypingStopped';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'conversation_id' => $this->conversationId,
        ];
    }
}

