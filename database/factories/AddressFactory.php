<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\Ranch;
use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para el modelo Address
 * 
 * Genera datos de prueba para direcciones venezolanas
 * con coordenadas GPS realistas.
 */
class AddressFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     */
    public function definition(): array
    {
        return [
            'profile_id' => Profile::factory(),
            'ranch_id' => $this->faker->optional(0.3)->randomElement([null, Ranch::factory()]),
            'city_id' => City::factory(),
            // La tabla actual usa 'adressses' como texto libre
            'adressses' => $this->faker->streetAddress(),
            'latitude' => $this->faker->latitude(0, 15), // Venezuela latitude range
            'longitude' => $this->faker->longitude(-75, -60), // Venezuela longitude range
            'status' => $this->faker->randomElement(['completeData', 'incompleteData', 'notverified']),
        ];
    }

    /**
     * Estado para direcciones completas
     */
    public function complete(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completeData',
        ]);
    }

    /**
     * Estado para direcciones incompletas
     */
    public function incomplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'incompleteData',
        ]);
    }

    /**
     * Estado para direcciones no verificadas
     */
    public function notVerified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'notverified',
        ]);
    }

    /**
     * Estado para direcciones de haciendas
     */
    public function ranchAddress(): static
    {
        return $this->state(fn (array $attributes) => [
            'ranch_id' => Ranch::factory(),
            'adressses' => $this->faker->randomElement([
                'Carretera Nacional Km '.$this->faker->numberBetween(1, 100),
                'Vía Principal Km '.$this->faker->numberBetween(1, 100),
                'Camino Rural Sector '.$this->faker->numberBetween(1, 50),
                'Carretera Estatal Km '.$this->faker->numberBetween(1, 120),
                'Vía Ganadera Parcela '.$this->faker->numberBetween(1, 200)
            ]),
            'status' => 'completeData',
        ]);
    }

    /**
     * Estado para direcciones urbanas
     */
    public function urban(): static
    {
        return $this->state(fn (array $attributes) => [
            'ranch_id' => null,
            'adressses' => $this->faker->streetAddress(),
        ]);
    }

    /**
     * Estado para direcciones rurales
     */
    public function rural(): static
    {
        return $this->state(fn (array $attributes) => [
            'adressses' => $this->faker->randomElement([
                'Carretera Nacional Km '.$this->faker->numberBetween(1, 200),
                'Vía Principal Km '.$this->faker->numberBetween(1, 200),
                'Camino Rural Sector '.$this->faker->numberBetween(1, 50),
                'Carretera Estatal Km '.$this->faker->numberBetween(1, 200),
                'Vía Ganadera Parcela '.$this->faker->numberBetween(1, 200),
                'Camino Vecinal Sector '.$this->faker->numberBetween(1, 50)
            ]),
        ]);
    }

    /**
     * Estado para direcciones en Zulia (principal estado ganadero)
     */
    public function zulia(): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => $this->faker->latitude(8, 12), // Zulia latitude range
            'longitude' => $this->faker->longitude(-73, -70), // Zulia longitude range
        ]);
    }

    /**
     * Estado para direcciones en Barinas (estado ganadero)
     */
    public function barinas(): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => $this->faker->latitude(7, 9), // Barinas latitude range
            'longitude' => $this->faker->longitude(-71, -68), // Barinas longitude range
        ]);
    }

    /**
     * Estado para direcciones en Apure (estado ganadero)
     */
    public function apure(): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => $this->faker->latitude(6, 8), // Apure latitude range
            'longitude' => $this->faker->longitude(-70, -67), // Apure longitude range
        ]);
    }

    /**
     * Estado para direcciones en estados ganaderos principales
     */
    public function cattleStates(): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => $this->faker->latitude(6, 12), // Rango de estados ganaderos
            'longitude' => $this->faker->longitude(-73, -67), // Rango de estados ganaderos
        ]);
    }

    /**
     * Estado para direcciones en ciudades ganaderas principales
     */
    public function cattleCities(): static
    {
        return $this->state(fn (array $attributes) => [
            'latitude' => $this->faker->latitude(7, 11), // Rango de ciudades ganaderas
            'longitude' => $this->faker->longitude(-72, -68), // Rango de ciudades ganaderas
        ]);
    }
}
