<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\Ranch;
use App\Models\OperatorCode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para el modelo Phone
 * 
 * Genera datos de prueba para números de teléfono venezolanos
 * con códigos de operadora realistas.
 */
class PhoneFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     */
    public function definition(): array
    {
        return [
            'profile_id' => Profile::factory(),
            'ranch_id' => $this->faker->optional(0.2)->randomElement([null, Ranch::factory()]),
            'operator_code_id' => OperatorCode::inRandomOrder()->first() ?? OperatorCode::factory(),
            'number' => $this->faker->numerify('#######'),
            'is_primary' => $this->faker->boolean(60), // 60% son principales
            'status' => $this->faker->boolean(90), // 90% activos
        ];
    }

    /**
     * Estado para teléfonos principales
     */
    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
            'status' => true,
        ]);
    }

    /**
     * Estado para teléfonos secundarios
     */
    public function secondary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => false,
            'status' => $this->faker->boolean(85),
        ]);
    }

    /**
     * Estado para teléfonos activos
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => true,
        ]);
    }

    /**
     * Estado para teléfonos inactivos
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }

    /**
     * Estado para teléfonos de hacienda
     */
    public function ranchPhone(): static
    {
        return $this->state(fn (array $attributes) => [
            'ranch_id' => Ranch::factory(),
            'profile_id' => null,
            'is_primary' => $this->faker->boolean(70),
        ]);
    }

    /**
     * Estado para teléfonos de perfil
     */
    public function profilePhone(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_id' => Profile::factory(),
            'ranch_id' => null,
        ]);
    }

    /**
     * Estado para teléfonos con código específico
     */
    public function withOperatorCode(string $code): static
    {
        return $this->state(fn (array $attributes) => [
            'operator_code_id' => OperatorCode::where('code', $code)->first() ?? OperatorCode::factory(),
        ]);
    }

    /**
     * Estado para teléfonos Movistar
     */
    public function movistar(): static
    {
        return $this->state(fn (array $attributes) => [
            'operator_code_id' => OperatorCode::where('code', '414')->first() ?? OperatorCode::factory()->state(['code' => '414', 'name' => 'Movistar']),
        ]);
    }

    /**
     * Estado para teléfonos Digitel
     */
    public function digitel(): static
    {
        return $this->state(fn (array $attributes) => [
            'operator_code_id' => OperatorCode::where('code', '412')->first() ?? OperatorCode::factory()->state(['code' => '412', 'name' => 'Digitel']),
        ]);
    }

    /**
     * Estado para teléfonos aprobados
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => true,
        ]);
    }

    /**
     * Estado para teléfonos pendientes
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => true,
        ]);
    }
}
