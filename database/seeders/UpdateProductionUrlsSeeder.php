<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Profile;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\DB;

class UpdateProductionUrlsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Actualizando URLs de imágenes para producción...');

        // URL de producción (desde .env)
        $productionUrl = env('APP_URL_PRODUCTION', 'https://backend.corralx.com');
        
        // Patrones de URLs locales a reemplazar
        $localPatterns = [
            'http://192.168.0.102:8000',
            'http://192.168.27.12:8000',
            'http://localhost:8000',
            'http://127.0.0.1:8000',
        ];

        $updatedProfiles = 0;
        $updatedProducts = 0;
        $updatedProductImages = 0;

        // 1. Actualizar fotos de perfil (photo_users)
        $this->command->info('📸 Actualizando fotos de perfil...');
        foreach ($localPatterns as $pattern) {
            $profiles = Profile::where('photo_users', 'like', $pattern . '%')->get();
            
            foreach ($profiles as $profile) {
                $oldUrl = $profile->photo_users;
                // Extraer solo la ruta después de /storage/
                if (preg_match('/\/storage\/(.+)$/', $oldUrl, $matches)) {
                    $newUrl = $productionUrl . '/storage/' . $matches[1];
                    $profile->update(['photo_users' => $newUrl]);
                    $updatedProfiles++;
                    $this->command->line("  ✓ Profile {$profile->id}: {$oldUrl} → {$newUrl}");
                }
            }
        }

        // 2. Actualizar imágenes de productos (ProductImage)
        $this->command->info('🖼️  Actualizando imágenes de productos...');
        foreach ($localPatterns as $pattern) {
            $images = ProductImage::where('image_path', 'like', $pattern . '%')->get();
            
            foreach ($images as $image) {
                $oldUrl = $image->image_path;
                // Extraer solo la ruta después de /storage/
                if (preg_match('/\/storage\/(.+)$/', $oldUrl, $matches)) {
                    $newUrl = $productionUrl . '/storage/' . $matches[1];
                    $image->update(['image_path' => $newUrl]);
                    $updatedProductImages++;
                    $this->command->line("  ✓ ProductImage {$image->id}: {$oldUrl} → {$newUrl}");
                }
            }
        }

        // 3. Actualizar featured_image de productos (si existe)
        $this->command->info('🌟 Actualizando imágenes destacadas de productos...');
        foreach ($localPatterns as $pattern) {
            $products = Product::where('featured_image', 'like', $pattern . '%')->get();
            
            foreach ($products as $product) {
                $oldUrl = $product->featured_image;
                // Extraer solo la ruta después de /storage/
                if (preg_match('/\/storage\/(.+)$/', $oldUrl, $matches)) {
                    $newUrl = $productionUrl . '/storage/' . $matches[1];
                    $product->update(['featured_image' => $newUrl]);
                    $updatedProducts++;
                    $this->command->line("  ✓ Product {$product->id}: {$oldUrl} → {$newUrl}");
                }
            }
        }

        // Resumen
        $this->command->newLine();
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('✅ ACTUALIZACIÓN COMPLETADA');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->line("📸 Fotos de perfil actualizadas: {$updatedProfiles}");
        $this->command->line("🖼️  Imágenes de productos actualizadas: {$updatedProductImages}");
        $this->command->line("🌟 Imágenes destacadas actualizadas: {$updatedProducts}");
        $this->command->line("📊 Total de URLs actualizadas: " . ($updatedProfiles + $updatedProducts + $updatedProductImages));
        $this->command->newLine();
    }
}

