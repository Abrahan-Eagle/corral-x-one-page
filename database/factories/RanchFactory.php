<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para el modelo Ranch
 * 
 * Genera datos de prueba para haciendas/ranchos venezolanos
 * con información realista para el marketplace de ganado.
 */
class RanchFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     */
    public function definition(): array
    {
        return [
            'profile_id' => Profile::factory(),
            'name' => $this->faker->company() . ' Ranch',
            'legal_name' => $this->faker->optional(0.7)->company(),
            'tax_id' => $this->faker->optional(0.8)->numerify('J-########-#'),
            // Campos alineados al esquema actual
            'business_description' => $this->faker->optional(0.7)->paragraph(3),
            'address_id' => Address::factory(),
            'is_primary' => $this->faker->boolean(60), // 60% son principales
            'delivery_policy' => $this->faker->optional(0.7)->paragraph(2),
            'return_policy' => $this->faker->optional(0.6)->paragraph(2),
            'contact_hours' => $this->faker->optional(0.8)->randomElement([
                '8:00 AM - 5:00 PM',
                '7:00 AM - 6:00 PM',
                '9:00 AM - 4:00 PM',
                'Lunes a Viernes 8:00 AM - 5:00 PM',
                'Sábados 8:00 AM - 12:00 PM'
            ]),
            'avg_rating' => $this->faker->randomFloat(2, 0, 5),
            'total_sales' => $this->faker->numberBetween(0, 200),
            'last_sale_at' => $this->faker->optional(0.7)->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Estado para haciendas principales
     */
    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
            'avg_rating' => $this->faker->randomFloat(2, 3.5, 5),
            'total_sales' => $this->faker->numberBetween(20, 200),
        ]);
    }

    /**
     * Estado para haciendas secundarias
     */
    public function secondary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => false,
        ]);
    }

    /**
     * Estado para haciendas especializadas en cría
     */
    public function breeding(): static
    {
        return $this->state(fn (array $attributes) => [
        ]);
    }

    /**
     * Estado para haciendas de engorde
     */
    public function fattening(): static
    {
        return $this->state(fn (array $attributes) => [
        ]);
    }

    /**
     * Estado para haciendas lecheras
     */
    public function dairy(): static
    {
        return $this->state(fn (array $attributes) => [
        ]);
    }

    /**
     * Estado para haciendas certificadas
     */
    public function certified(): static
    {
        return $this->state(fn (array $attributes) => [
            'avg_rating' => $this->faker->randomFloat(2, 4, 5),
        ]);
    }

    /**
     * Estado para haciendas con alta calificación
     */
    public function highRated(): static
    {
        return $this->state(fn (array $attributes) => [
            'avg_rating' => $this->faker->randomFloat(2, 4.2, 5),
            'total_sales' => $this->faker->numberBetween(50, 200),
        ]);
    }

    /**
     * Estado para haciendas nuevas
     */
    public function newRanch(): static
    {
        return $this->state(fn (array $attributes) => [
            'avg_rating' => 0,
            'total_sales' => 0,
            'last_sale_at' => null,
        ]);
    }

    /**
     * Estado para haciendas que no aceptan órdenes
     */
    public function notAcceptingOrders(): static
    {
        return $this->state(fn (array $attributes) => [
        ]);
    }

    /**
     * Estado para haciendas con nombres venezolanos
     */
    public function venezuelan(): static
    {
        $venezuelanRanchNames = [
            'Hacienda El Paraíso',
            'Rancho La Esperanza',
            'Finca San José',
            'Hacienda Los Llanos',
            'Rancho El Progreso',
            'Finca La Victoria',
            'Hacienda El Refugio',
            'Rancho San Martín',
            'Finca La Esperanza',
            'Hacienda El Dorado',
            'Rancho La Paz',
            'Finca San Antonio',
            'Hacienda Los Alpes',
            'Rancho El Milagro',
            'Finca La Fortuna',
        ];

        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement($venezuelanRanchNames),
            'legal_name' => $this->faker->randomElement($venezuelanRanchNames) . ' C.A.',
        ]);
    }
}
