<?php

namespace Database\Factories;

use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para el modelo City
 * 
 * Genera datos de prueba para ciudades venezolanas
 * con nombres realistas para el marketplace de ganado.
 */
class CityFactory extends Factory
{
    /**
     * Define el estado por defecto del modelo.
     */
    public function definition(): array
    {
        return [
            'state_id' => State::factory(),
            'name' => $this->faker->city(),
        ];
    }

    /**
     * Ciudades venezolanas principales
     */
    public function venezuelanCities(): static
    {
        $venezuelanCities = [
            // Zulia
            'Maracaibo', 'Cabimas', 'Ciudad Ojeda', 'San Francisco', 'La Concepción',
            // Barinas
            'Barinas', 'Barinitas', 'Socopó', 'Santa Bárbara', 'Sabaneta',
            // Apure
            'San Fernando de Apure', 'Guasdualito', 'El Amparo', 'Achaguas', 'Biruaca',
            // Cojedes
            'San Carlos', 'Tinaquillo', 'El Pao', 'Tinaco', 'Las Vegas',
            // Portuguesa
            'Guanare', 'Acarigua', 'Araure', 'Turén', 'Villa Bruzual',
            // Lara
            'Barquisimeto', 'Carora', 'El Tocuyo', 'Quíbor', 'Duaca',
            // Yaracuy
            'San Felipe', 'Yaritagua', 'Nirgua', 'Chivacoa', 'Urachiche',
            // Falcón
            'Coro', 'Punto Fijo', 'Valencia', 'La Vela', 'Dabajuro',
            // Carabobo
            'Valencia', 'Puerto Cabello', 'Guacara', 'Mariara', 'Bejuma',
            // Aragua
            'Maracay', 'Turmero', 'La Victoria', 'Villa de Cura', 'Cagua',
            // Miranda
            'Caracas', 'Los Teques', 'Guarenas', 'Guatire', 'Santa Teresa',
            // Guárico
            'San Juan de los Morros', 'Valle de la Pascua', 'Calabozo', 'Zaraza', 'Tucupido',
            // Anzoátegui
            'Barcelona', 'Puerto La Cruz', 'Lechería', 'El Tigre', 'Anaco',
            // Monagas
            'Maturín', 'Caripito', 'Punta de Mata', 'Temblador', 'Aragua de Maturín',
            // Sucre
            'Cumaná', 'Carúpano', 'Güiria', 'Río Caribe', 'Casanay',
            // Bolívar
            'Ciudad Bolívar', 'Ciudad Guayana', 'Upata', 'El Callao', 'Tumeremo',
            // Táchira
            'San Cristóbal', 'Táriba', 'La Fría', 'Rubio', 'Colón',
            // Mérida
            'Mérida', 'El Vigía', 'Tovar', 'Ejido', 'Lagunillas',
            // Trujillo
            'Trujillo', 'Valera', 'Boconó', 'La Puerta', 'Monay',
        ];

        return $this->state(function (array $attributes) use ($venezuelanCities) {
            return [
                'name' => $this->faker->randomElement($venezuelanCities),
            ];
        });
    }

    /**
     * Ciudades principales para ganado
     */
    public function cattleCities(): static
    {
        $cattleCities = [
            // Principales centros ganaderos
            'Maracaibo', 'Barinas', 'San Fernando de Apure', 'San Carlos', 'Guanare',
            'Acarigua', 'Barquisimeto', 'Carora', 'Valencia', 'Maracay',
            'San Juan de los Morros', 'Valle de la Pascua', 'Calabozo', 'Barcelona',
            'Maturín', 'Ciudad Bolívar', 'San Cristóbal', 'Mérida', 'Trujillo',
        ];

        return $this->state(function (array $attributes) use ($cattleCities) {
            return [
                'name' => $this->faker->randomElement($cattleCities),
            ];
        });
    }

}
