<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para el modelo ProductImage
 * 
 * Genera datos de prueba para imágenes y videos de productos
 * con metadatos realistas para el marketplace de ganado.
 */
class ProductImageFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     */
    public function definition(): array
    {
        $fileType = $this->faker->randomElement(['image', 'video']);
        
        // URLs reales de ganado para imágenes por defecto
        $defaultCattleImages = [
            'https://images.unsplash.com/photo-1719167610856-415b6938a40e?fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1yZWxhdGVkfDE4fHx8ZW58MHx8fHx8&ixlib=rb-4.1.0&q=60&?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1500595046743-cd271d694d30?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1568478570328-be3225a8dc05?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1589348792383-8ebcbec4ef88?fm=jpg&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8Y2F0dGxlJTIwZ3JhemluZ3xlbnwwfHwwfHx8MA%3D%3D&ixlib=rb-4.1.0&q=60?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1500595046743-cd271d694d30?w=800&h=600&fit=crop',
        ];
        
        return [
            'product_id' => Product::factory(),
            'file_url' => $fileType === 'image' ? $this->faker->randomElement($defaultCattleImages) : $this->faker->url() . '.mp4',
            'file_type' => $fileType,
            'alt_text' => $this->faker->optional(0.7)->sentence(4),
            'is_primary' => $this->faker->boolean(20), // 20% son principales
            'sort_order' => $this->faker->numberBetween(0, 10),
            'duration' => $fileType === 'video' ? $this->faker->numberBetween(5, 15) : null,
            'file_size' => $this->faker->numberBetween(500000, 10000000), // 500KB - 10MB
            'resolution' => $this->faker->randomElement(['1920x1080', '1280x720', '800x600', '1024x768']),
            'format' => $fileType === 'image' ? $this->faker->randomElement(['jpg', 'png', 'webp']) : 'mp4',
            'compression' => $this->faker->optional(0.5)->randomElement(['high', 'medium', 'low']),
        ];
    }

    /**
     * Estado para imágenes
     */
    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_type' => 'image',
            'file_url' => $this->faker->imageUrl(800, 600, 'animals'),
            'duration' => null,
            'format' => $this->faker->randomElement(['jpg', 'png', 'webp']),
            'resolution' => $this->faker->randomElement(['1920x1080', '1280x720', '800x600']),
        ]);
    }

    /**
     * Estado para videos
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_type' => 'video',
            'file_url' => $this->faker->url() . '.mp4',
            'duration' => $this->faker->numberBetween(5, 15), // Máximo 15 segundos
            'format' => 'mp4',
            'resolution' => $this->faker->randomElement(['1920x1080', '1280x720']),
            'file_size' => $this->faker->numberBetween(2000000, 50000000), // 2MB - 50MB
        ]);
    }

    /**
     * Estado para imagen principal
     */
    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
            'sort_order' => 0,
            'file_type' => 'image', // Las principales siempre son imágenes
            'format' => 'jpg',
        ]);
    }

    /**
     * Estado para imágenes de alta calidad
     */
    public function highQuality(): static
    {
        return $this->state(fn (array $attributes) => [
            'resolution' => '1920x1080',
            'file_size' => $this->faker->numberBetween(2000000, 8000000), // 2MB - 8MB
            'compression' => 'high',
            'format' => 'jpg',
        ]);
    }

    /**
     * Estado para imágenes de baja calidad
     */
    public function lowQuality(): static
    {
        return $this->state(fn (array $attributes) => [
            'resolution' => '800x600',
            'file_size' => $this->faker->numberBetween(200000, 800000), // 200KB - 800KB
            'compression' => 'low',
            'format' => 'jpg',
        ]);
    }

    /**
     * Estado para videos cortos
     */
    public function shortVideo(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_type' => 'video',
            'duration' => $this->faker->numberBetween(5, 10),
            'file_size' => $this->faker->numberBetween(1000000, 3000000), // 1MB - 3MB
            'format' => 'mp4',
        ]);
    }

    /**
     * Estado para videos largos (máximo 15 segundos)
     */
    public function longVideo(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_type' => 'video',
            'duration' => $this->faker->numberBetween(10, 15),
            'file_size' => $this->faker->numberBetween(3000000, 10000000), // 3MB - 10MB
            'format' => 'mp4',
        ]);
    }

    /**
     * Estado para imágenes de ganado específico
     */
    public function cattleImage(): static
    {
        $cattleImages = [
            'https://diariolaeconomia.com/media/k2/items/cache/891bc0e45e0849a552d0ba70b9f8ec5e_XL.jpg',
            'https://www.fincasadhana.mx/wp-content/uploads/2019/11/IMG_1902.jpg',
            'https://static.wikia.nocookie.net/silverspoon/images/0/0c/Cow_female_black_white.jpg/revision/latest?cb=20130804033709&path-prefix=es',
            'https://gruposansimon.com/web/wp-content/uploads/esta.jpg',
            'https://i.pinimg.com/originals/0c/0c/f8/0c0cf804a70201bb973d6a7dcbe6aace.jpg',
            'https://images.unsplash.com/photo-1500595046743-cd271d694d30?w=800&h=600&fit=crop',
            'https://i.pinimg.com/originals/c1/5d/dc/c15ddce6844b74fd749e27be9b47bb13.jpg',
            'https://i0.wp.com/geneticatricolor.com/home/wp-content/uploads/70110-JDH-Hawk-Manso-666-5-Brahman-Gris-1.jpeg?fit=800%2C600&ssl=1',
            'https://hgeldorado.com/images/Destete-1.png',
            'https://diariolaeconomia.com/media/k2/items/cache/891bc0e45e0849a552d0ba70b9f8ec5e_XL.jpg',
            'https://www.fincasadhana.mx/wp-content/uploads/2019/11/IMG_1902.jpg',
            'https://static.wikia.nocookie.net/silverspoon/images/0/0c/Cow_female_black_white.jpg/revision/latest?cb=20130804033709&path-prefix=es',
            'https://gruposansimon.com/web/wp-content/uploads/esta.jpg',
            'https://i.pinimg.com/originals/0c/0c/f8/0c0cf804a70201bb973d6a7dcbe6aace.jpg',
            'https://images.unsplash.com/photo-1500595046743-cd271d694d30?w=800&h=600&fit=crop',
            'https://i.pinimg.com/originals/c1/5d/dc/c15ddce6844b74fd749e27be9b47bb13.jpg',
            'https://i0.wp.com/geneticatricolor.com/home/wp-content/uploads/70110-JDH-Hawk-Manso-666-5-Brahman-Gris-1.jpeg?fit=800%2C600&ssl=1',
            'https://hgeldorado.com/images/Destete-1.png',        
            'https://diariolaeconomia.com/media/k2/items/cache/891bc0e45e0849a552d0ba70b9f8ec5e_XL.jpg',
            'https://www.fincasadhana.mx/wp-content/uploads/2019/11/IMG_1902.jpg',
            'https://static.wikia.nocookie.net/silverspoon/images/0/0c/Cow_female_black_white.jpg/revision/latest?cb=20130804033709&path-prefix=es',
            'https://gruposansimon.com/web/wp-content/uploads/esta.jpg',
            'https://i.pinimg.com/originals/0c/0c/f8/0c0cf804a70201bb973d6a7dcbe6aace.jpg',
            'https://images.unsplash.com/photo-1500595046743-cd271d694d30?w=800&h=600&fit=crop',
            'https://i.pinimg.com/originals/c1/5d/dc/c15ddce6844b74fd749e27be9b47bb13.jpg',
            'https://i0.wp.com/geneticatricolor.com/home/wp-content/uploads/70110-JDH-Hawk-Manso-666-5-Brahman-Gris-1.jpeg?fit=800%2C600&ssl=1',
            'https://hgeldorado.com/images/Destete-1.png',            
        ];

        return $this->state(fn (array $attributes) => [
            'file_type' => 'image',
            'file_url' => $this->faker->randomElement($cattleImages),
            'alt_text' => $this->faker->randomElement([
                'Ganado bovino en pastoreo',
                'Novillos en el campo',
                'Vacas lecheras',
                'Terneros jugando',
                'Toro reproductor',
                'Ganado Brahman',
                'Ganado Holstein',
                'Ganado Guzerat',
                'Ganado Angus',
                'Ganado Simmental',
                'Ganado Charolais',
                'Ganado Nelore'
            ]),
        ]);
    }

    /**
     * Estado para videos de ganado
     */
    public function cattleVideo(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_type' => 'video',
            'file_url' => $this->faker->url() . '.mp4',
            'duration' => $this->faker->numberBetween(8, 15),
            'alt_text' => $this->faker->randomElement([
                'Video del ganado en movimiento',
                'Ganado pastando',
                'Ganado en el corral',
                'Video de la hacienda',
                'Ganado en el campo'
            ]),
        ]);
    }

    /**
     * Estado para imágenes de equipos
     */
    public function equipmentImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_type' => 'image',
            'file_url' => $this->faker->imageUrl(800, 600, 'technics', true, 'tractor'),
            'alt_text' => $this->faker->randomElement([
                'Tractor agrícola',
                'Equipo de ordeño',
                'Bebedero automático',
                'Comedero para ganado',
                'Cerca eléctrica'
            ]),
        ]);
    }

    /**
     * Estado para imágenes de alimentos
     */
    public function feedImage(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_type' => 'image',
            'file_url' => $this->faker->imageUrl(800, 600, 'food', true, 'feed'),
            'alt_text' => $this->faker->randomElement([
                'Concentrado para ganado',
                'Forraje verde',
                'Suplemento vitamínico',
                'Minerales para ganado',
                'Alimento balanceado'
            ]),
        ]);
    }

    /**
     * Estado para imágenes sin texto alternativo
     */
    public function noAltText(): static
    {
        return $this->state(fn (array $attributes) => [
            'alt_text' => null,
        ]);
    }

    /**
     * Estado para archivos grandes
     */
    public function largeFile(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_size' => $this->faker->numberBetween(5000000, 20000000), // 5MB - 20MB
            'resolution' => '1920x1080',
            'compression' => 'high',
        ]);
    }

    /**
     * Estado para archivos pequeños
     */
    public function smallFile(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_size' => $this->faker->numberBetween(100000, 500000), // 100KB - 500KB
            'resolution' => '800x600',
            'compression' => 'low',
        ]);
    }
}
