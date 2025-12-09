<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Factory para el modelo User
 * 
 * Genera datos de prueba para usuarios con autenticación
 * básica y Google OAuth para el marketplace de ganado.
 */
class UserFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            // Evitar fechas problemáticas por husos/DST en producción: usar now() o null
            'email_verified_at' => $this->faker->boolean(80) ? now() : null,
            'password' => Hash::make('password'), // Password por defecto para testing
            'google_id' => $this->faker->optional(0.3)->numerify('##################'), // 30% con Google
            'given_name' => $this->faker->optional(0.3)->firstName(),
            'family_name' => $this->faker->optional(0.3)->lastName(),
            'profile_pic' => $this->faker->optional(0.4)->imageUrl(200, 200, 'people'),
            'AccessToken' => $this->faker->optional(0.3)->sha256(),
            'completed_onboarding' => $this->faker->boolean(85), // 85% completaron onboarding
            'role' => $this->faker->randomElement(['users', 'admin']),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Estado para usuarios administradores
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'email_verified_at' => now(),
            'completed_onboarding' => true,
        ]);
    }

    /**
     * Estado para usuarios regulares
     */
    public function user(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'users',
        ]);
    }

    /**
     * Estado para usuarios con Google OAuth
     */
    public function googleUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'google_id' => $this->faker->numerify('##################'),
            'given_name' => $this->faker->firstName(),
            'family_name' => $this->faker->lastName(),
            'profile_pic' => $this->faker->imageUrl(200, 200, 'people'),
            'AccessToken' => $this->faker->sha256(),
            'password' => null, // Sin password si usa Google
        ]);
    }

    /**
     * Estado para usuarios sin verificar email
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Estado para usuarios que no completaron onboarding
     */
    public function incompleteOnboarding(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed_onboarding' => false,
        ]);
    }

    /**
     * Estado para usuarios con onboarding completado
     */
    public function completedOnboarding(): static
    {
        return $this->state(fn (array $attributes) => [
            'completed_onboarding' => true,
        ]);
    }

    /**
     * Estado para usuarios venezolanos (nombres típicos)
     */
    public function venezuelan(): static
    {
        $venezuelanNames = [
            'José', 'María', 'Carlos', 'Ana', 'Luis', 'Carmen', 'Pedro', 'Rosa',
            'Juan', 'Isabel', 'Miguel', 'Elena', 'Antonio', 'Patricia', 'Francisco', 'Mónica',
            'Manuel', 'Sandra', 'David', 'Laura', 'Roberto', 'Andrea', 'Jorge', 'Natalia',
            'Fernando', 'Gabriela', 'Ricardo', 'Valentina', 'Alejandro', 'Camila', 'Daniel', 'Sofia',
        ];

        $venezuelanLastNames = [
            'González', 'Rodríguez', 'García', 'Martínez', 'López', 'Hernández', 'Pérez', 'Sánchez',
            'Ramírez', 'Cruz', 'Flores', 'Gómez', 'Díaz', 'Reyes', 'Morales', 'Jiménez',
            'Álvarez', 'Ruiz', 'Herrera', 'Medina', 'Aguilar', 'Vargas', 'Castillo', 'Ramos',
            'Romero', 'Torres', 'Gutiérrez', 'Mendoza', 'Silva', 'Vega', 'Rojas', 'Molina',
        ];

        return $this->state(fn (array $attributes) => [
            'name' => $this->faker->randomElement($venezuelanNames) . ' ' . 
                     $this->faker->randomElement($venezuelanLastNames),
            'given_name' => $this->faker->randomElement($venezuelanNames),
            'family_name' => $this->faker->randomElement($venezuelanLastNames),
        ]);
    }
}