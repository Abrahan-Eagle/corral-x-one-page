<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento de mensaje enviado para broadcasting en tiempo real
 * Se dispara cuando un usuario envía un mensaje en una conversación
 */
class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;
    public int $conversationId;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message, int $conversationId)
    {
        $this->message = $message;
        $this->conversationId = $conversationId;

        \Log::info('🔊 MessageSent event creado', [
            'message_id' => $message->id,
            'conversation_id' => $conversationId,
            'sender_id' => $message->sender_id
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        \Log::info('🔊 Broadcasting MessageSent en canal público', [
            'channel' => "conversation.{$this->conversationId}",
            'message_id' => $this->message->id
        ]);

        return [
            new Channel("conversation.{$this->conversationId}"),
        ];
    }

    /**
     * Nombre del evento para el cliente
     */
    public function broadcastAs(): string
    {
        return 'MessageSent';
    }

    /**
     * Datos a enviar al cliente
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'sender_id' => $this->message->sender_id,
                'content' => $this->message->content,
                'message_type' => $this->message->message_type,
                'sent_at' => $this->message->created_at->toISOString(),
                'read_at' => $this->message->read_at?->toISOString(),
                'sender' => [
                    'id' => $this->message->sender->id,
                    'name' => $this->message->sender->first_name . ' ' . $this->message->sender->last_name,
                    'avatar' => $this->message->sender->profile_picture,
                ],
            ],
            'conversation_id' => $this->conversationId,
        ];
    }
}

