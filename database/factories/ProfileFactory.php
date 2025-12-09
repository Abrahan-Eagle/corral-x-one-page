<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para el modelo Profile
 * 
 * Genera datos de prueba para perfiles de usuarios del marketplace
 * con información realista para ganaderos venezolanos.
 */
class ProfileFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'firstName' => $this->faker->firstName(),
            'middleName' => $this->faker->optional(0.3)->firstName(),
            'lastName' => $this->faker->lastName(),
            'secondLastName' => $this->faker->optional(0.4)->lastName(),
            'photo_users' => $this->faker->optional(0.6)->randomElement([
                'https://i.pravatar.cc/300?img=1',
                'https://i.pravatar.cc/300?img=2',
                'https://i.pravatar.cc/300?img=3',
                'https://i.pravatar.cc/300?img=4',
                'https://i.pravatar.cc/300?img=5',
                'https://i.pravatar.cc/300?img=6',
                'https://i.pravatar.cc/300?img=7',
                'https://i.pravatar.cc/300?img=8',
                'https://i.pravatar.cc/300?img=9',
                'https://i.pravatar.cc/300?img=10',
                'https://i.pravatar.cc/300?img=11',
                'https://i.pravatar.cc/300?img=12',
            ]),
            'date_of_birth' => $this->faker->dateTimeBetween('-70 years', '-18 years'),
            'maritalStatus' => $this->faker->randomElement(['married', 'divorced', 'single', 'widowed']),
            'sex' => $this->faker->randomElement(['F', 'M', 'O']),
            'status' => $this->faker->randomElement(['completeData', 'incompleteData', 'notverified']),
            'is_verified' => $this->faker->boolean(60), // 60% verificados
            'rating' => $this->faker->randomFloat(2, 0, 5),
            'ratings_count' => $this->faker->numberBetween(0, 50),
            'has_unread_messages' => $this->faker->boolean(20), // 20% con mensajes no leídos
            'user_type' => $this->faker->randomElement(['buyer', 'seller', 'both']),
            'is_both_verified' => $this->faker->boolean(30),
            'accepts_calls' => $this->faker->boolean(80),
            'accepts_whatsapp' => $this->faker->boolean(90),
            'accepts_emails' => $this->faker->boolean(85),
            'whatsapp_number' => $this->faker->optional(0.8)->numerify('+58412#######'),
            // 'preferred_communication_hours' removido - ahora se maneja en ranches.contact_hours
            'is_premium_seller' => $this->faker->boolean(15), // 15% premium
            'premium_expires_at' => $this->faker->optional(0.15)->dateTimeBetween('now', '+1 year'),
            // CI venezolana básica para MVP (V-12345678)
            'ci_number' => $this->faker->unique()->numerify('V-########'),
            // Campos KYC básicos (por defecto no verificado)
            'kyc_status' => 'no_verified',
            'kyc_rejection_reason' => null,
            'kyc_document_type' => 'ci_ve',
            'kyc_document_number' => null,
            'kyc_country_code' => 'VE',
            'kyc_doc_front_path' => null,
            'kyc_rif_path' => null,
            'kyc_selfie_path' => null,
            'kyc_selfie_with_doc_path' => null,
            'kyc_verified_at' => null,
        ];
    }

    /**
     * Estado para vendedores verificados
     */
    public function verifiedSeller(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'seller',
            'is_verified' => true,
            'status' => 'completeData',
            'is_both_verified' => true,
        ]);
    }

    /**
     * Estado para compradores verificados
     */
    public function verifiedBuyer(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'buyer',
            'is_verified' => true,
            'status' => 'completeData',
            'is_both_verified' => true,
        ]);
    }

    /**
     * Estado para usuarios mixtos (compradores y vendedores)
     */
    public function both(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'both',
            'is_both_verified' => $this->faker->boolean(60),
        ]);
    }

    /**
     * Estado para usuarios premium
     */
    public function premium(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_premium_seller' => true,
            'premium_expires_at' => $this->faker->dateTimeBetween('now', '+1 year'),
            'is_verified' => true,
            'is_both_verified' => true,
        ]);
    }

    /**
     * Estado para usuarios con datos completos
     */
    public function completeData(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completeData',
            'is_verified' => true,
            'whatsapp_number' => $this->faker->numerify('+58412#######'),
        ]);
    }

    /**
     * Estado para usuarios con datos incompletos
     */
    public function incompleteData(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'incompleteData',
            'is_verified' => false,
            'whatsapp_number' => null,
        ]);
    }

    /**
     * Estado para usuarios no verificados
     */
    public function notVerified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'notverified',
            'is_verified' => false,
            'is_both_verified' => false,
        ]);
    }

    /**
     * Estado para ganaderos experimentados
     */
    public function experienced(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => true,
            'rating' => $this->faker->randomFloat(2, 3.5, 5),
            'ratings_count' => $this->faker->numberBetween(10, 100),
        ]);
    }

    /**
     * Estado para usuarios nuevos
     */
    public function newUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => false,
            'rating' => 0,
            'ratings_count' => 0,
            'status' => 'notverified',
        ]);
    }

    /**
     * Estado para perfiles venezolanos (nombres típicos)
     */
    public function venezuelan(): static
    {
        $venezuelanFirstNames = [
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
            'firstName' => $this->faker->randomElement($venezuelanFirstNames),
            'middleName' => $this->faker->optional(0.3)->randomElement($venezuelanFirstNames),
            'lastName' => $this->faker->randomElement($venezuelanLastNames),
            'secondLastName' => $this->faker->optional(0.4)->randomElement($venezuelanLastNames),
        ]);
    }

    /**
     * Estado para administradores
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_type' => 'both',
            'is_verified' => true,
            'is_both_verified' => true,
        ]);
    }
}