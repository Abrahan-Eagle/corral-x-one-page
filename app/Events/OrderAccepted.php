<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento de pedido aceptado para broadcasting en tiempo real
 * Se dispara cuando un vendedor acepta un pedido
 */
class OrderAccepted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order->load(['product', 'buyer', 'seller', 'ranch']);
        
        \Log::info('🔊 OrderAccepted event creado', [
            'order_id' => $order->id,
            'buyer_id' => $order->buyer_id,
            'seller_id' => $order->seller_id,
        ]);
    }

    public function broadcastOn(): array
    {
        return [
            new Channel("profile.{$this->order->buyer_profile_id}"),
            new Channel("profile.{$this->order->seller_profile_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderAccepted';
    }

    public function broadcastWith(): array
    {
        return [
            'order' => $this->order->toArray(),
            'type' => 'accepted',
        ];
    }
}

