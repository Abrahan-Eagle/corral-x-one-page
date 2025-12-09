<?php

namespace Database\Factories;

use App\Models\Ranch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para el modelo Product
 * 
 * Genera datos de prueba para productos de ganado venezolanos
 * con información realista y específica del mercado ganadero.
 */
class ProductFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     */
    public function definition(): array
    {
        return [
            'ranch_id' => Ranch::factory(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraphs(3, true),
            'type' => $this->faker->randomElement(['engorde', 'lechero', 'padrote', 'equipment', 'feed', 'other']),
            'breed' => $this->faker->randomElement([
                'Brahman', 'Holstein', 'Guzerat', 'Gyr', 'Nelore', 'Jersey', 'Angus', 'Simmental',
                'Pardo Suizo', 'Charolais', 'Limousin', 'Santa Gertrudis', 'Brangus', 'Girolando',
                'Carora', 'Criollo Limonero', 'Mosaico Perijanero', 'Indubrasil', 'Sardo Negro',
                'Senepol', 'Romosinuano', 'Sahiwal', 'Búfalo Murrah', 'Búfalo Jafarabadi',
                'Búfalo Mediterráneo', 'Búfalo Carabao', 'Búfalo Nili-Ravi', 'Búfalo Surti',
                'Búfalo Pandharpuri', 'Búfalo Nagpuri', 'Búfalo Mehsana', 'Búfalo Bhadawari',
                'Búfalo Toda', 'Búfalo Kundi', 'Búfalo Nili', 'Búfalo Ravi', 'Otra'
            ]),
            'age' => $this->faker->optional(0.8)->numberBetween(6, 120), // 6 meses a 10 años
            'quantity' => $this->faker->numberBetween(1, 100),
            'price' => $this->faker->randomFloat(2, 500, 50000),
            'currency' => $this->faker->randomElement(['USD', 'VES']),
            'status' => $this->faker->randomElement(['active', 'paused', 'sold', 'expired']),
            'weight_avg' => $this->faker->optional(0.7)->randomFloat(2, 200, 800),
            'weight_min' => $this->faker->optional(0.6)->randomFloat(2, 150, 600),
            'weight_max' => $this->faker->optional(0.6)->randomFloat(2, 300, 1000),
            'sex' => $this->faker->randomElement(['male', 'female', 'mixed']),
            'purpose' => $this->faker->optional(0.7)->randomElement(['breeding', 'meat', 'dairy', 'mixed']),
            'feeding_type' => $this->faker->randomElement(['pastura_natural', 'pasto_corte', 'concentrado', 'mixto', 'otro']), // ✅ NUEVO: tipo de alimento
            'health_certificate_url' => $this->faker->optional(0.4)->url(),
            'vaccines_applied' => $this->faker->optional(0.6)->randomElements([
                'Vacuna contra brucelosis',
                'Vacuna contra tuberculosis',
                'Vacuna contra fiebre aftosa',
                'Vacuna contra carbunco',
                'Vacuna contra rabia',
                'Vacuna contra leptospirosis'
            ], $this->faker->numberBetween(1, 4)) ? json_encode($this->faker->randomElements([
                'Vacuna contra brucelosis',
                'Vacuna contra tuberculosis',
                'Vacuna contra fiebre aftosa',
                'Vacuna contra carbunco',
                'Vacuna contra rabia',
                'Vacuna contra leptospirosis'
            ], $this->faker->numberBetween(1, 4))) : null,
            'last_vaccination' => $this->faker->optional(0.6)->dateTimeBetween('-1 year', 'now'),
            'is_vaccinated' => $this->faker->boolean(70),
            'feeding_info' => $this->faker->optional(0.5)->paragraph(2),
            'handling_info' => $this->faker->optional(0.4)->paragraph(2),
            'origin_farm' => $this->faker->optional(0.3)->company() . ' Ranch',
            'available_from' => $this->faker->optional(0.7)->dateTimeBetween('-1 month', '+1 month'),
            'available_until' => $this->faker->optional(0.5)->dateTimeBetween('+1 month', '+6 months'),
            'delivery_method' => $this->faker->randomElement(['pickup', 'delivery', 'both']),
            'delivery_cost' => $this->faker->optional(0.4)->randomFloat(2, 50, 2000),
            'delivery_radius_km' => $this->faker->optional(0.5)->numberBetween(50, 500),
            'price_type' => $this->faker->randomElement(['per_unit', 'per_lot', 'per_kg']),
            'negotiable' => $this->faker->boolean(80),
            'min_order_quantity' => $this->faker->optional(0.3)->numberBetween(1, 10),
            'is_featured' => $this->faker->boolean(15), // 15% destacados
            'views' => $this->faker->numberBetween(0, 500),
            'transportation_included' => $this->faker->randomElement(['yes', 'no', 'negotiable']),
            'documentation_included' => $this->faker->optional(0.6)->randomElements([
                'Certificado sanitario',
                'Registro de vacunación',
                'Certificado de origen',
                'Registro genealógico',
                'Certificado de salud'
            ], $this->faker->numberBetween(1, 3)) ? json_encode($this->faker->randomElements([
                'Certificado sanitario',
                'Registro de vacunación',
                'Certificado de origen',
                'Registro genealógico',
                'Certificado de salud'
            ], $this->faker->numberBetween(1, 3))) : null,
            'genetic_tests_available' => $this->faker->boolean(20),
            'genetic_test_results' => $this->faker->optional(0.2)->randomElements([
                'ADN paterno confirmado',
                'Prueba de paternidad',
                'Análisis genético completo',
                'Certificado de pureza racial'
            ], $this->faker->numberBetween(1, 2)) ? json_encode($this->faker->randomElements([
                'ADN paterno confirmado',
                'Prueba de paternidad',
                'Análisis genético completo',
                'Certificado de pureza racial'
            ], $this->faker->numberBetween(1, 2))) : null,
            'bloodline' => $this->faker->optional(0.3)->randomElement([
                'Línea materna reconocida',
                'Línea paterna certificada',
                'Línea genética premium',
                'Línea de alta producción'
            ]),
        ];
    }

    /**
     * Estado para productos activos
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'available_from' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'available_until' => $this->faker->dateTimeBetween('+1 month', '+6 months'),
        ]);
    }

    /**
     * Estado para productos vendidos
     */
    public function sold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sold',
            'available_until' => $this->faker->dateTimeBetween('-3 months', '-1 week'),
        ]);
    }

    /**
     * Estado para productos destacados
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'status' => 'active',
            'views' => $this->faker->numberBetween(100, 1000),
        ]);
    }

    /**
     * Estado para productos de engorde
     */
    public function fattening(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'engorde',
            'breed' => $this->faker->randomElement(['Brahman', 'Nelore', 'Angus', 'Charolais', 'Limousin']),
            'sex' => $this->faker->randomElement(['male', 'female']),
            'purpose' => 'meat',
            'age' => $this->faker->numberBetween(12, 36), // 1-3 años
            'weight_avg' => $this->faker->randomFloat(2, 300, 600),
            'title' => $this->faker->randomElement([
                'Novillos para engorde',
                'Terneros de engorde',
                'Ganado de engorde',
                'Novillos Brahman',
                'Terneros Nelore'
            ]),
        ]);
    }

    /**
     * Estado para productos lecheros
     */
    public function dairy(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'lechero',
            'breed' => $this->faker->randomElement(['Holstein', 'Jersey', 'Pardo Suizo', 'Girolando']),
            'sex' => 'female',
            'purpose' => 'dairy',
            'age' => $this->faker->numberBetween(24, 120), // 2-10 años
            'weight_avg' => $this->faker->randomFloat(2, 400, 700),
            'title' => $this->faker->randomElement([
                'Vacas lecheras Holstein',
                'Vacas Jersey productoras',
                'Vacas lecheras Pardo Suizo',
                'Vacas Girolando',
                'Vacas de alta producción'
            ]),
        ]);
    }

    /**
     * Estado para productos reproductores
     */
    public function breeding(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'padrote',
            'breed' => $this->faker->randomElement(['Brahman', 'Nelore', 'Angus', 'Simmental', 'Charolais']),
            'sex' => 'male',
            'purpose' => 'breeding',
            'age' => $this->faker->numberBetween(36, 120), // 3-10 años
            'weight_avg' => $this->faker->randomFloat(2, 500, 900),
            'genetic_tests_available' => true,
            'genetic_test_results' => json_encode($this->faker->randomElements([
                'ADN paterno confirmado',
                'Prueba de paternidad',
                'Análisis genético completo',
                'Certificado de pureza racial'
            ], $this->faker->numberBetween(2, 4))),
            'title' => $this->faker->randomElement([
                'Toro reproductor Brahman',
                'Toro Nelore certificado',
                'Toro Angus de cría',
                'Toro Simmental premium',
                'Toro Charolais certificado'
            ]),
        ]);
    }

    /**
     * Estado para productos vacunados
     */
    public function vaccinated(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_vaccinated' => true,
            'vaccines_applied' => json_encode($this->faker->randomElements([
                'Vacuna contra brucelosis',
                'Vacuna contra tuberculosis',
                'Vacuna contra fiebre aftosa',
                'Vacuna contra carbunco',
                'Vacuna contra rabia',
                'Vacuna contra leptospirosis'
            ], $this->faker->numberBetween(3, 6))),
            'last_vaccination' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'health_certificate_url' => $this->faker->url(),
        ]);
    }

    /**
     * Estado para productos con entrega
     */
    public function withDelivery(): static
    {
        return $this->state(fn (array $attributes) => [
            'delivery_method' => $this->faker->randomElement(['delivery', 'both']),
            'delivery_cost' => $this->faker->randomFloat(2, 100, 1500),
            'delivery_radius_km' => $this->faker->numberBetween(100, 300),
            'transportation_included' => $this->faker->randomElement(['yes', 'negotiable']),
        ]);
    }

    /**
     * Estado para productos premium
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
            'negotiable' => false,
            'genetic_tests_available' => true,
            'documentation_included' => json_encode([
                'Certificado sanitario',
                'Registro de vacunación',
                'Certificado de origen',
                'Registro genealógico',
                'Certificado de salud'
            ]),
            'price' => $this->faker->randomFloat(2, 10000, 50000),
            'currency' => 'USD',
        ]);
    }

    /**
     * Estado para productos económicos
     */
    public function budget(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => $this->faker->randomFloat(2, 500, 3000),
            'currency' => 'VES',
            'negotiable' => true,
            'delivery_method' => 'pickup',
            'transportation_included' => 'no',
        ]);
    }
}