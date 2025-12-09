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

        // Obtener productos activos aleatorios
        $activeProducts = Product::where('status', 'active')
            ->inRandomOrder()
            ->limit(10)
            ->get();

        if ($activeProducts->isEmpty()) {
            $this->command->warn('      ⚠️  No hay productos activos para patrocinar');
            return;
        }

        foreach ($activeProducts as $product) {
            $product->load(['ranch', 'images']);
            $ranchName = $product->ranch?->name ?? 'Hacienda';
            $imageUrl = $product->images->first()?->file_url ?? 'https://via.placeholder.com/800x600?text=Producto+Patrocinado';
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
                    'priority' => rand(50, 100), // Alta prioridad
                    'start_date' => now()->subDays(rand(1, 7)),
                    'end_date' => now()->addDays(rand(15, 60)),
                ]);

            $this->command->info("      ✅ Producto patrocinado creado: {$product->title}");
        }
    }

    /**
     * Crear publicidad externa de terceros
     */
    private function createExternalAds(?User $admin): void
    {
        $this->command->info('   🏢 Creando publicidad externa...');

        // Anunciantes venezolanos comunes
        $advertisers = [
            [
                'name' => 'Toyota de Venezuela',
                'description' => 'Vehículos y repuestos para el sector ganadero',
                'image_url' => 'https://via.placeholder.com/800x600?text=Toyota+Venezuela',
                'target_url' => 'https://toyota.com.ve',
            ],
            [
                'name' => 'Agropecuaria La Esperanza',
                'description' => 'Alimentos balanceados y suplementos para ganado',
                'image_url' => 'https://via.placeholder.com/800x600?text=Agropecuaria',
                'target_url' => null,
            ],
            [
                'name' => 'Veterinaria San Gabriel',
                'description' => 'Servicios veterinarios y vacunas para ganado',
                'image_url' => 'https://via.placeholder.com/800x600?text=Veterinaria',
                'target_url' => null,
            ],
            [
                'name' => 'Fertilizantes del Llano',
                'description' => 'Fertilizantes y productos para pastos',
                'image_url' => 'https://via.placeholder.com/800x600?text=Fertilizantes',
                'target_url' => null,
            ],
            [
                'name' => 'Maquinaria Agrícola Venagro',
                'description' => 'Equipos y maquinaria para el campo',
                'image_url' => 'https://via.placeholder.com/800x600?text=Maquinaria',
                'target_url' => null,
            ],
            [
                'name' => 'Seguros Ganaderos del Sur',
                'description' => 'Seguros especializados para ganadería',
                'image_url' => 'https://via.placeholder.com/800x600?text=Seguros',
                'target_url' => null,
            ],
            [
                'name' => 'Banco Agrícola',
                'description' => 'Créditos y financiamiento para ganaderos',
                'image_url' => 'https://via.placeholder.com/800x600?text=Banco+Agricola',
                'target_url' => null,
            ],
            [
                'name' => 'Transporte de Ganado Express',
                'description' => 'Servicios de transporte de ganado',
                'image_url' => 'https://via.placeholder.com/800x600?text=Transporte',
                'target_url' => null,
            ],
        ];

        foreach ($advertisers as $advertiser) {
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

        // Crear algunos anuncios inactivos para pruebas
        for ($i = 0; $i < 3; $i++) {
            Advertisement::factory()
                ->externalAd()
                ->inactive()
                ->create([
                    'created_by' => $admin?->id ?? User::factory()->admin()->create()->id,
                ]);
        }

        // Crear algunos anuncios expirados para pruebas
        for ($i = 0; $i < 2; $i++) {
            Advertisement::factory()
                ->externalAd()
                ->expired()
                ->create([
                    'created_by' => $admin?->id ?? User::factory()->admin()->create()->id,
                ]);
        }
    }
}
