<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Seeder para el modelo Category
 * 
 * Pobla la base de datos con categorías de productos
 * específicas del mercado ganadero venezolano.
 */
class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Categorías principales
        $mainCategories = [
            [
                'name' => 'Ganado Bovino',
                'slug' => 'ganado-bovino',
                'description' => 'Categoría principal para todo tipo de ganado bovino',
                'parent_id' => null,
                'sort_order' => 1,
                'is_active' => true,
                'icon' => 'cow',
                'color' => '#8B4513',
            ],
            [
                'name' => 'Equipos Ganaderos',
                'slug' => 'equipos-ganaderos',
                'description' => 'Equipos y maquinaria para la actividad ganadera',
                'parent_id' => null,
                'sort_order' => 2,
                'is_active' => true,
                'icon' => 'tractor',
                'color' => '#228B22',
            ],
            [
                'name' => 'Alimentos y Suplementos',
                'slug' => 'alimentos-suplementos',
                'description' => 'Alimentos, suplementos y medicamentos para ganado',
                'parent_id' => null,
                'sort_order' => 3,
                'is_active' => true,
                'icon' => 'feed',
                'color' => '#FFD700',
            ],
            [
                'name' => 'Servicios Ganaderos',
                'slug' => 'servicios-ganaderos',
                'description' => 'Servicios especializados para la actividad ganadera',
                'parent_id' => null,
                'sort_order' => 4,
                'is_active' => true,
                'icon' => 'tools',
                'color' => '#4169E1',
            ],
        ];

        $mainCategoryIds = [];
        foreach ($mainCategories as $category) {
            $created = Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category
            );
            $mainCategoryIds[$category['slug']] = $created->id;
        }

        // Subcategorías de Ganado Bovino
        $cattleSubcategories = [
            [
                'name' => 'Novillos',
                'slug' => 'novillos',
                'description' => 'Ganado bovino macho para engorde',
                'parent_id' => $mainCategoryIds['ganado-bovino'],
                'sort_order' => 1,
                'is_active' => true,
                'icon' => 'bull',
                'color' => '#8B4513',
            ],
            [
                'name' => 'Vacas',
                'slug' => 'vacas',
                'description' => 'Ganado bovino hembra adulto',
                'parent_id' => $mainCategoryIds['ganado-bovino'],
                'sort_order' => 2,
                'is_active' => true,
                'icon' => 'cow',
                'color' => '#8B4513',
            ],
            [
                'name' => 'Terneros',
                'slug' => 'terneros',
                'description' => 'Ganado bovino joven',
                'parent_id' => $mainCategoryIds['ganado-bovino'],
                'sort_order' => 3,
                'is_active' => true,
                'icon' => 'calf',
                'color' => '#8B4513',
            ],
            [
                'name' => 'Toros',
                'slug' => 'toros',
                'description' => 'Ganado bovino macho reproductor',
                'parent_id' => $mainCategoryIds['ganado-bovino'],
                'sort_order' => 4,
                'is_active' => true,
                'icon' => 'bull',
                'color' => '#8B4513',
            ],
            [
                'name' => 'Vaquillonas',
                'slug' => 'vaquillonas',
                'description' => 'Ganado bovino hembra joven',
                'parent_id' => $mainCategoryIds['ganado-bovino'],
                'sort_order' => 5,
                'is_active' => true,
                'icon' => 'cow',
                'color' => '#8B4513',
            ],
            [
                'name' => 'Búfalos',
                'slug' => 'bufalos',
                'description' => 'Ganado búfalo para producción',
                'parent_id' => $mainCategoryIds['ganado-bovino'],
                'sort_order' => 6,
                'is_active' => true,
                'icon' => 'bull',
                'color' => '#8B4513',
            ],
        ];

        foreach ($cattleSubcategories as $subcategory) {
            Category::updateOrCreate(
                ['slug' => $subcategory['slug']],
                $subcategory
            );
        }

        // Subcategorías de Equipos Ganaderos
        $equipmentSubcategories = [
            [
                'name' => 'Tractores',
                'slug' => 'tractores',
                'description' => 'Tractores y maquinaria agrícola',
                'parent_id' => $mainCategoryIds['equipos-ganaderos'],
                'sort_order' => 1,
                'is_active' => true,
                'icon' => 'tractor',
                'color' => '#228B22',
            ],
            [
                'name' => 'Ordeñadoras',
                'slug' => 'ordenadoras',
                'description' => 'Equipos de ordeño y producción láctea',
                'parent_id' => $mainCategoryIds['equipos-ganaderos'],
                'sort_order' => 2,
                'is_active' => true,
                'icon' => 'milk',
                'color' => '#228B22',
            ],
            [
                'name' => 'Bebederos',
                'slug' => 'bebederos',
                'description' => 'Sistemas de bebederos automáticos',
                'parent_id' => $mainCategoryIds['equipos-ganaderos'],
                'sort_order' => 3,
                'is_active' => true,
                'icon' => 'water',
                'color' => '#228B22',
            ],
            [
                'name' => 'Comederos',
                'slug' => 'comederos',
                'description' => 'Comederos y sistemas de alimentación',
                'parent_id' => $mainCategoryIds['equipos-ganaderos'],
                'sort_order' => 4,
                'is_active' => true,
                'icon' => 'feed',
                'color' => '#228B22',
            ],
            [
                'name' => 'Cercas',
                'slug' => 'cercas',
                'description' => 'Sistemas de cercado y división',
                'parent_id' => $mainCategoryIds['equipos-ganaderos'],
                'sort_order' => 5,
                'is_active' => true,
                'icon' => 'fence',
                'color' => '#228B22',
            ],
            [
                'name' => 'Corrales',
                'slug' => 'corrales',
                'description' => 'Corrales y sistemas de manejo',
                'parent_id' => $mainCategoryIds['equipos-ganaderos'],
                'sort_order' => 6,
                'is_active' => true,
                'icon' => 'building',
                'color' => '#228B22',
            ],
        ];

        foreach ($equipmentSubcategories as $subcategory) {
            Category::updateOrCreate(
                ['slug' => $subcategory['slug']],
                $subcategory
            );
        }

        // Subcategorías de Alimentos y Suplementos
        $feedSubcategories = [
            [
                'name' => 'Concentrados',
                'slug' => 'concentrados',
                'description' => 'Alimentos concentrados para ganado',
                'parent_id' => $mainCategoryIds['alimentos-suplementos'],
                'sort_order' => 1,
                'is_active' => true,
                'icon' => 'feed',
                'color' => '#FFD700',
            ],
            [
                'name' => 'Forrajes',
                'slug' => 'forrajes',
                'description' => 'Forrajes verdes y secos',
                'parent_id' => $mainCategoryIds['alimentos-suplementos'],
                'sort_order' => 2,
                'is_active' => true,
                'icon' => 'grass',
                'color' => '#FFD700',
            ],
            [
                'name' => 'Medicamentos',
                'slug' => 'medicamentos',
                'description' => 'Medicamentos veterinarios',
                'parent_id' => $mainCategoryIds['alimentos-suplementos'],
                'sort_order' => 3,
                'is_active' => true,
                'icon' => 'medicine',
                'color' => '#FFD700',
            ],
            [
                'name' => 'Vitaminas',
                'slug' => 'vitaminas',
                'description' => 'Suplementos vitamínicos',
                'parent_id' => $mainCategoryIds['alimentos-suplementos'],
                'sort_order' => 4,
                'is_active' => true,
                'icon' => 'medicine',
                'color' => '#FFD700',
            ],
            [
                'name' => 'Minerales',
                'slug' => 'minerales',
                'description' => 'Suplementos minerales',
                'parent_id' => $mainCategoryIds['alimentos-suplementos'],
                'sort_order' => 5,
                'is_active' => true,
                'icon' => 'medicine',
                'color' => '#FFD700',
            ],
            [
                'name' => 'Vacunas',
                'slug' => 'vacunas',
                'description' => 'Vacunas para ganado',
                'parent_id' => $mainCategoryIds['alimentos-suplementos'],
                'sort_order' => 6,
                'is_active' => true,
                'icon' => 'medicine',
                'color' => '#FFD700',
            ],
        ];

        foreach ($feedSubcategories as $subcategory) {
            Category::updateOrCreate(
                ['slug' => $subcategory['slug']],
                $subcategory
            );
        }

        // Subcategorías de Servicios Ganaderos
        $servicesSubcategories = [
            [
                'name' => 'Servicios Veterinarios',
                'slug' => 'servicios-veterinarios',
                'description' => 'Servicios de medicina veterinaria',
                'parent_id' => $mainCategoryIds['servicios-ganaderos'],
                'sort_order' => 1,
                'is_active' => true,
                'icon' => 'medicine',
                'color' => '#4169E1',
            ],
            [
                'name' => 'Inseminación Artificial',
                'slug' => 'inseminacion-artificial',
                'description' => 'Servicios de inseminación artificial',
                'parent_id' => $mainCategoryIds['servicios-ganaderos'],
                'sort_order' => 2,
                'is_active' => true,
                'icon' => 'tools',
                'color' => '#4169E1',
            ],
            [
                'name' => 'Transporte de Ganado',
                'slug' => 'transporte-ganado',
                'description' => 'Servicios de transporte de ganado',
                'parent_id' => $mainCategoryIds['servicios-ganaderos'],
                'sort_order' => 3,
                'is_active' => true,
                'icon' => 'tractor',
                'color' => '#4169E1',
            ],
            [
                'name' => 'Análisis de Laboratorio',
                'slug' => 'analisis-laboratorio',
                'description' => 'Servicios de análisis de laboratorio',
                'parent_id' => $mainCategoryIds['servicios-ganaderos'],
                'sort_order' => 4,
                'is_active' => true,
                'icon' => 'tools',
                'color' => '#4169E1',
            ],
            [
                'name' => 'Asesoría Técnica',
                'slug' => 'asesoria-tecnica',
                'description' => 'Asesoría técnica ganadera',
                'parent_id' => $mainCategoryIds['servicios-ganaderos'],
                'sort_order' => 5,
                'is_active' => true,
                'icon' => 'tools',
                'color' => '#4169E1',
            ],
            [
                'name' => 'Certificaciones',
                'slug' => 'certificaciones',
                'description' => 'Servicios de certificación ganadera',
                'parent_id' => $mainCategoryIds['servicios-ganaderos'],
                'sort_order' => 6,
                'is_active' => true,
                'icon' => 'tools',
                'color' => '#4169E1',
            ],
        ];

        foreach ($servicesSubcategories as $subcategory) {
            Category::updateOrCreate(
                ['slug' => $subcategory['slug']],
                $subcategory
            );
        }
    }
}
