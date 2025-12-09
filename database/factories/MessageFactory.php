<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para el modelo Message
 * 
 * Genera datos de prueba para mensajes de chat
 * en conversaciones del marketplace de ganado.
 */
class MessageFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'sender_id' => Profile::factory(),
            'content' => $this->faker->paragraph(2),
            'message_type' => $this->faker->randomElement(['text', 'image', 'video', 'document']),
            'attachment_url' => $this->faker->optional(0.3)->url(),
            'read_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 week', 'now'),
            'is_deleted' => $this->faker->boolean(5), // 5% eliminados
        ];
    }

    /**
     * Estado para mensajes de texto
     */
    public function text(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => 'text',
            'content' => $this->faker->paragraph(2),
            'attachment_url' => null,
        ]);
    }

    /**
     * Estado para mensajes con imagen
     */
    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => 'image',
            'content' => $this->faker->optional(0.6)->sentence(),
            'attachment_url' => $this->faker->imageUrl(800, 600, 'animals'),
        ]);
    }

    /**
     * Estado para mensajes con video
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => 'video',
            'content' => $this->faker->optional(0.6)->sentence(),
            'attachment_url' => $this->faker->url() . '.mp4',
        ]);
    }

    /**
     * Estado para mensajes con documento
     */
    public function document(): static
    {
        return $this->state(fn (array $attributes) => [
            'message_type' => 'document',
            'content' => $this->faker->optional(0.6)->sentence(),
            'attachment_url' => $this->faker->url() . '.pdf',
        ]);
    }

    /**
     * Estado para mensajes leídos
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'is_deleted' => false,
        ]);
    }

    /**
     * Estado para mensajes no leídos
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
            'is_deleted' => false,
        ]);
    }

    /**
     * Estado para mensajes eliminados
     */
    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_deleted' => true,
            'read_at' => null,
        ]);
    }

    /**
     * Estado para mensajes recientes
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Estado para mensajes antiguos
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-3 months', '-1 month'),
            'updated_at' => $this->faker->dateTimeBetween('-3 months', '-1 month'),
        ]);
    }

    /**
     * Estado para mensajes sobre productos
     */
    public function aboutProduct(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->randomElement([
                '¿Cuál es el precio del ganado?',
                '¿Está disponible para entrega?',
                '¿Tiene certificados sanitarios?',
                '¿Cuál es la edad del ganado?',
                '¿Está vacunado?',
                '¿Puedo visitar la hacienda?',
                '¿Cuál es el peso promedio?',
                '¿Está certificado genéticamente?'
            ]),
        ]);
    }

    /**
     * Estado para mensajes sobre entrega
     */
    public function aboutDelivery(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->randomElement([
                '¿Hacen entrega a domicilio?',
                '¿Cuál es el costo de envío?',
                '¿En qué tiempo llega?',
                '¿Puedo recoger en la hacienda?',
                '¿Incluye transporte?',
                '¿Cuál es el radio de entrega?',
                '¿Pueden entregar mañana?',
                '¿Hay costo adicional por entrega?'
            ]),
        ]);
    }

    /**
     * Estado para mensajes sobre precios
     */
    public function aboutPrice(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->randomElement([
                '¿Cuál es el precio por cabeza?',
                '¿Hay descuento por cantidad?',
                '¿El precio es negociable?',
                '¿Cuál es el precio del lote completo?',
                '¿Aceptan pagos a plazos?',
                '¿Cuál es el precio por kilo?',
                '¿Hay descuento por pago al contado?',
                '¿Cuál es el precio mínimo?'
            ]),
        ]);
    }

    /**
     * Estado para mensajes sobre salud
     */
    public function aboutHealth(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->randomElement([
                '¿Está vacunado contra brucelosis?',
                '¿Tiene certificado sanitario?',
                '¿Está libre de tuberculosis?',
                '¿Cuándo fue la última vacunación?',
                '¿Tiene problemas de salud?',
                '¿Está certificado genéticamente?',
                '¿Cuál es su estado de salud?',
                '¿Tiene todas las vacunas?'
            ]),
        ]);
    }

    /**
     * Estado para mensajes de saludo
     */
    public function greeting(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->randomElement([
                'Hola, buenos días',
                'Buenas tardes',
                'Hola, ¿cómo está?',
                'Buenos días, ¿en qué puedo ayudarle?',
                'Hola, ¿le interesa el ganado?',
                'Buenas tardes, ¿tiene alguna pregunta?',
                'Hola, ¿necesita más información?',
                'Buenos días, ¿le gustaría visitar la hacienda?'
            ]),
        ]);
    }

    /**
     * Estado para mensajes de despedida
     */
    public function farewell(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->randomElement([
                'Gracias por su tiempo',
                'Nos vemos pronto',
                'Que tenga buen día',
                'Hasta luego',
                'Gracias por la información',
                'Nos mantenemos en contacto',
                'Que le vaya bien',
                'Hasta la próxima'
            ]),
        ]);
    }

    /**
     * Estado para mensajes de confirmación
     */
    public function confirmation(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->randomElement([
                'Perfecto, me interesa',
                'Sí, me gustaría comprar',
                'Confirmo la compra',
                'Estoy de acuerdo con el precio',
                'Sí, procedemos con la venta',
                'Confirmo la entrega',
                'Perfecto, nos vemos mañana',
                'Sí, acepto las condiciones'
            ]),
        ]);
    }

    /**
     * Estado para mensajes de negociación
     */
    public function negotiation(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->randomElement([
                '¿Podría bajar un poco el precio?',
                '¿Hay descuento por cantidad?',
                '¿Podemos negociar el precio?',
                '¿Cuál es su mejor precio?',
                '¿Acepta pagos a plazos?',
                '¿Podría incluir el transporte?',
                '¿Cuál es el precio mínimo?',
                '¿Podemos hacer un trato?'
            ]),
        ]);
    }

    /**
     * Estado para mensajes de vendedor
     */
    public function fromSeller(): static
    {
        return $this->state(fn (array $attributes) => [
            'sender_id' => Profile::factory()->verifiedSeller(),
            'content' => $this->faker->randomElement([
                'El ganado está en excelente estado',
                'Tenemos certificados sanitarios',
                'Puede visitar la hacienda cuando guste',
                'El precio es negociable',
                'Hacemos entrega a domicilio',
                'El ganado está vacunado',
                'Tenemos disponibilidad inmediata',
                'Puede ver el ganado en persona'
            ]),
        ]);
    }

    /**
     * Estado para mensajes de comprador
     */
    public function fromBuyer(): static
    {
        return $this->state(fn (array $attributes) => [
            'sender_id' => Profile::factory()->verifiedBuyer(),
            'content' => $this->faker->randomElement([
                'Me interesa el ganado',
                '¿Cuál es el precio?',
                '¿Puedo visitar la hacienda?',
                '¿Está disponible?',
                '¿Hacen entrega?',
                '¿Tiene certificados?',
                '¿Cuál es la edad del ganado?',
                '¿Está vacunado?'
            ]),
        ]);
    }
}
