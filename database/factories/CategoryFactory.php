<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para el modelo Category
 * 
 * Genera datos de prueba para categorías de productos
 * específicas del mercado ganadero venezolano.
 */
class CategoryFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true),
            'slug' => $this->faker->slug(2),
            'description' => $this->faker->optional(0.8)->paragraph(2),
            'parent_id' => $this->faker->optional(0.3)->randomElement([null, Category::factory()]),
            'sort_order' => $this->faker->numberBetween(0, 100),
            'is_active' => $this->faker->boolean(90), // 90% activas
            'icon' => $this->faker->optional(0.6)->randomElement([
                'cow', 'bull', 'calf', 'milk', 'meat', 'tractor', 'feed', 'medicine',
                'equipment', 'tools', 'building', 'fence', 'water', 'grass'
            ]),
            'color' => $this->faker->optional(0.5)->hexColor(),
        ];
    }

    /**
     * Estado para categorías principales
     */
    public function parent(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => null,
            'sort_order' => $this->faker->numberBetween(0, 10),
            'is_active' => true,
        ]);
    }

    /**
     * Estado para subcategorías
     */
    public function child(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => Category::factory()->parent(),
            'sort_order' => $this->faker->numberBetween(0, 50),
        ]);
    }

    /**
     * Estado para categorías inactivas
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Estado para categorías de ganado bovino
     */
    public function cattle(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Ganado Bovino',
            'slug' => 'ganado-bovino',
            'description' => 'Categoría principal para todo tipo de ganado bovino',
            'parent_id' => null,
            'icon' => 'cow',
            'color' => '#8B4513',
            'is_active' => true,
        ]);
    }

    /**
     * Estado para categorías de equipos
     */
    public function equipment(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Equipos Ganaderos',
            'slug' => 'equipos-ganaderos',
            'description' => 'Equipos y maquinaria para la actividad ganadera',
            'parent_id' => null,
            'icon' => 'tractor',
            'color' => '#228B22',
            'is_active' => true,
        ]);
    }

    /**
     * Estado para categorías de alimentos
     */
    public function feed(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Alimentos y Suplementos',
            'slug' => 'alimentos-suplementos',
            'description' => 'Alimentos, suplementos y medicamentos para ganado',
            'parent_id' => null,
            'icon' => 'feed',
            'color' => '#FFD700',
            'is_active' => true,
        ]);
    }

    /**
     * Estado para subcategorías de ganado por tipo
     */
    public function cattleByType(): static
    {
        $cattleTypes = [
            ['name' => 'Novillos', 'slug' => 'novillos', 'icon' => 'bull'],
            ['name' => 'Vacas', 'slug' => 'vacas', 'icon' => 'cow'],
            ['name' => 'Terneros', 'slug' => 'terneros', 'icon' => 'calf'],
            ['name' => 'Toros', 'slug' => 'toros', 'icon' => 'bull'],
            ['name' => 'Vaquillonas', 'slug' => 'vaquillonas', 'icon' => 'cow'],
        ];

        return $this->state(function (array $attributes) use ($cattleTypes) {
            $type = $this->faker->randomElement($cattleTypes);
            return [
                'name' => $type['name'],
                'slug' => $type['slug'],
                'description' => "Ganado bovino tipo {$type['name']}",
                'parent_id' => Category::factory()->cattle(),
                'icon' => $type['icon'],
                'is_active' => true,
            ];
        });
    }

    /**
     * Estado para subcategorías de equipos
     */
    public function equipmentByType(): static
    {
        $equipmentTypes = [
            ['name' => 'Tractores', 'slug' => 'tractores', 'icon' => 'tractor'],
            ['name' => 'Ordeñadoras', 'slug' => 'ordenadoras', 'icon' => 'milk'],
            ['name' => 'Bebederos', 'slug' => 'bebederos', 'icon' => 'water'],
            ['name' => 'Comederos', 'slug' => 'comederos', 'icon' => 'feed'],
            ['name' => 'Cercas', 'slug' => 'cercas', 'icon' => 'fence'],
        ];

        return $this->state(function (array $attributes) use ($equipmentTypes) {
            $type = $this->faker->randomElement($equipmentTypes);
            return [
                'name' => $type['name'],
                'slug' => $type['slug'],
                'description' => "Equipos tipo {$type['name']} para ganadería",
                'parent_id' => Category::factory()->equipment(),
                'icon' => $type['icon'],
                'is_active' => true,
            ];
        });
    }

    /**
     * Estado para subcategorías de alimentos
     */
    public function feedByType(): static
    {
        $feedTypes = [
            ['name' => 'Concentrados', 'slug' => 'concentrados', 'icon' => 'feed'],
            ['name' => 'Forrajes', 'slug' => 'forrajes', 'icon' => 'grass'],
            ['name' => 'Medicamentos', 'slug' => 'medicamentos', 'icon' => 'medicine'],
            ['name' => 'Vitaminas', 'slug' => 'vitaminas', 'icon' => 'medicine'],
            ['name' => 'Minerales', 'slug' => 'minerales', 'icon' => 'medicine'],
        ];

        return $this->state(function (array $attributes) use ($feedTypes) {
            $type = $this->faker->randomElement($feedTypes);
            return [
                'name' => $type['name'],
                'slug' => $type['slug'],
                'description' => "Alimentos y suplementos tipo {$type['name']}",
                'parent_id' => Category::factory()->feed(),
                'icon' => $type['icon'],
                'is_active' => true,
            ];
        });
    }
}
