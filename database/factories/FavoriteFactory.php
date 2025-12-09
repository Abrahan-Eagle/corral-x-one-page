<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para el modelo Favorite
 * 
 * Genera datos de prueba para productos favoritos
 * de usuarios del marketplace de ganado.
 */
class FavoriteFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     */
    public function definition(): array
    {
        return [
            'profile_id' => Profile::factory(),
            'product_id' => Product::factory(),
        ];
    }

    /**
     * Estado para favoritos recientes
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Estado para favoritos antiguos
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 year', '-3 months'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', '-3 months'),
        ]);
    }

    /**
     * Estado para favoritos de productos activos
     */
    public function activeProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::factory()->active(),
        ]);
    }

    /**
     * Estado para favoritos de productos destacados
     */
    public function featuredProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::factory()->featured(),
        ]);
    }

    /**
     * Estado para favoritos de productos premium
     */
    public function premiumProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::factory()->premium(),
        ]);
    }

    /**
     * Estado para favoritos de productos de engorde
     */
    public function fatteningProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::factory()->fattening(),
        ]);
    }

    /**
     * Estado para favoritos de productos lecheros
     */
    public function dairyProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::factory()->dairy(),
        ]);
    }

    /**
     * Estado para favoritos de productos reproductores
     */
    public function breedingProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::factory()->breeding(),
        ]);
    }

    /**
     * Estado para favoritos de productos vacunados
     */
    public function vaccinatedProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::factory()->vaccinated(),
        ]);
    }

    /**
     * Estado para favoritos de productos con entrega
     */
    public function productWithDelivery(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::factory()->withDelivery(),
        ]);
    }

    /**
     * Estado para favoritos de productos económicos
     */
    public function budgetProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::factory()->budget(),
        ]);
    }

    /**
     * Estado para favoritos de vendedores verificados
     */
    public function verifiedSeller(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_id' => Profile::factory()->verifiedSeller(),
        ]);
    }

    /**
     * Estado para favoritos de compradores verificados
     */
    public function verifiedBuyer(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_id' => Profile::factory()->verifiedBuyer(),
        ]);
    }

    /**
     * Estado para favoritos de usuarios premium
     */
    public function premiumUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_id' => Profile::factory()->premium(),
        ]);
    }

    /**
     * Estado para favoritos de usuarios experimentados
     */
    public function experiencedUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_id' => Profile::factory()->experienced(),
        ]);
    }

    /**
     * Estado para favoritos de usuarios nuevos
     */
    public function newUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_id' => Profile::factory()->newUser(),
        ]);
    }
}
