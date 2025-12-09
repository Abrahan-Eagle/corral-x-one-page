<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\Profile;
use App\Models\Ranch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $buyer = Profile::factory()->create();
        $seller = Profile::factory()->create();
        $ranch = Ranch::factory()->for($seller)->create();
        $product = Product::factory()->for($ranch)->create([
            'status' => 'active',
        ]);

        $quantity = $this->faker->numberBetween(1, 5);
        $unitPrice = $product->price;

        return [
            'product_id' => $product->id,
            'buyer_profile_id' => $buyer->id,
            'seller_profile_id' => $seller->id,
            'conversation_id' => null,
            'ranch_id' => $ranch->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $unitPrice * $quantity,
            'currency' => $product->currency,
            'status' => 'pending',
            'delivery_method' => Arr::random(['buyer_transport', 'seller_transport', 'external_delivery']),
            'pickup_location' => Arr::random(['ranch', 'other']),
            'pickup_address' => $this->faker->address,
            'delivery_address' => $this->faker->address,
            'delivery_cost' => $this->faker->randomFloat(2, 0, 300),
            'delivery_cost_currency' => $product->currency,
            'expected_pickup_date' => $this->faker->dateTimeBetween('now', '+5 days'),
            'buyer_notes' => $this->faker->optional()->sentence(),
            'seller_notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function pending(): self
    {
        return $this->state(fn () => ['status' => 'pending']);
    }

    public function accepted(): self
    {
        return $this->state(fn () => [
            'status' => 'accepted',
            'accepted_at' => now(),
            'receipt_number' => $this->faker->unique()->regexify('CORRALX-\d{8}-\d{8}'),
        ]);
    }

    public function delivered(): self
    {
        return $this->accepted()->state(fn () => [
            'status' => 'delivered',
            'delivered_at' => now(),
            'actual_pickup_date' => now(),
        ]);
    }

    public function completed(): self
    {
        return $this->delivered()->state(fn () => [
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): self
    {
        return $this->state(fn () => [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }
}
