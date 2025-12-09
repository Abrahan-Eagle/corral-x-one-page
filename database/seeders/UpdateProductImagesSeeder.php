<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductImage;

class UpdateProductImagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Actualizando URLs de imágenes de productos...');

        // URLs reales de ganado
        $cattleImages = [
            'https://diariolaeconomia.com/media/k2/items/cache/891bc0e45e0849a552d0ba70b9f8ec5e_XL.jpg',
            'https://www.agronegocios.co/assets/uploads/2017/02/14/083321-2000-1000-0-0--470-.jpg',
            'https://static.wikia.nocookie.net/silverspoon/images/0/0c/Cow_female_black_white.jpg/revision/latest?cb=20130804033709&path-prefix=es',
            'https://gruposansimon.com/web/wp-content/uploads/esta.jpg',
            'https://images.unsplash.com/photo-1560114928-40f1f1eb26a0?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1500595046743-cd271d694d30?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1564501049412-61c2a3083791?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1516467508483-a7212febe31a?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1544966503-7cc8bb01d7b9?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=800&h=600&fit=crop',
        ];

        $altTexts = [
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
            'Ganado Nelore',
            'Ganado en pastoreo',
            'Rebaño de vacas',
            'Terneros en el campo',
            'Ganado de engorde'
        ];

        // Actualizar todas las imágenes existentes
        $images = ProductImage::where('file_type', 'image')->get();
        $updatedCount = 0;

        foreach ($images as $image) {
            $image->update([
                'file_url' => fake()->randomElement($cattleImages),
                'alt_text' => fake()->randomElement($altTexts),
            ]);
            $updatedCount++;
        }

        $this->command->info("✅ {$updatedCount} imágenes actualizadas con URLs reales de ganado");

        // Verificar algunas URLs actualizadas
        $sampleImages = ProductImage::where('file_type', 'image')->take(3)->get(['file_url']);
        $this->command->info('📸 URLs de muestra:');
        foreach ($sampleImages as $img) {
            $this->command->line("   - {$img->file_url}");
        }
    }
}
