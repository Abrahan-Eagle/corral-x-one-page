<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\Product;
use App\Models\Ranch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para el modelo Report
 * 
 * Genera datos de prueba para reportes de moderación
 * en el marketplace de ganado.
 */
class ReportFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     */
    public function definition(): array
    {
        return [
            'reporter_id' => Profile::factory(),
            'reportable_type' => $this->faker->randomElement(['App\\Models\\Product', 'App\\Models\\Profile', 'App\\Models\\Ranch']),
            'reportable_id' => $this->faker->numberBetween(1, 100),
            'report_type' => $this->faker->randomElement(['spam', 'inappropriate', 'fraud', 'fake_product', 'harassment', 'other']),
            'description' => $this->faker->optional(0.8)->paragraph(2),
            'status' => $this->faker->randomElement(['pending', 'reviewing', 'resolved', 'dismissed']),
            'admin_id' => $this->faker->optional(0.6)->randomElement([null, Profile::factory()]),
            'admin_notes' => $this->faker->optional(0.4)->paragraph(1),
            'resolved_at' => $this->faker->optional(0.5)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Estado para reportes pendientes
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'admin_id' => null,
            'admin_notes' => null,
            'resolved_at' => null,
        ]);
    }

    /**
     * Estado para reportes en revisión
     */
    public function reviewing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'reviewing',
            'admin_id' => Profile::factory()->admin(),
            'admin_notes' => null,
            'resolved_at' => null,
        ]);
    }

    /**
     * Estado para reportes resueltos
     */
    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'admin_id' => Profile::factory()->admin(),
            'admin_notes' => $this->faker->randomElement([
                'Reporte válido, se tomaron las medidas correspondientes',
                'Contenido inapropiado eliminado',
                'Usuario sancionado según políticas',
                'Producto falso removido del marketplace',
                'Se contactó al usuario para aclarar la situación'
            ]),
            'resolved_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Estado para reportes desestimados
     */
    public function dismissed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'dismissed',
            'admin_id' => Profile::factory()->admin(),
            'admin_notes' => $this->faker->randomElement([
                'Reporte infundado, no se encontraron violaciones',
                'Contenido cumple con las políticas del marketplace',
                'Usuario no violó las reglas establecidas',
                'Reporte malicioso, se archivó sin acción',
                'No se encontraron evidencias de la violación reportada'
            ]),
            'resolved_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Estado para reportes de spam
     */
    public function spam(): static
    {
        return $this->state(fn (array $attributes) => [
            'report_type' => 'spam',
            'description' => $this->faker->randomElement([
                'Usuario publicando contenido repetitivo',
                'Mensajes spam en conversaciones',
                'Publicaciones duplicadas',
                'Contenido promocional no autorizado',
                'Usuario enviando mensajes masivos'
            ]),
        ]);
    }

    /**
     * Estado para reportes de contenido inapropiado
     */
    public function inappropriate(): static
    {
        return $this->state(fn (array $attributes) => [
            'report_type' => 'inappropriate',
            'description' => $this->faker->randomElement([
                'Contenido ofensivo en la descripción',
                'Imágenes inapropiadas del producto',
                'Lenguaje ofensivo en mensajes',
                'Contenido sexualmente explícito',
                'Contenido discriminatorio'
            ]),
        ]);
    }

    /**
     * Estado para reportes de fraude
     */
    public function fraud(): static
    {
        return $this->state(fn (array $attributes) => [
            'report_type' => 'fraud',
            'description' => $this->faker->randomElement([
                'Usuario intentando estafar compradores',
                'Precios sospechosamente bajos',
                'Información falsa sobre el producto',
                'Usuario no entregó el producto pagado',
                'Documentos falsificados'
            ]),
        ]);
    }

    /**
     * Estado para reportes de productos falsos
     */
    public function fakeProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'report_type' => 'fake_product',
            'description' => $this->faker->randomElement([
                'Producto no existe o no está disponible',
                'Imágenes falsas del ganado',
                'Información incorrecta sobre la raza',
                'Precio no corresponde al producto',
                'Producto no cumple con la descripción'
            ]),
        ]);
    }

    /**
     * Estado para reportes de acoso
     */
    public function harassment(): static
    {
        return $this->state(fn (array $attributes) => [
            'report_type' => 'harassment',
            'description' => $this->faker->randomElement([
                'Usuario enviando mensajes amenazantes',
                'Acoso constante en conversaciones',
                'Comportamiento intimidatorio',
                'Mensajes ofensivos repetitivos',
                'Usuario persiguiendo a otros usuarios'
            ]),
        ]);
    }

    /**
     * Estado para reportes de otros tipos
     */
    public function other(): static
    {
        return $this->state(fn (array $attributes) => [
            'report_type' => 'other',
            'description' => $this->faker->randomElement([
                'Problema técnico con la plataforma',
                'Error en la información del producto',
                'Problema con el sistema de pagos',
                'Usuario no responde a mensajes',
                'Problema con la entrega del producto'
            ]),
        ]);
    }

    /**
     * Estado para reportes sobre productos
     */
    public function aboutProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'reportable_type' => 'App\\Models\\Product',
            'reportable_id' => Product::factory(),
        ]);
    }

    /**
     * Estado para reportes sobre perfiles
     */
    public function aboutProfile(): static
    {
        return $this->state(fn (array $attributes) => [
            'reportable_type' => 'App\\Models\\Profile',
            'reportable_id' => Profile::factory(),
        ]);
    }

    /**
     * Estado para reportes sobre haciendas
     */
    public function aboutRanch(): static
    {
        return $this->state(fn (array $attributes) => [
            'reportable_type' => 'App\\Models\\Ranch',
            'reportable_id' => Ranch::factory(),
        ]);
    }

    /**
     * Estado para reportes recientes
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Estado para reportes antiguos
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-6 months', '-1 month'),
            'updated_at' => $this->faker->dateTimeBetween('-6 months', '-1 month'),
        ]);
    }

    /**
     * Estado para reportes con descripción
     */
    public function withDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => $this->faker->paragraph(2),
        ]);
    }

    /**
     * Estado para reportes sin descripción
     */
    public function withoutDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => null,
        ]);
    }

    /**
     * Estado para reportes de usuarios verificados
     */
    public function fromVerifiedUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'reporter_id' => Profile::factory()->verifiedSeller(),
        ]);
    }

    /**
     * Estado para reportes de usuarios nuevos
     */
    public function fromNewUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'reporter_id' => Profile::factory()->newUser(),
        ]);
    }

    /**
     * Estado para reportes de usuarios experimentados
     */
    public function fromExperiencedUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'reporter_id' => Profile::factory()->experienced(),
        ]);
    }
}
