<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\Ranch;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Review;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Report;
use App\Models\Address;
use App\Models\Phone;
// Documentos CI/RIF post-MVP (sin tablas activas actualmente)
use Illuminate\Database\Seeder;

/**
 * Seeder principal para CorralX
 * 
 * Pobla la base de datos con datos de prueba realistas
 * para el marketplace de ganado venezolano.
 */
class CorralXSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando seeder de CorralX...');

        // Crear códigos de operadora primero
        $this->createOperatorCodes();

        // Crear usuarios administradores
        $this->createAdmins();
        
        // Crear usuarios vendedores verificados
        $this->createVerifiedSellers();
        
        // Crear usuarios compradores verificados
        $this->createVerifiedBuyers();
        
        // Crear usuarios mixtos (compradores y vendedores)
        $this->createMixedUsers();
        
        // Crear usuarios nuevos
        $this->createNewUsers();
        
        // Crear usuarios premium
        $this->createPremiumUsers();

        // Crear anuncios de publicidad
        $this->command->info('');
        $this->call(AdvertisementSeeder::class);

        $this->command->info('✅ Seeder de CorralX completado exitosamente!');
    }

    /**
     * Crear usuarios administradores
     */
    private function createAdmins(): void
    {
        $this->command->info('👑 Creando administradores...');

        $admins = User::factory()
            ->count(3)
            ->admin()
            ->venezuelan()
            ->create();

        foreach ($admins as $admin) {
            Profile::factory()
                ->venezuelan()
                ->completeData()
                ->create(['user_id' => $admin->id]);

            $this->command->info("   ✅ Admin creado: {$admin->name}");
        }
    }

    /**
     * Crear usuarios vendedores verificados
     */
    private function createVerifiedSellers(): void
    {
        $this->command->info('🐄 Creando vendedores verificados...');

        $sellers = User::factory()
            ->count(15)
            ->user()
            ->venezuelan()
            ->completedOnboarding()
            ->create();

        foreach ($sellers as $seller) {
            $profile = Profile::factory()
                ->venezuelan()
                ->verifiedSeller()
                ->experienced()
                ->create(['user_id' => $seller->id]);

            // Documentos CI deshabilitados (post-MVP)

            // Crear dirección
            $address = Address::factory()
                ->ranchAddress()
                ->cattleStates()
                ->create(['profile_id' => $profile->id]);

            // Crear teléfono
            Phone::factory()
                ->profilePhone()
                ->primary()
                ->approved()
                ->create(['profile_id' => $profile->id]);

            // Crear hacienda principal
            $ranch = Ranch::factory()
                ->venezuelan()
                ->primary()
                ->certified()
                ->highRated()
                ->create([
                    'profile_id' => $profile->id,
                    'address_id' => $address->id,
                ]);

            // Documentos RIF deshabilitados (post-MVP)

            // Crear teléfono de la hacienda
            Phone::factory()
                ->ranchPhone()
                ->primary()
                ->approved()
                ->create([
                    'profile_id' => $profile->id,
                    'ranch_id' => $ranch->id,
                ]);

            // Crear productos
            $this->createProductsForRanch($ranch);

            $this->command->info("   ✅ Vendedor creado: {$profile->firstName} {$profile->lastName}");
        }
    }

    /**
     * Crear usuarios compradores verificados
     */
    private function createVerifiedBuyers(): void
    {
        $this->command->info('🛒 Creando compradores verificados...');

        $buyers = User::factory()
            ->count(25)
            ->user()
            ->venezuelan()
            ->completedOnboarding()
            ->create();

        foreach ($buyers as $buyer) {
            $profile = Profile::factory()
                ->venezuelan()
                ->verifiedBuyer()
                ->create(['user_id' => $buyer->id]);

            // Documentos CI deshabilitados (post-MVP)

            // Crear dirección
            Address::factory()
                ->urban()
                ->cattleCities()
                ->create(['profile_id' => $profile->id]);

            // Crear teléfono
            Phone::factory()
                ->profilePhone()
                ->primary()
                ->approved()
                ->create(['profile_id' => $profile->id]);

            // Crear favoritos
            $this->createFavoritesForProfile($profile);

            $this->command->info("   ✅ Comprador creado: {$profile->firstName} {$profile->lastName}");
        }
    }

    /**
     * Crear usuarios mixtos (compradores y vendedores)
     */
    private function createMixedUsers(): void
    {
        $this->command->info('🔄 Creando usuarios mixtos...');

        $mixedUsers = User::factory()
            ->count(20)
            ->user()
            ->venezuelan()
            ->completedOnboarding()
            ->create();

        foreach ($mixedUsers as $user) {
            $profile = Profile::factory()
                ->venezuelan()
                ->both()
                ->experienced()
                ->create(['user_id' => $user->id]);

            // Documentos CI deshabilitados (post-MVP)

            // Crear dirección
            $address = Address::factory()
                ->ranchAddress()
                ->cattleStates()
                ->create(['profile_id' => $profile->id]);

            // Crear teléfono
            Phone::factory()
                ->profilePhone()
                ->primary()
                ->approved()
                ->create(['profile_id' => $profile->id]);

            // Crear hacienda
            $ranch = Ranch::factory()
                ->venezuelan()
                ->primary()
                ->create([
                    'profile_id' => $profile->id,
                    'address_id' => $address->id,
                ]);

            // Documentos RIF deshabilitados (post-MVP)

            // Crear productos
            $this->createProductsForRanch($ranch);

            // Crear favoritos
            $this->createFavoritesForProfile($profile);

            $this->command->info("   ✅ Usuario mixto creado: {$profile->firstName} {$profile->lastName}");
        }
    }

    /**
     * Crear usuarios nuevos
     */
    private function createNewUsers(): void
    {
        $this->command->info('🆕 Creando usuarios nuevos...');

        $newUsers = User::factory()
            ->count(30)
            ->user()
            ->venezuelan()
            ->incompleteOnboarding()
            ->create();

        foreach ($newUsers as $user) {
            $profile = Profile::factory()
                ->venezuelan()
                ->newUser()
                ->create(['user_id' => $user->id]);

            $this->command->info("   ✅ Usuario nuevo creado: {$profile->firstName} {$profile->lastName}");
        }
    }

    /**
     * Crear usuarios premium
     */
    private function createPremiumUsers(): void
    {
        $this->command->info('⭐ Creando usuarios premium...');

        $premiumUsers = User::factory()
            ->count(5)
            ->user()
            ->venezuelan()
            ->completedOnboarding()
            ->create();

        foreach ($premiumUsers as $user) {
            $profile = Profile::factory()
                ->venezuelan()
                ->premium()
                ->experienced()
                ->create(['user_id' => $user->id]);

            // Documentos CI deshabilitados (post-MVP)

            // Crear dirección
            $address = Address::factory()
                ->ranchAddress()
                ->cattleStates()
                ->create(['profile_id' => $profile->id]);

            // Crear teléfono
            Phone::factory()
                ->profilePhone()
                ->primary()
                ->approved()
                ->create(['profile_id' => $profile->id]);

            // Crear hacienda premium
            $ranch = Ranch::factory()
                ->venezuelan()
                ->primary()
                ->certified()
                ->highRated()
                ->create([
                    'profile_id' => $profile->id,
                    'address_id' => $address->id,
                ]);

            // Documentos RIF deshabilitados (post-MVP)

            // Crear productos premium
            $this->createPremiumProductsForRanch($ranch);

            $this->command->info("   ✅ Usuario premium creado: {$profile->firstName} {$profile->lastName}");
        }
    }

    /**
     * Crear productos para una hacienda
     */
    private function createProductsForRanch(Ranch $ranch): void
    {
        $productCount = rand(3, 8);
        
        for ($i = 0; $i < $productCount; $i++) {
            $product = Product::factory()
                ->active()
                ->create(['ranch_id' => $ranch->id]);

            // Crear imágenes para el producto
            $this->createImagesForProduct($product);

            // Crear reseñas para el producto
            $this->createReviewsForProduct($product);

            // Crear conversaciones sobre el producto
            $this->createConversationsForProduct($product);
        }
    }

    /**
     * Crear productos premium para una hacienda
     */
    private function createPremiumProductsForRanch(Ranch $ranch): void
    {
        $productCount = rand(5, 10);
        
        for ($i = 0; $i < $productCount; $i++) {
            $product = Product::factory()
                ->premium()
                ->featured()
                ->create(['ranch_id' => $ranch->id]);

            // Crear imágenes para el producto
            $this->createImagesForProduct($product);

            // Crear reseñas para el producto
            $this->createReviewsForProduct($product);

            // Crear conversaciones sobre el producto
            $this->createConversationsForProduct($product);
        }
    }

    /**
     * Crear imágenes para un producto
     */
    private function createImagesForProduct(Product $product): void
    {
        $imageCount = rand(3, 8);
        
        for ($i = 0; $i < $imageCount; $i++) {
            $isPrimary = $i === 0;
            
            ProductImage::factory()
                ->cattleImage()
                ->create([
                    'product_id' => $product->id,
                    'is_primary' => $isPrimary,
                    'sort_order' => $i,
                ]);
        }

        // Agregar algunos videos
        $videoCount = rand(1, 3);
        for ($i = 0; $i < $videoCount; $i++) {
            ProductImage::factory()
                ->cattleVideo()
                ->create([
                    'product_id' => $product->id,
                    'sort_order' => $imageCount + $i,
                ]);
        }
    }

    /**
     * Crear reseñas para un producto
     */
    private function createReviewsForProduct(Product $product): void
    {
        $reviewCount = rand(2, 8);
        
        for ($i = 0; $i < $reviewCount; $i++) {
            Review::factory()
                ->approved()
                ->verifiedPurchase()
                ->create([
                    'product_id' => $product->id,
                    'ranch_id' => $product->ranch_id,
                ]);
        }
    }

    /**
     * Crear conversaciones para un producto
     */
    private function createConversationsForProduct(Product $product): void
    {
        $conversationCount = rand(1, 5);
        
        for ($i = 0; $i < $conversationCount; $i++) {
            $conversation = Conversation::factory()
                ->aboutProduct()
                ->active()
                ->create([
                    'product_id' => $product->id,
                    'ranch_id' => $product->ranch_id,
                ]);

            // Crear mensajes para la conversación
            $this->createMessagesForConversation($conversation);
        }
    }

    /**
     * Crear mensajes para una conversación
     */
    private function createMessagesForConversation(Conversation $conversation): void
    {
        $messageCount = rand(3, 15);
        
        for ($i = 0; $i < $messageCount; $i++) {
            $isFromSeller = $i % 2 === 0;
            
            Message::factory()
                ->text()
                ->aboutProduct()
                ->create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $isFromSeller ? $conversation->profile_id_2 : $conversation->profile_id_1,
                ]);
        }
    }

    /**
     * Crear favoritos para un perfil
     */
    private function createFavoritesForProfile(Profile $profile): void
    {
        $favoriteCount = rand(3, 10);
        
        for ($i = 0; $i < $favoriteCount; $i++) {
            Favorite::factory()
                ->activeProduct()
                ->create(['profile_id' => $profile->id]);
        }
    }

    /**
     * Crear códigos de operadora venezolanos
     */
    private function createOperatorCodes(): void
    {
        $this->command->info('📱 Creando códigos de operadora...');
        
        // Crear códigos principales de Venezuela
        $operatorCodes = [
            ['code' => '412', 'name' => 'Digitel'],
            ['code' => '414', 'name' => 'Movistar'],
            ['code' => '416', 'name' => 'Movistar'],
            ['code' => '424', 'name' => 'Movistar'],
            ['code' => '426', 'name' => 'Movistar'],
            ['code' => '428', 'name' => 'Digitel'],
            ['code' => '430', 'name' => 'Digitel'],
            ['code' => '432', 'name' => 'Digitel'],
            ['code' => '434', 'name' => 'Digitel'],
            ['code' => '436', 'name' => 'Digitel'],
            ['code' => '438', 'name' => 'Movilnet'],
            ['code' => '440', 'name' => 'Movilnet'],
            ['code' => '442', 'name' => 'Movilnet'],
            ['code' => '444', 'name' => 'Movilnet'],
            ['code' => '446', 'name' => 'Movilnet'],
        ];

        foreach ($operatorCodes as $operatorCode) {
            \App\Models\OperatorCode::firstOrCreate(
                ['code' => $operatorCode['code']],
                [
                    'name' => $operatorCode['name'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('✅ Códigos de operadora creados');
    }
}
