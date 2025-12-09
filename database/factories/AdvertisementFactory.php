<?php

namespace Database\Factories;

use App\Models\Advertisement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para el modelo Advertisement
 * 
 * Genera datos de prueba para anuncios del sistema de publicidad.
 */
class AdvertisementFactory extends Factory
{
    protected $model = Advertisement::class;

    /**
     * Define el estado por defecto del modelo.
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['sponsored_product', 'external_ad']);
        
        return [
            'type' => $type,
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->optional()->paragraph(),
            'image_url' => $this->faker->imageUrl(800, 600, 'animals', true),
            'target_url' => $this->faker->optional(0.7)->url(),
            'is_active' => $this->faker->boolean(80), // 80% activos
            'start_date' => $this->faker->dateTimeBetween('-30 days', '+7 days'),
            'end_date' => $this->faker->optional(0.8)->dateTimeBetween('+7 days', '+90 days'),
            'priority' => $this->faker->numberBetween(0, 100),
            'clicks' => $this->faker->numberBetween(0, 1000),
            'impressions' => $this->faker->numberBetween(0, 10000),
            'product_id' => $type === 'sponsored_product' ? Product::factory() : null,
            'advertiser_name' => $type === 'external_ad' ? $this->faker->company() : null,
            'created_by' => User::factory(),
        ];
    }

    /**
     * Estado para anuncios activos
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'start_date' => now()->subDays(7),
            'end_date' => now()->addDays(30),
        ]);
    }

    /**
     * Estado para anuncios inactivos
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Estado para productos patrocinados
     */
    public function sponsoredProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'sponsored_product',
            'product_id' => Product::factory(),
            'advertiser_name' => null,
        ]);
    }

    /**
     * Estado para publicidad externa
     */
    public function externalAd(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'external_ad',
            'product_id' => null,
            'advertiser_name' => $this->faker->company(),
        ]);
    }

    /**
     * Estado para anuncios expirados
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'start_date' => now()->subDays(60),
            'end_date' => now()->subDays(1),
        ]);
    }
}
