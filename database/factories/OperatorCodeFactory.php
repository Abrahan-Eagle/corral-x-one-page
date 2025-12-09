<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para el modelo OperatorCode
 * 
 * Genera datos de prueba para códigos de operadora telefónica
 * venezolanos con información realista.
 */
class OperatorCodeFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->numerify('###'),
            'name' => $this->faker->company(),
            'is_active' => $this->faker->boolean(90), // 90% activos
        ];
    }

    /**
     * Códigos de operadora venezolanos reales
     */
    public function venezuelanOperators(): static
    {
        $venezuelanOperators = [
            ['code' => '412', 'name' => 'Digitel'],
            ['code' => '414', 'name' => 'Movistar'],
            ['code' => '416', 'name' => 'Movistar'],
            ['code' => '424', 'name' => 'Movistar'],
            ['code' => '426', 'name' => 'Movistar'],
            ['code' => '428', 'name' => 'Digitel'],
            ['code' => '430', 'name' => 'Digitel'],
            ['code' => '432', 'name' => 'Digitel'],
            ['code' => '434', 'name' => 'Digitel'],
            ['code' => '436', 'name' => 'Digitel'],
            ['code' => '438', 'name' => 'Movilnet'],
            ['code' => '440', 'name' => 'Movilnet'],
            ['code' => '442', 'name' => 'Movilnet'],
            ['code' => '444', 'name' => 'Movilnet'],
            ['code' => '446', 'name' => 'Movilnet'],
        ];

        return $this->state(function (array $attributes) use ($venezuelanOperators) {
            $operator = $this->faker->randomElement($venezuelanOperators);
            return [
                'code' => $operator['code'],
                'name' => $operator['name'],
                'is_active' => true,
            ];
        });
    }

    /**
     * Operadoras principales
     */
    public function mainOperators(): static
    {
        $mainOperators = [
            ['code' => '412', 'name' => 'Digitel'],
            ['code' => '414', 'name' => 'Movistar'],
            ['code' => '416', 'name' => 'Movistar'],
            ['code' => '424', 'name' => 'Movistar'],
            ['code' => '426', 'name' => 'Movistar'],
        ];

        return $this->state(function (array $attributes) use ($mainOperators) {
            $operator = $this->faker->randomElement($mainOperators);
            return [
                'code' => $operator['code'],
                'name' => $operator['name'],
                'is_active' => true,
            ];
        });
    }

    /**
     * Operadora inactiva
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
