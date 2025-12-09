<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Profile;
use App\Models\Ranch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_create_order(): void
    {
        [$buyer, $seller, $product] = $this->prepareProductBetweenProfiles();

        $payload = [
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 1200,
            'delivery_method' => 'buyer_transport',
            'pickup_location' => 'ranch',
        ];

        $response = $this->actingAs($buyer->user)
            ->postJson('/api/orders', $payload);

        $response->assertCreated()
            ->assertJsonPath('buyer_profile_id', $buyer->id)
            ->assertJsonPath('seller_profile_id', $seller->id)
            ->assertJsonPath('quantity', 2)
            ->assertJsonPath('status', 'pending');
    }

    public function test_product_owner_cannot_create_order_on_itself(): void
    {
        [, $seller, $product] = $this->prepareProductBetweenProfiles();

        $response = $this->actingAs($seller->user)
            ->postJson('/api/orders', [
                'product_id' => $product->id,
                'quantity' => 1,
                'delivery_method' => 'buyer_transport',
                'pickup_location' => 'ranch',
            ]);

        $response->assertStatus(422);
    }

    public function test_seller_can_accept_order(): void
    {
        [$buyer, $seller, $product] = $this->prepareProductBetweenProfiles();
        $order = $this->createOrderForProfiles($buyer, $seller, $product, [
            'status' => 'pending',
        ]);

        $response = $this->actingAs($seller->user)
            ->putJson("/api/orders/{$order->id}/accept");

        $response->assertOk()
            ->assertJsonPath('status', 'accepted')
            ->assertJsonPath('receipt_number', fn ($value) => !empty($value));
    }

    public function test_buyer_can_mark_order_as_delivered(): void
    {
        [$buyer, $seller, $product] = $this->prepareProductBetweenProfiles();
        $order = $this->createOrderForProfiles($buyer, $seller, $product, [
            'status' => 'accepted',
        ]);

        $response = $this->actingAs($buyer->user)
            ->putJson("/api/orders/{$order->id}/deliver");

        $response->assertOk()
            ->assertJsonPath('status', 'delivered');
    }

    protected function prepareProductBetweenProfiles(): array
    {
        $buyer = Profile::factory()->for(User::factory())->create();
        $seller = Profile::factory()->for(User::factory())->create();
        $ranch = Ranch::factory()->for($seller)->create();
        $product = Product::factory()->for($ranch)->create([
            'quantity' => 5,
            'price' => 1000,
            'currency' => 'USD',
            'status' => 'active',
        ]);

        return [$buyer, $seller, $product];
    }
    protected function createOrderForProfiles(Profile $buyer, Profile $seller, Product $product, array $overrides = []): Order
    {
        $unitPrice = $product->price;
        $defaults = [
            'product_id' => $product->id,
            'buyer_profile_id' => $buyer->id,
            'seller_profile_id' => $seller->id,
            'conversation_id' => null,
            'ranch_id' => $product->ranch_id,
            'quantity' => 1,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice,
            'currency' => $product->currency,
            'status' => 'pending',
            'delivery_method' => 'buyer_transport',
            'pickup_location' => 'ranch',
        ];

        return Order::create(array_merge($defaults, $overrides));
    }
}
