<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder para Anuncios/Publicidad
 * 
 * Crea anuncios de prueba para el marketplace:
 * - Productos patrocinados (sponsored_product)
 * - Publicidad externa de terceros (external_ad)
 */
class AdvertisementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📢 Creando anuncios de publicidad...');

        // Obtener usuarios admin para crear anuncios
        $admins = User::where('role', 'admin')->get();
        
        if ($admins->isEmpty()) {
            $this->command->warn('⚠️  No hay usuarios admin. Creando anuncios sin created_by...');
            $admin = null;
        } else {
            $admin = $admins->first();
        }

        // Crear productos patrocinados
        $this->createSponsoredProducts($admin);

        // Crear publicidad externa
        $this->createExternalAds($admin);

        $this->command->info('✅ Anuncios de publicidad creados exitosamente!');
    }

    /**
     * Crear productos patrocinados
     */
    private function createSponsoredProducts(?User $admin): void
    {
        $this->command->info('   🎯 Creando productos patrocinados...');

        // URLs de ganado de ProductImageFactory (Unsplash)
        $cattleImages = [
            'https://images.unsplash.com/photo-1719167610856-415b6938a40e?fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1yZWxhdGVkfDE4fHx8ZW58MHx8fHx8&ixlib=rb-4.1.0&q=60&?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1500595046743-cd271d694d30?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1568478570328-be3225a8dc05?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1589348792383-8ebcbec4ef88?fm=jpg&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8Y2F0dGxlJTIwZ3JhemluZ3xlbnwwfHwwfHx8MA%3D%3D&ixlib=rb-4.1.0&q=60?w=800&h=600&fit=crop',
        ];

        // Obtener solo 3 productos activos aleatorios
        $activeProducts = Product::where('status', 'active')
            ->inRandomOrder()
            ->limit(3)
            ->get();

        if ($activeProducts->isEmpty()) {
            $this->command->warn('      ⚠️  No hay productos activos para patrocinar');
            return;
        }

        $imageIndex = 0;
        foreach ($activeProducts as $product) {
            $product->load(['ranch', 'images']);
            $ranchName = $product->ranch?->name ?? 'Hacienda';
            // Usar imagen del producto si existe, sino usar URL de ganado de Unsplash
            $imageUrl = $product->images->first()?->file_url ?? $cattleImages[$imageIndex % count($cattleImages)];
            $createdBy = $admin?->id ?? User::factory()->admin()->create()->id;

            Advertisement::factory()
                ->sponsoredProduct()
                ->active()
                ->create([
                    'product_id' => $product->id,
                    'created_by' => $createdBy,
                    'title' => "⭐ {$product->title} - Destacado",
                    'description' => "Producto destacado de {$ranchName}",
                    'image_url' => $imageUrl,
                    'target_url' => null, // Apunta al producto mismo
                    'priority' => rand(10, 50), // Alta prioridad
                    'start_date' => now()->subDays(rand(1, 7)),
                    'end_date' => now()->addDays(rand(15, 60)),
                ]);

            $this->command->info("      ✅ Producto patrocinado creado: {$product->title}");
            $imageIndex++;
        }
    }

    /**
     * Crear publicidad externa de terceros
     */
    private function createExternalAds(?User $admin): void
    {
        $this->command->info('   🏢 Creando publicidad externa...');

        // URLs de ganado de ProductImageFactory (Unsplash)
        $cattleImages = [
            'https://images.unsplash.com/photo-1719167610856-415b6938a40e?fm=jpg&ixid=M3wxMjA3fDB8MHxwaG90by1yZWxhdGVkfDE4fHx8ZW58MHx8fHx8&ixlib=rb-4.1.0&q=60&?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1500595046743-cd271d694d30?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1568478570328-be3225a8dc05?ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&?w=800&h=600&fit=crop',
            'https://images.unsplash.com/photo-1589348792383-8ebcbec4ef88?fm=jpg&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8Y2F0dGxlJTIwZ3JhemluZ3xlbnwwfHwwfHx8MA%3D%3D&ixlib=rb-4.1.0&q=60?w=800&h=600&fit=crop',
        ];

        // Solo crear 1 anuncio externo activo
        $advertiser = [
            'name' => 'Agropecuaria La Esperanza',
            'description' => 'Alimentos balanceados y suplementos para ganado',
            'image_url' => $cattleImages[0], // Usar imagen de ganado
            'target_url' => null,
        ];

        Advertisement::factory()
            ->externalAd()
            ->active()
            ->create([
                'advertiser_name' => $advertiser['name'],
                'title' => $advertiser['name'],
                'description' => $advertiser['description'],
                'image_url' => $advertiser['image_url'],
                'target_url' => $advertiser['target_url'],
                'created_by' => $admin?->id ?? User::factory()->admin()->create()->id,
                'priority' => rand(10, 50), // Prioridad media
                'start_date' => now()->subDays(rand(1, 14)),
                'end_date' => now()->addDays(rand(30, 90)),
            ]);

        $this->command->info("      ✅ Publicidad externa creada: {$advertiser['name']}");
    }
}
