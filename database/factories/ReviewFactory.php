<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Profile;
use App\Models\Product;
use App\Models\Ranch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para el modelo Review
 * 
 * Genera datos de prueba para reseñas y calificaciones
 * de productos y haciendas en el marketplace de ganado.
 */
class ReviewFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     */
    public function definition(): array
    {
        $order = Order::factory()->create();

        return [
            'order_id' => $order->id,
            'profile_id' => $order->buyer_profile_id ?? Profile::factory(),
            'product_id' => $order->product_id ?? Product::factory(),
            'ranch_id' => $order->ranch_id ?? Ranch::factory(),
            'rating' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->optional(0.8)->paragraphs(2, true),
            'is_verified_purchase' => $this->faker->boolean(60), // 60% compras verificadas
            'is_approved' => $this->faker->boolean(85), // 85% aprobadas
        ];
    }

    /**
     * Estado para reseñas aprobadas
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => true,
        ]);
    }

    /**
     * Estado para reseñas pendientes
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => false,
        ]);
    }

    /**
     * Estado para reseñas de compras verificadas
     */
    public function verifiedPurchase(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified_purchase' => true,
            'is_approved' => true,
        ]);
    }

    /**
     * Estado para reseñas excelentes (5 estrellas)
     */
    public function excellent(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 5,
            'comment' => $this->faker->randomElement([
                'Excelente producto, muy recomendado',
                'Ganado de primera calidad',
                'Vendedor muy profesional y confiable',
                'Producto superó mis expectativas',
                'Excelente servicio y atención',
                'Ganado saludable y bien cuidado',
                'Transacción muy satisfactoria',
                'Producto de alta calidad'
            ]),
            'is_approved' => true,
        ]);
    }

    /**
     * Estado para reseñas muy buenas (4 estrellas)
     */
    public function veryGood(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 4,
            'comment' => $this->faker->randomElement([
                'Muy buen producto, recomendado',
                'Ganado de buena calidad',
                'Vendedor confiable',
                'Producto cumple con lo esperado',
                'Buen servicio y atención',
                'Ganado saludable',
                'Transacción satisfactoria',
                'Producto de buena calidad'
            ]),
            'is_approved' => true,
        ]);
    }

    /**
     * Estado para reseñas buenas (3 estrellas)
     */
    public function good(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 3,
            'comment' => $this->faker->randomElement([
                'Producto aceptable',
                'Ganado en buen estado',
                'Vendedor correcto',
                'Producto cumple básicamente',
                'Servicio regular',
                'Ganado saludable',
                'Transacción normal',
                'Producto de calidad regular'
            ]),
            'is_approved' => true,
        ]);
    }

    /**
     * Estado para reseñas regulares (2 estrellas)
     */
    public function regular(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 2,
            'comment' => $this->faker->randomElement([
                'Producto regular',
                'Ganado en estado regular',
                'Vendedor con algunas fallas',
                'Producto no cumple completamente',
                'Servicio con deficiencias',
                'Ganado con algunos problemas',
                'Transacción con inconvenientes',
                'Producto de calidad regular'
            ]),
            'is_approved' => true,
        ]);
    }

    /**
     * Estado para reseñas malas (1 estrella)
     */
    public function bad(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => 1,
            'comment' => $this->faker->randomElement([
                'Producto de mala calidad',
                'Ganado en mal estado',
                'Vendedor poco confiable',
                'Producto no cumple con lo prometido',
                'Servicio deficiente',
                'Ganado con problemas de salud',
                'Transacción problemática',
                'Producto de baja calidad'
            ]),
            'is_approved' => true,
        ]);
    }

    /**
     * Estado para reseñas con comentarios largos
     */
    public function longComment(): static
    {
        return $this->state(fn (array $attributes) => [
            'comment' => $this->faker->paragraphs(4, true),
            'is_approved' => true,
        ]);
    }

    /**
     * Estado para reseñas sin comentarios
     */
    public function noComment(): static
    {
        return $this->state(fn (array $attributes) => [
            'comment' => null,
            'is_approved' => true,
        ]);
    }

    /**
     * Estado para reseñas recientes
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Estado para reseñas antiguas
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 year', '-3 months'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', '-3 months'),
        ]);
    }

    /**
     * Estado para reseñas de productos de engorde
     */
    public function fatteningProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::factory()->fattening(),
            'comment' => $this->faker->randomElement([
                'Excelentes novillos para engorde',
                'Ganado ideal para engorde',
                'Novillos de buena calidad',
                'Perfectos para engorde',
                'Ganado engordado correctamente'
            ]),
        ]);
    }

    /**
     * Estado para reseñas de productos lecheros
     */
    public function dairyProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::factory()->dairy(),
            'comment' => $this->faker->randomElement([
                'Excelentes vacas lecheras',
                'Alta producción de leche',
                'Vacas muy productivas',
                'Ganado lechero de calidad',
                'Excelente producción láctea'
            ]),
        ]);
    }

    /**
     * Estado para reseñas de productos reproductores
     */
    public function breedingProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::factory()->breeding(),
            'comment' => $this->faker->randomElement([
                'Excelente toro reproductor',
                'Genética de primera calidad',
                'Toro certificado y confiable',
                'Excelente para reproducción',
                'Genética superior'
            ]),
        ]);
    }

    /**
     * Estado para reseñas de productos vacunados
     */
    public function vaccinatedProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::factory()->vaccinated(),
            'comment' => $this->faker->randomElement([
                'Ganado completamente vacunado',
                'Excelente estado sanitario',
                'Ganado saludable y vacunado',
                'Certificados sanitarios en orden',
                'Ganado con todas las vacunas'
            ]),
        ]);
    }

    /**
     * Estado para reseñas de productos premium
     */
    public function premiumProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::factory()->premium(),
            'rating' => $this->faker->numberBetween(4, 5),
            'comment' => $this->faker->randomElement([
                'Producto premium de excelente calidad',
                'Ganado de élite',
                'Calidad superior certificada',
                'Producto de alta gama',
                'Excelente inversión'
            ]),
            'is_verified_purchase' => true,
            'is_approved' => true,
        ]);
    }

    /**
     * Estado para reseñas de productos económicos
     */
    public function budgetProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => Product::factory()->budget(),
            'rating' => $this->faker->numberBetween(2, 4),
            'comment' => $this->faker->randomElement([
                'Buen producto por el precio',
                'Calidad aceptable para el costo',
                'Producto económico y funcional',
                'Buena relación calidad-precio',
                'Producto accesible'
            ]),
        ]);
    }
}