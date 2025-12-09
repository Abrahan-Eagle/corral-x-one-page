<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\Product;
use App\Models\Ranch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para el modelo Conversation
 * 
 * Genera datos de prueba para conversaciones de chat
 * entre compradores y vendedores en el marketplace de ganado.
 */
class ConversationFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     */
    public function definition(): array
    {
        return [
            'profile_id_1' => Profile::factory(),
            'profile_id_2' => Profile::factory(),
            'product_id' => $this->faker->optional(0.7)->randomElement([null, Product::factory()]),
            'ranch_id' => $this->faker->optional(0.6)->randomElement([null, Ranch::factory()]),
            'last_message_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 month', 'now'),
            'is_active' => $this->faker->boolean(85), // 85% activas
        ];
    }

    /**
     * Estado para conversaciones activas
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'last_message_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Estado para conversaciones archivadas
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'last_message_at' => $this->faker->dateTimeBetween('-3 months', '-1 month'),
        ]);
    }

    /**
     * Estado para conversaciones recientes
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_message_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'is_active' => true,
        ]);
    }

    /**
     * Estado para conversaciones antiguas
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_message_at' => $this->faker->dateTimeBetween('-6 months', '-1 month'),
            'is_active' => $this->faker->boolean(60),
        ]);
    }

    /**
     * Estado para conversaciones sobre productos
     */
    public function aboutProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::factory(),
            'ranch_id' => Ranch::factory(),
        ]);
    }

    /**
     * Estado para conversaciones sobre haciendas
     */
    public function aboutRanch(): static
    {
        return $this->state(fn (array $attributes) => [
            'ranch_id' => Ranch::factory(),
            'product_id' => null,
        ]);
    }

    /**
     * Estado para conversaciones generales
     */
    public function general(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => null,
            'ranch_id' => null,
        ]);
    }

    /**
     * Estado para conversaciones sobre productos de engorde
     */
    public function aboutFatteningProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::factory()->fattening(),
            'ranch_id' => Ranch::factory(),
        ]);
    }

    /**
     * Estado para conversaciones sobre productos lecheros
     */
    public function aboutDairyProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::factory()->dairy(),
            'ranch_id' => Ranch::factory(),
        ]);
    }

    /**
     * Estado para conversaciones sobre productos reproductores
     */
    public function aboutBreedingProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::factory()->breeding(),
            'ranch_id' => Ranch::factory(),
        ]);
    }

    /**
     * Estado para conversaciones sobre productos premium
     */
    public function aboutPremiumProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::factory()->premium(),
            'ranch_id' => Ranch::factory(),
        ]);
    }

    /**
     * Estado para conversaciones sobre productos económicos
     */
    public function aboutBudgetProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::factory()->budget(),
            'ranch_id' => Ranch::factory(),
        ]);
    }

    /**
     * Estado para conversaciones entre vendedores verificados
     */
    public function verifiedSeller(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_id_2' => Profile::factory()->verifiedSeller(),
        ]);
    }

    /**
     * Estado para conversaciones entre compradores verificados
     */
    public function verifiedBuyer(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_id_1' => Profile::factory()->verifiedBuyer(),
        ]);
    }

    /**
     * Estado para conversaciones entre usuarios premium
     */
    public function premiumUsers(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_id_1' => Profile::factory()->premium(),
            'profile_id_2' => Profile::factory()->premium(),
        ]);
    }

    /**
     * Estado para conversaciones entre usuarios experimentados
     */
    public function experiencedUsers(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_id_1' => Profile::factory()->experienced(),
            'profile_id_2' => Profile::factory()->experienced(),
        ]);
    }

    /**
     * Estado para conversaciones entre usuarios nuevos
     */
    public function newUsers(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_id_1' => Profile::factory()->newUser(),
            'profile_id_2' => Profile::factory()->newUser(),
        ]);
    }

    /**
     * Estado para conversaciones sin mensajes
     */
    public function noMessages(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_message_at' => null,
            'is_active' => true,
        ]);
    }

    /**
     * Estado para conversaciones con muchos mensajes
     */
    public function withManyMessages(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_message_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'is_active' => true,
        ]);
    }
}
