<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para el modelo Country
 * 
 * Genera datos de prueba para países con información realista
 * para el contexto venezolano del marketplace de ganado.
 */
class CountryFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->country(),
            'sortname' => $this->faker->countryCode(),
            'phonecode' => $this->faker->numberBetween(1, 999),
        ];
    }

    /**
     * Estado para Venezuela (país principal)
     */
    public function venezuela(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Venezuela',
            'sortname' => 'VE',
            'phonecode' => 58,
        ]);
    }

    /**
     * Estado para Colombia (país vecino)
     */
    public function colombia(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Colombia',
            'sortname' => 'CO',
            'phonecode' => 57,
        ]);
    }

    /**
     * Estado para Brasil (país vecino)
     */
    public function brazil(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Brasil',
            'sortname' => 'BR',
            'phonecode' => 55,
        ]);
    }
}
