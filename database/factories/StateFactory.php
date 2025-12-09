<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para el modelo State
 * 
 * Genera datos de prueba para estados/provincias venezolanos
 * con información realista para el marketplace de ganado.
 */
class StateFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     */
    public function definition(): array
    {
        return [
            'countries_id' => Country::factory(),
            'name' => $this->faker->state(),
        ];
    }

    /**
     * Estados venezolanos principales para ganado
     */
    public function venezuelanStates(): static
    {
        $venezuelanStates = [
            'Zulia', 'Barinas', 'Apure', 'Cojedes', 'Portuguesa', 'Lara',
            'Yaracuy', 'Falcón', 'Carabobo', 'Aragua', 'Miranda', 'Distrito Capital',
            'Vargas', 'Guárico', 'Anzoátegui', 'Monagas', 'Sucre', 'Delta Amacuro',
            'Bolívar', 'Amazonas', 'Táchira', 'Mérida', 'Trujillo', 'Nueva Esparta',
        ];

        return $this->state(function (array $attributes) use ($venezuelanStates) {
            return [
                'name' => $this->faker->randomElement($venezuelanStates),
            ];
        });
    }

    /**
     * Estados principales para ganado (mayor producción)
     */
    public function cattleStates(): static
    {
        $cattleStates = [
            'Zulia', 'Barinas', 'Apure', 'Cojedes', 'Portuguesa', 'Guárico',
        ];

        return $this->state(function (array $attributes) use ($cattleStates) {
            return [
                'name' => $this->faker->randomElement($cattleStates),
            ];
        });
    }
}
