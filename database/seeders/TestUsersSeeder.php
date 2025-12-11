<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeder para datos de prueba de usuarios 1, 2 y 3
 * 
 * Este seeder extrae todos los datos relacionados con los usuarios 1, 2 y 3
 * del dump SQL para poder usarlos en tests.
 * 
 * Usuarios incluidos:
 * - Usuario 1: Wistremiro A Pulido B (wistremiropulido@gmail.com)
 * - Usuario 2: abrahan pulido (ing.pulido.abrahan@gmail.com)
 * - Usuario 3: Renny HF (rennyfurneri@gmail.com)
 * 
 * NOTA: Las URLs de imágenes se construyen dinámicamente según el entorno
 * (producción o local) usando APP_URL_PRODUCTION o APP_URL_LOCAL del .env
 */
class TestUsersSeeder extends Seeder
{
    /**
     * Obtener la URL base según el entorno
     */
    private function getBaseUrl(): string
    {
        return env('APP_ENV') === 'production'
            ? (env('APP_URL_PRODUCTION') ?: 'https://corralx.com')
            : (env('APP_URL_LOCAL') ?: 'http://localhost:8000');
    }

    /**
     * Construir URL completa para una ruta de storage
     */
    private function buildStorageUrl(string $relativePath): string
    {
        // Si ya es una URL completa, devolverla tal cual
        if (str_starts_with($relativePath, 'http://') || str_starts_with($relativePath, 'https://')) {
            // Extraer solo la ruta relativa después de /storage/
            if (preg_match('/\/storage\/(.+)$/', $relativePath, $matches)) {
                return $this->getBaseUrl() . '/storage/' . $matches[1];
            }
            return $relativePath;
        }

        // Si es una ruta relativa, construir la URL completa
        $path = ltrim($relativePath, '/');
        if (!str_starts_with($path, 'storage/')) {
            $path = 'storage/' . $path;
        }
        return $this->getBaseUrl() . '/' . $path;
    }
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Desactivar verificación de foreign keys temporalmente
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Limpiar datos existentes de estos usuarios (si existen)
        $this->cleanupExistingData();

        // Insertar datos en orden de dependencias
        $this->seedUsers();
        $this->seedProfiles();
        $this->seedAddresses();
        $this->seedRanches();
        $this->seedProducts();
        $this->seedProductImages();
        $this->seedProductCategories();
        $this->seedPhones();
        $this->seedConversations();
        $this->seedMessages();
        $this->seedOrders();
        $this->seedReviews();
        $this->seedFavorites();
        $this->seedIAInsights();

        // Reactivar verificación de foreign keys
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Limpiar datos existentes de los usuarios 1, 2 y 3
     */
    private function cleanupExistingData(): void
    {
        // Obtener profile_ids de los usuarios
        $profileIds = DB::table('profiles')
            ->whereIn('user_id', [1, 2, 3])
            ->pluck('id')
            ->toArray();

        if (empty($profileIds)) {
            return;
        }

        // Obtener ranch_ids de los perfiles
        $ranchIds = DB::table('ranches')
            ->whereIn('profile_id', $profileIds)
            ->pluck('id')
            ->toArray();

        // Obtener product_ids de los ranches
        $productIds = DB::table('products')
            ->whereIn('ranch_id', $ranchIds)
            ->pluck('id')
            ->toArray();

        // Obtener conversation_ids
        $conversationIds = DB::table('conversations')
            ->whereIn('profile_id_1', $profileIds)
            ->orWhereIn('profile_id_2', $profileIds)
            ->pluck('id')
            ->toArray();

        // Obtener order_ids
        $orderIds = DB::table('orders')
            ->whereIn('buyer_profile_id', $profileIds)
            ->orWhereIn('seller_profile_id', $profileIds)
            ->pluck('id')
            ->toArray();

        // Eliminar en orden inverso de dependencias
        DB::table('reviews')->whereIn('order_id', $orderIds)->delete();
        DB::table('reviews')->whereIn('profile_id', $profileIds)->delete();
        DB::table('reviews')->whereIn('product_id', $productIds)->delete();
        DB::table('favorites')->whereIn('profile_id', $profileIds)->delete();
        DB::table('favorites')->whereIn('product_id', $productIds)->delete();
        DB::table('messages')->whereIn('conversation_id', $conversationIds)->delete();
        DB::table('orders')->whereIn('id', $orderIds)->delete();
        DB::table('conversations')->whereIn('id', $conversationIds)->delete();
        DB::table('product_categories')->whereIn('product_id', $productIds)->delete();
        DB::table('product_images')->whereIn('product_id', $productIds)->delete();
        DB::table('products')->whereIn('id', $productIds)->delete();
        DB::table('phones')->whereIn('profile_id', $profileIds)->delete();
        DB::table('phones')->whereIn('ranch_id', $ranchIds)->delete();
        DB::table('addresses')->whereIn('profile_id', $profileIds)->delete();
        DB::table('addresses')->whereIn('ranch_id', $ranchIds)->delete();
        DB::table('ranches')->whereIn('id', $ranchIds)->delete();
        DB::table('profiles')->whereIn('id', $profileIds)->delete();
        DB::table('ia_insight_user_recommendations')->whereIn('user_id', [1, 2, 3])->delete();
        DB::table('users')->whereIn('id', [1, 2, 3])->delete();
    }

    /**
     * Insertar usuarios
     */
    private function seedUsers(): void
    {
        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Wistremiro A Pulido B',
                'email' => 'wistremiropulido@gmail.com',
                'email_verified_at' => null,
                'password' => null,
                'google_id' => '107212919897356810816',
                'given_name' => 'Wistremiro A',
                'family_name' => 'Pulido B',
                'profile_pic' => 'https://lh3.googleusercontent.com/a/ACg8ocKgWH29et0okV9S-wV6quri0609QRDbCoqH_C2OmUKMl_mi5Q=s96-c',
                'AccessToken' => null,
                'completed_onboarding' => 1,
                'role' => 'users',
                'remember_token' => null,
                'created_at' => '2025-11-28 22:06:48',
                'updated_at' => '2025-11-28 22:08:05',
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'name' => 'abrahan pulido',
                'email' => 'ing.pulido.abrahan@gmail.com',
                'email_verified_at' => null,
                'password' => null,
                'google_id' => '111890855875234910207',
                'given_name' => 'abrahan',
                'family_name' => 'pulido',
                'profile_pic' => 'https://lh3.googleusercontent.com/a/ACg8ocIuLGJWAUiZXz3X-UKcCtla9yqtb8nK0sTu_33NkIv2O1x5d5-E=s96-c',
                'AccessToken' => null,
                'completed_onboarding' => 1,
                'role' => 'users',
                'remember_token' => null,
                'created_at' => '2025-11-28 22:10:01',
                'updated_at' => '2025-11-28 22:10:52',
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'name' => 'Renny HF',
                'email' => 'rennyfurneri@gmail.com',
                'email_verified_at' => null,
                'password' => null,
                'google_id' => '114649311760105800487',
                'given_name' => 'Renny',
                'family_name' => 'HF',
                'profile_pic' => 'https://lh3.googleusercontent.com/a/ACg8ocKf0yDumV9ukhf4Y2VB0VUQS6nd6Jy30ljEm1CaDNOyBQSG4KW1Fw=s96-c',
                'AccessToken' => null,
                'completed_onboarding' => 1,
                'role' => 'users',
                'remember_token' => null,
                'created_at' => '2025-12-02 06:37:55',
                'updated_at' => '2025-12-02 06:39:31',
                'deleted_at' => null,
            ],
        ]);
    }

    /**
     * Insertar perfiles
     */
    private function seedProfiles(): void
    {
        DB::table('profiles')->insert([
            [
                'id' => 1,
                'user_id' => 1,
                'firstName' => 'Comprador Cliente',
                'middleName' => 'Cxx',
                'lastName' => 'Usuarios',
                'secondLastName' => 'Cxx',
                'photo_users' => $this->buildStorageUrl('http://192.168.27.12:8000/storage/profile_images/cGlzhRsVuyKkDKFdv8H4c5OKq1ytgtTCsFUAldAO.jpg'),
                'bio' => 'hola mamá bendición como estás que haces en la casa de',
                'date_of_birth' => '2005-12-03',
                'maritalStatus' => 'single',
                'sex' => 'M',
                'status' => 'notverified',
                'ranch' => null,
                'phone' => null,
                'address' => null,
                'is_verified' => 0,
                'rating' => 0.00,
                'ratings_count' => 0,
                'has_unread_messages' => 0,
                'user_type' => 'both',
                'is_both_verified' => 0,
                'accepts_calls' => 1,
                'accepts_whatsapp' => 1,
                'accepts_emails' => 1,
                'whatsapp_number' => null,
                'is_premium_seller' => 0,
                'premium_expires_at' => null,
                'ci_number' => 'V-19217553',
                'fcm_device_token' => null,
                // Campos KYC
                'kyc_status' => 'no_verified',
                'kyc_rejection_reason' => null,
                'kyc_document_type' => null,
                'kyc_document_number' => null,
                'kyc_country_code' => null,
                'kyc_doc_front_path' => null,
                'kyc_rif_path' => null,
                'kyc_selfie_path' => null,
                'kyc_selfie_with_doc_path' => null,
                'kyc_liveness_selfies_paths' => null,
                'kyc_verified_at' => null,
                'created_at' => '2025-11-28 22:08:05',
                'updated_at' => '2025-11-28 22:09:11',
            ],
            [
                'id' => 2,
                'user_id' => 2,
                'firstName' => 'Hacienda Rancho',
                'middleName' => 'Ven',
                'lastName' => 'Vendedor',
                'secondLastName' => 'De',
                'photo_users' => $this->buildStorageUrl('http://192.168.27.12:8000/storage/profile_images/NjKLwIdQnJq5vlVHOqwQqQGeHJhzOdmXD6Xk2Y7s.jpg'),
                'bio' => 'B a la casa de la casa de la casa de la casa de la casa de la casa de la casa de',
                'date_of_birth' => '2005-12-03',
                'maritalStatus' => 'married',
                'sex' => 'M',
                'status' => 'notverified',
                'ranch' => null,
                'phone' => null,
                'address' => null,
                'is_verified' => 0,
                'rating' => 0.00,
                'ratings_count' => 0,
                'has_unread_messages' => 0,
                'user_type' => 'both',
                'is_both_verified' => 0,
                'accepts_calls' => 1,
                'accepts_whatsapp' => 1,
                'accepts_emails' => 1,
                'whatsapp_number' => null,
                'is_premium_seller' => 0,
                'premium_expires_at' => null,
                'ci_number' => 'V-64646491',
                'fcm_device_token' => null,
                // Campos KYC - Pendiente de verificación
                'kyc_status' => 'pending',
                'kyc_rejection_reason' => null,
                'kyc_document_type' => 'ci_ve',
                'kyc_document_number' => 'V-64646491',
                'kyc_country_code' => 'VE',
                'kyc_doc_front_path' => 'kyc/documents/front_user2.jpg',
                'kyc_rif_path' => 'kyc/documents/rif_user2.jpg',
                'kyc_selfie_path' => 'kyc/selfies/selfie_user2.jpg',
                'kyc_selfie_with_doc_path' => null, // Falta este paso
                'kyc_liveness_selfies_paths' => null,
                'kyc_verified_at' => null,
                'created_at' => '2025-11-28 22:10:52',
                'updated_at' => '2025-11-28 22:11:36',
            ],
            [
                'id' => 3,
                'user_id' => 3,
                'firstName' => 'Renny',
                'middleName' => 'Rene',
                'lastName' => 'Furneri',
                'secondLastName' => 'Hernandez',
                'photo_users' => $this->buildStorageUrl('https://corralx.com/storage/profile_images/Sbk2nU5TDv279AWrrPWfLBzbcCXg3oUSF0jtVdoq.jpg'),
                'bio' => '20 años de experiencia',
                'date_of_birth' => '1985-07-08',
                'maritalStatus' => 'single',
                'sex' => 'M',
                'status' => 'notverified',
                'ranch' => null,
                'phone' => null,
                'address' => null,
                'is_verified' => 0,
                'rating' => 0.00,
                'ratings_count' => 0,
                'has_unread_messages' => 0,
                'user_type' => 'both',
                'is_both_verified' => 0,
                'accepts_calls' => 1,
                'accepts_whatsapp' => 1,
                'accepts_emails' => 1,
                'whatsapp_number' => null,
                'is_premium_seller' => 0,
                'premium_expires_at' => null,
                'ci_number' => 'V-17515747',
                'fcm_device_token' => 'eK2lTlZ4Tz-ebzbOSlh7OU:APA91bE0RkZERjCD2NmyhlRd7-gpsVYNcbKOuT0VdThDHFaC_4bp3VhrwOcqY8lT1tKTPwXJhpW10FK7dWtGYBmgbTZzpSJGZxvqJfExOLtkp3pSUIIKO20',
                // Campos KYC - Verificado completamente
                'kyc_status' => 'verified',
                'kyc_rejection_reason' => null,
                'kyc_document_type' => 'ci_ve',
                'kyc_document_number' => 'V-17515747',
                'kyc_country_code' => 'VE',
                'kyc_doc_front_path' => 'kyc/documents/front_user3.jpg',
                'kyc_rif_path' => 'kyc/documents/rif_user3.jpg',
                'kyc_selfie_path' => 'kyc/selfies/selfie_user3.jpg',
                'kyc_selfie_with_doc_path' => 'kyc/selfies/selfie_with_doc_user3.jpg',
                'kyc_liveness_selfies_paths' => json_encode([
                    'kyc/3/liveness_1.jpg',
                    'kyc/3/liveness_2.jpg',
                    'kyc/3/liveness_3.jpg',
                    'kyc/3/liveness_4.jpg',
                    'kyc/3/liveness_5.jpg',
                ]),
                'kyc_verified_at' => '2025-12-02 07:00:00',
                'created_at' => '2025-12-02 06:39:29',
                'updated_at' => '2025-12-02 07:15:30',
            ],
        ]);
    }

    /**
     * Insertar direcciones
     * Nota: Los ranches también usan estas direcciones (address_id en ranches)
     */
    private function seedAddresses(): void
    {
        DB::table('addresses')->insert([
            [
                'id' => 1,
                'level' => 'users',
                'adressses' => 'el socorro',
                'latitude' => 10.1252975,
                'longitude' => -68.0512946,
                'status' => 'notverified',
                'created_at' => '2025-11-28 22:08:05',
                'updated_at' => '2025-11-28 22:09:42',
                'profile_id' => 1,
                'ranch_id' => null, // Los ranches apuntan a las direcciones mediante address_id, no al revés
                'city_id' => 47294,
                'parish_id' => null,
            ],
            [
                'id' => 2,
                'level' => 'users',
                'adressses' => 'el socorro',
                'latitude' => 10.1253090,
                'longitude' => -68.0512910,
                'status' => 'notverified',
                'created_at' => '2025-11-28 22:10:52',
                'updated_at' => '2025-11-28 22:12:15',
                'profile_id' => 2,
                'ranch_id' => null, // Los ranches apuntan a las direcciones mediante address_id, no al revés
                'city_id' => 47293,
                'parish_id' => null,
            ],
            [
                'id' => 3,
                'level' => 'users',
                'adressses' => 'las acacias',
                'latitude' => 10.2021103,
                'longitude' => -68.0038002,
                'status' => 'notverified',
                'created_at' => '2025-12-02 06:39:30',
                'updated_at' => '2025-12-02 06:39:30',
                'profile_id' => 3,
                'ranch_id' => null,
                'city_id' => 47294,
                'parish_id' => null,
            ],
            [
                'id' => 4,
                'level' => 'ranches',
                'adressses' => 'loma linda',
                'latitude' => 10.2021423,
                'longitude' => -68.0037877,
                'status' => 'notverified',
                'created_at' => '2025-12-02 06:51:11',
                'updated_at' => '2025-12-02 06:55:15',
                'profile_id' => 3,
                'ranch_id' => null,
                'city_id' => 48369,
                'parish_id' => null,
            ],
        ]);
    }

    /**
     * Insertar haciendas
     */
    private function seedRanches(): void
    {
        DB::table('ranches')->insert([
            [
                'id' => 1,
                'profile_id' => 1,
                'name' => 'Comprá',
                'legal_name' => 'Comprq',
                'tax_id' => 'V-83737372-7',
                'business_description' => 'hola mamá bendición como estás que haces en la casa de mi mamá dijo que',
                'certifications' => json_encode(['Libre de Tuberculosis']),
                'business_license_url' => null,
                'address_id' => 1,
                'is_primary' => 1,
                'delivery_policy' => 'hola mamá bendición como estás que haces en',
                'return_policy' => 'y tú cómo estás mi vida como estás que haces en la casa de',
                'accepts_visits' => 0,
                'contact_hours' => '24/7 Disponible',
                'avg_rating' => 0.00,
                'total_sales' => 0,
                'last_sale_at' => null,
                'created_at' => '2025-11-28 22:08:05',
                'updated_at' => '2025-11-28 22:09:42',
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'profile_id' => 2,
                'name' => 'Hacienda',
                'legal_name' => 'Hacienda',
                'tax_id' => 'J-83727372-7',
                'business_description' => 'hshshshshsh de la casa de la casa de la casa de la',
                'certifications' => json_encode(['Libre de Tuberculosis']),
                'business_license_url' => null,
                'address_id' => 2,
                'is_primary' => 1,
                'delivery_policy' => 'hola buenos días como estas javier cómo estás que haces',
                'return_policy' => 'hola buenos días como estas javier cómo estás que haces',
                'accepts_visits' => 0,
                'contact_hours' => '24/7 Disponible',
                'avg_rating' => 0.00,
                'total_sales' => 0,
                'last_sale_at' => null,
                'created_at' => '2025-11-28 22:10:52',
                'updated_at' => '2025-11-28 22:12:15',
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'profile_id' => 3,
                'name' => 'Los Aguacates De Dios',
                'legal_name' => 'Quantum Nexus',
                'tax_id' => 'V-17515747-2',
                'business_description' => 'los mejores aguacates de Venezuela',
                'certifications' => null,
                'business_license_url' => null,
                'address_id' => 3,
                'is_primary' => 0,
                'delivery_policy' => null,
                'return_policy' => null,
                'accepts_visits' => 0,
                'contact_hours' => null,
                'avg_rating' => 4.33,
                'total_sales' => 0,
                'last_sale_at' => null,
                'created_at' => '2025-12-02 06:39:30',
                'updated_at' => '2025-12-02 07:13:45',
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'profile_id' => 3,
                'name' => 'los aguacates de Dios 2',
                'legal_name' => 'Quantum Nexus C.A',
                'tax_id' => 'J-17515747-2',
                'business_description' => 'seguimos sembrando el futuro',
                'certifications' => json_encode(['Certificación Orgánica', 'Buenas Prácticas Ganaderas (BPG)', 'SENASICA', 'Libre de Brucelosis', 'Libre de Tuberculosis']),
                'business_license_url' => null,
                'address_id' => 4,
                'is_primary' => 1,
                'delivery_policy' => 'después del pago',
                'return_policy' => 'no hay devoluciones',
                'accepts_visits' => 0,
                'contact_hours' => 'Lunes a Viernes 8:00 AM - 5:00 PM',
                'avg_rating' => 0.00,
                'total_sales' => 0,
                'last_sale_at' => null,
                'created_at' => '2025-12-02 06:51:11',
                'updated_at' => '2025-12-02 06:51:11',
                'deleted_at' => null,
            ],
        ]);
    }

    /**
     * Insertar productos
     */
    private function seedProducts(): void
    {
        DB::table('products')->insert([
            [
                'id' => 1,
                'ranch_id' => 2,
                'state_id' => 4026,
                'title' => 'la vaca negra',
                'description' => 'hola buenos días como estas javier cómo estás que haces yyyyyyyyyyyyyyyyyh años hhh que',
                'type' => 'lechero',
                'breed' => 'Brahman',
                'age' => 99,
                'quantity' => 1000,
                'price' => 300.00,
                'currency' => 'USD',
                'status' => 'active',
                'weight_avg' => 999.00,
                'weight_min' => 849.15,
                'weight_max' => 1148.85,
                'sex' => 'male',
                'purpose' => 'meat',
                'feeding_type' => 'pastura_natural', // ✅ NUEVO: tipo de alimento obligatorio
                'health_certificate_url' => null,
                'vaccines_applied' => null,
                'last_vaccination' => null,
                'is_vaccinated' => 1,
                'feeding_info' => null,
                'handling_info' => null,
                'origin_farm' => null,
                'available_from' => null,
                'available_until' => null,
                'delivery_method' => 'pickup',
                'delivery_cost' => null,
                'delivery_radius_km' => null,
                'price_type' => 'per_unit',
                'negotiable' => 1,
                'min_order_quantity' => null,
                'is_featured' => 1,
                'views' => 0,
                'transportation_included' => 'negotiable',
                'documentation_included' => 'true',
                'genetic_tests_available' => 0,
                'genetic_test_results' => null,
                'bloodline' => null,
                'created_at' => '2025-11-28 22:13:18',
                'updated_at' => '2025-11-28 22:13:18',
            ],
        ]);
    }

    /**
     * Insertar imágenes de productos
     */
    private function seedProductImages(): void
    {
        DB::table('product_images')->insert([
            [
                'id' => 1,
                'product_id' => 1,
                'file_url' => $this->buildStorageUrl('http://192.168.27.12:8000/storage/product_images/h2n22ZWhHP1rArXwm29HWRTBAgIGmYcgLADh3vBe.jpg'),
                'file_type' => 'image',
                'alt_text' => null,
                'is_primary' => 1,
                'sort_order' => 1,
                'duration' => null,
                'file_size' => 345958,
                'resolution' => null,
                'format' => 'jpg',
                'compression' => null,
                'created_at' => '2025-11-28 22:13:18',
                'updated_at' => '2025-11-28 22:13:18',
            ],
            [
                'id' => 2,
                'product_id' => 2,
                'file_url' => $this->buildStorageUrl('https://corralx.com/storage/product_images/K8QOzOgmgf5ETQdZCTzvAWYsqhWwN5Q9FcqVmqmZ.jpg'),
                'file_type' => 'image',
                'alt_text' => null,
                'is_primary' => 1,
                'sort_order' => 1,
                'duration' => null,
                'file_size' => 110652,
                'resolution' => null,
                'format' => 'jpg',
                'compression' => null,
                'created_at' => '2025-12-02 07:03:25',
                'updated_at' => '2025-12-02 07:03:25',
            ],
        ]);
    }

    /**
     * Insertar categorías de productos
     */
    private function seedProductCategories(): void
    {
        // Si hay categorías en el SQL, insertarlas aquí
        // Por ahora está vacío según el grep anterior
    }

    /**
     * Insertar teléfonos
     */
    private function seedPhones(): void
    {
        DB::table('phones')->insert([
            [
                'id' => 1,
                'profile_id' => 1,
                'ranch_id' => null,
                'operator_code_id' => 1,
                'number' => '1234545',
                'is_primary' => 1,
                'status' => 1,
                'created_at' => '2025-11-28 22:08:05',
                'updated_at' => '2025-11-28 22:08:05',
            ],
            [
                'id' => 2,
                'profile_id' => 2,
                'ranch_id' => null,
                'operator_code_id' => 3,
                'number' => '3434646',
                'is_primary' => 1,
                'status' => 1,
                'created_at' => '2025-11-28 22:10:52',
                'updated_at' => '2025-11-28 22:10:52',
            ],
            [
                'id' => 3,
                'profile_id' => 3,
                'ranch_id' => null,
                'operator_code_id' => 3,
                'number' => '0433205',
                'is_primary' => 1,
                'status' => 1,
                'created_at' => '2025-12-02 06:39:29',
                'updated_at' => '2025-12-02 06:39:29',
            ],
        ]);
    }

    /**
     * Insertar conversaciones
     */
    private function seedConversations(): void
    {
        DB::table('conversations')->insert([
            [
                'id' => 3,
                'profile_id_1' => 2,
                'profile_id_2' => 3,
                'product_id' => 2,
                'ranch_id' => null,
                'last_message_at' => '2025-12-02 07:36:35',
                'is_active' => 1,
                'created_at' => '2025-12-02 07:32:05',
                'updated_at' => '2025-12-02 07:36:35',
            ],
        ]);
    }

    /**
     * Insertar mensajes
     */
    private function seedMessages(): void
    {
        DB::table('messages')->insert([
            [
                'id' => 12,
                'conversation_id' => 3,
                'sender_id' => 2, // profile_id 2
                'content' => 'Hola',
                'message_type' => 'text',
                'attachment_url' => null,
                'read_at' => '2025-12-02 07:33:24',
                'is_deleted' => 0,
                'created_at' => '2025-12-02 07:32:09',
                'updated_at' => '2025-12-02 07:33:24',
            ],
            [
                'id' => 13,
                'conversation_id' => 3,
                'sender_id' => 3, // profile_id 3
                'content' => 'Hola',
                'message_type' => 'text',
                'attachment_url' => null,
                'read_at' => '2025-12-02 07:37:50',
                'is_deleted' => 0,
                'created_at' => '2025-12-02 07:35:11',
                'updated_at' => '2025-12-02 07:37:50',
            ],
            [
                'id' => 14,
                'conversation_id' => 3,
                'sender_id' => 2, // profile_id 2
                'content' => 'Hi',
                'message_type' => 'text',
                'attachment_url' => null,
                'read_at' => '2025-12-02 07:35:29',
                'is_deleted' => 0,
                'created_at' => '2025-12-02 07:35:28',
                'updated_at' => '2025-12-02 07:35:29',
            ],
            [
                'id' => 15,
                'conversation_id' => 3,
                'sender_id' => 3, // profile_id 3
                'content' => 'No me sale pa comprar',
                'message_type' => 'text',
                'attachment_url' => null,
                'read_at' => '2025-12-02 07:37:50',
                'is_deleted' => 0,
                'created_at' => '2025-12-02 07:35:38',
                'updated_at' => '2025-12-02 07:37:50',
            ],
            [
                'id' => 16,
                'conversation_id' => 3,
                'sender_id' => 3, // profile_id 3
                'content' => 'Estafador',
                'message_type' => 'text',
                'attachment_url' => null,
                'read_at' => '2025-12-02 07:37:50',
                'is_deleted' => 0,
                'created_at' => '2025-12-02 07:35:44',
                'updated_at' => '2025-12-02 07:37:50',
            ],
            [
                'id' => 17,
                'conversation_id' => 3,
                'sender_id' => 2, // profile_id 2
                'content' => 'Yo estoy comprando',
                'message_type' => 'text',
                'attachment_url' => null,
                'read_at' => null,
                'is_deleted' => 0,
                'created_at' => '2025-12-02 07:36:24',
                'updated_at' => '2025-12-02 07:36:24',
            ],
            [
                'id' => 18,
                'conversation_id' => 3,
                'sender_id' => 2, // profile_id 2
                'content' => 'Está vez',
                'message_type' => 'text',
                'attachment_url' => null,
                'read_at' => null,
                'is_deleted' => 0,
                'created_at' => '2025-12-02 07:36:30',
                'updated_at' => '2025-12-02 07:36:30',
            ],
            [
                'id' => 19,
                'conversation_id' => 3,
                'sender_id' => 3, // profile_id 3
                'content' => 'Ok',
                'message_type' => 'text',
                'attachment_url' => null,
                'read_at' => '2025-12-02 07:37:50',
                'is_deleted' => 0,
                'created_at' => '2025-12-02 07:36:35',
                'updated_at' => '2025-12-02 07:37:50',
            ],
        ]);
    }

    /**
     * Insertar pedidos
     */
    private function seedOrders(): void
    {
        DB::table('orders')->insert([
            [
                'id' => 1,
                'product_id' => 2,
                'buyer_profile_id' => 2,
                'seller_profile_id' => 3,
                'conversation_id' => 1,
                'ranch_id' => 3,
                'quantity' => 1,
                'unit_price' => 1500.00,
                'total_price' => 1500.00,
                'currency' => 'USD',
                'status' => 'completed',
                'delivery_method' => 'buyer_transport',
                'pickup_location' => 'ranch',
                'pickup_address' => 'las acacias, Valencia, Carabobo',
                'delivery_address' => null,
                'pickup_notes' => 'hola',
                'delivery_cost' => 0.00,
                'delivery_cost_currency' => 'USD',
                'delivery_provider' => null,
                'delivery_tracking_number' => null,
                'expected_pickup_date' => '2025-12-07',
                'actual_pickup_date' => '2025-12-02',
                'buyer_notes' => 'hola',
                'seller_notes' => null,
                'receipt_number' => 'CORRALX-00000001-20251202',
                'receipt_data' => json_encode([
                    'receipt_number' => 'CORRALX-00000001-20251202',
                    'issue_date' => '2025-12-02 02:13:17',
                    'seller' => [
                        'name' => 'Renny Rene Furneri Hernandez',
                        'ranch_name' => 'Los Aguacates De Dios',
                        'legal_name' => 'Quantum Nexus',
                        'tax_id' => 'V-17515747-2',
                        'address' => 'las acacias, Valencia, Carabobo, Venezuela',
                        'phone' => null,
                        'email' => 'rennyfurneri@gmail.com',
                    ],
                    'buyer' => [
                        'name' => 'Hacienda Rancho Ven Vendedor De',
                        'ci_number' => 'V-64646491',
                        'address' => null,
                    ],
                    'product' => [
                        'title' => 'mi niña',
                        'type' => 'engorde',
                        'breed' => 'Gyr',
                        'quantity' => 1,
                        'unit_price' => '1500.00',
                        'total_price' => '1500.00',
                        'currency' => 'USD',
                    ],
                ]),
                'accepted_at' => '2025-12-02 07:13:17',
                'rejected_at' => null,
                'delivered_at' => '2025-12-02 07:13:31',
                'completed_at' => '2025-12-02 07:13:45',
                'cancelled_at' => null,
                'created_at' => '2025-12-02 07:09:37',
                'updated_at' => '2025-12-02 07:13:45',
            ],
            [
                'id' => 2,
                'product_id' => 1,
                'buyer_profile_id' => 3,
                'seller_profile_id' => 2,
                'conversation_id' => 2,
                'ranch_id' => 2,
                'quantity' => 1,
                'unit_price' => 300.00,
                'total_price' => 300.00,
                'currency' => 'USD',
                'status' => 'completed',
                'delivery_method' => 'buyer_transport',
                'pickup_location' => 'ranch',
                'pickup_address' => 'el socorro, Tacarigua, Carabobo',
                'delivery_address' => null,
                'pickup_notes' => null,
                'delivery_cost' => 0.00,
                'delivery_cost_currency' => 'USD',
                'delivery_provider' => null,
                'delivery_tracking_number' => null,
                'expected_pickup_date' => '2025-12-02',
                'actual_pickup_date' => '2025-12-02',
                'buyer_notes' => null,
                'seller_notes' => null,
                'receipt_number' => 'CORRALX-00000002-20251202',
                'receipt_data' => json_encode([
                    'receipt_number' => 'CORRALX-00000002-20251202',
                    'issue_date' => '2025-12-02 02:17:12',
                    'seller' => [
                        'name' => 'Hacienda Rancho Ven Vendedor De',
                        'ranch_name' => 'Hacienda',
                        'legal_name' => 'Hacienda',
                        'tax_id' => 'J-83727372-7',
                        'address' => 'el socorro, Tacarigua, Carabobo, Venezuela',
                        'phone' => null,
                        'email' => 'ing.pulido.abrahan@gmail.com',
                    ],
                    'buyer' => [
                        'name' => 'Renny Rene Furneri Hernandez',
                        'ci_number' => 'V-17515747',
                        'address' => null,
                    ],
                    'product' => [
                        'title' => 'la vaca negra',
                        'type' => 'lechero',
                        'breed' => 'Brahman',
                        'quantity' => 1,
                        'unit_price' => '300.00',
                        'total_price' => '300.00',
                        'currency' => 'USD',
                    ],
                ]),
                'accepted_at' => '2025-12-02 07:17:12',
                'rejected_at' => null,
                'delivered_at' => '2025-12-02 07:17:34',
                'completed_at' => '2025-12-02 07:17:51',
                'cancelled_at' => null,
                'created_at' => '2025-12-02 07:17:05',
                'updated_at' => '2025-12-02 07:17:51',
            ],
            [
                'id' => 3,
                'product_id' => 1,
                'buyer_profile_id' => 3,
                'seller_profile_id' => 2,
                'conversation_id' => 2,
                'ranch_id' => 2,
                'quantity' => 1,
                'unit_price' => 300.00,
                'total_price' => 300.00,
                'currency' => 'USD',
                'status' => 'rejected',
                'delivery_method' => 'buyer_transport',
                'pickup_location' => 'other',
                'pickup_address' => 'Calle Los Cedros, Lomas del Este, Valencia, Carabobo, Venezuela',
                'delivery_address' => null,
                'pickup_notes' => null,
                'delivery_cost' => 0.00,
                'delivery_cost_currency' => 'USD',
                'delivery_provider' => null,
                'delivery_tracking_number' => null,
                'expected_pickup_date' => '2025-12-11',
                'actual_pickup_date' => null,
                'buyer_notes' => null,
                'seller_notes' => 'Motivo: y eso xq tan',
                'receipt_number' => null,
                'receipt_data' => null,
                'accepted_at' => null,
                'rejected_at' => '2025-12-02 07:27:20',
                'delivered_at' => null,
                'completed_at' => null,
                'cancelled_at' => null,
                'created_at' => '2025-12-02 07:24:55',
                'updated_at' => '2025-12-02 07:27:20',
            ],
            [
                'id' => 4,
                'product_id' => 2,
                'buyer_profile_id' => 2,
                'seller_profile_id' => 3,
                'conversation_id' => 3,
                'ranch_id' => 3,
                'quantity' => 1,
                'unit_price' => 1500.00,
                'total_price' => 1500.00,
                'currency' => 'USD',
                'status' => 'delivered',
                'delivery_method' => 'external_delivery',
                'pickup_location' => 'ranch',
                'pickup_address' => null,
                'delivery_address' => 'Valencia, Carabobo, Venezuela',
                'pickup_notes' => null,
                'delivery_cost' => 0.00,
                'delivery_cost_currency' => 'USD',
                'delivery_provider' => 'mrw',
                'delivery_tracking_number' => 'kdjdididkd',
                'expected_pickup_date' => '2025-12-31',
                'actual_pickup_date' => '2025-12-02',
                'buyer_notes' => null,
                'seller_notes' => null,
                'receipt_number' => 'CORRALX-00000004-20251202',
                'receipt_data' => json_encode([
                    'receipt_number' => 'CORRALX-00000004-20251202',
                    'issue_date' => '2025-12-02 02:38:20',
                    'seller' => [
                        'name' => 'Renny Rene Furneri Hernandez',
                        'ranch_name' => 'Los Aguacates De Dios',
                        'legal_name' => 'Quantum Nexus',
                        'tax_id' => 'V-17515747-2',
                        'address' => 'las acacias, Valencia, Carabobo, Venezuela',
                        'phone' => null,
                        'email' => 'rennyfurneri@gmail.com',
                    ],
                    'buyer' => [
                        'name' => 'Hacienda Rancho Ven Vendedor De',
                        'ci_number' => 'V-64646491',
                        'address' => 'Valencia, Carabobo, Venezuela',
                    ],
                    'product' => [
                        'title' => 'mi niña',
                        'type' => 'engorde',
                        'breed' => 'Gyr',
                        'quantity' => 1,
                        'unit_price' => '1500.00',
                        'total_price' => '1500.00',
                        'currency' => 'USD',
                    ],
                ]),
                'accepted_at' => '2025-12-02 07:38:20',
                'rejected_at' => null,
                'delivered_at' => '2025-12-02 07:43:42',
                'completed_at' => null,
                'cancelled_at' => null,
                'created_at' => '2025-12-02 07:36:05',
                'updated_at' => '2025-12-02 07:43:42',
            ],
        ]);
    }

    /**
     * Insertar reseñas
     */
    private function seedReviews(): void
    {
        DB::table('reviews')->insert([
            [
                'id' => 1,
                'order_id' => 1,
                'profile_id' => 3,
                'product_id' => 2,
                'ranch_id' => 3,
                'rating' => 3,
                'comment' => null,
                'is_verified_purchase' => 1,
                'is_approved' => 1,
                'created_at' => '2025-12-02 07:13:44',
                'updated_at' => '2025-12-02 07:13:44',
            ],
            [
                'id' => 2,
                'order_id' => 1,
                'profile_id' => 2,
                'product_id' => 2,
                'ranch_id' => 3,
                'rating' => 5,
                'comment' => 'hola',
                'is_verified_purchase' => 1,
                'is_approved' => 1,
                'created_at' => '2025-12-02 07:13:45',
                'updated_at' => '2025-12-02 07:13:45',
            ],
            [
                'id' => 3,
                'order_id' => 1,
                'profile_id' => 2,
                'product_id' => null,
                'ranch_id' => 3,
                'rating' => 5,
                'comment' => 'hola',
                'is_verified_purchase' => 1,
                'is_approved' => 1,
                'created_at' => '2025-12-02 07:13:45',
                'updated_at' => '2025-12-02 07:13:45',
            ],
            [
                'id' => 4,
                'order_id' => 2,
                'profile_id' => 2,
                'product_id' => 1,
                'ranch_id' => 2,
                'rating' => 5,
                'comment' => 'hola',
                'is_verified_purchase' => 1,
                'is_approved' => 1,
                'created_at' => '2025-12-02 07:17:43',
                'updated_at' => '2025-12-02 07:17:43',
            ],
            [
                'id' => 5,
                'order_id' => 2,
                'profile_id' => 3,
                'product_id' => 1,
                'ranch_id' => 2,
                'rating' => 4,
                'comment' => null,
                'is_verified_purchase' => 1,
                'is_approved' => 1,
                'created_at' => '2025-12-02 07:17:51',
                'updated_at' => '2025-12-02 07:17:51',
            ],
            [
                'id' => 6,
                'order_id' => 2,
                'profile_id' => 3,
                'product_id' => null,
                'ranch_id' => 2,
                'rating' => 3,
                'comment' => null,
                'is_verified_purchase' => 1,
                'is_approved' => 1,
                'created_at' => '2025-12-02 07:17:51',
                'updated_at' => '2025-12-02 07:17:51',
            ],
            [
                'id' => 7,
                'order_id' => 4,
                'profile_id' => 2,
                'product_id' => 2,
                'ranch_id' => 3,
                'rating' => 5,
                'comment' => 'productos',
                'is_verified_purchase' => 1,
                'is_approved' => 1,
                'created_at' => '2025-12-02 07:44:05',
                'updated_at' => '2025-12-02 07:44:05',
            ],
            [
                'id' => 8,
                'order_id' => 4,
                'profile_id' => 2,
                'product_id' => null,
                'ranch_id' => 3,
                'rating' => 5,
                'comment' => 'vendedor',
                'is_verified_purchase' => 1,
                'is_approved' => 1,
                'created_at' => '2025-12-02 07:44:05',
                'updated_at' => '2025-12-02 07:44:05',
            ],
        ]);
    }

    /**
     * Insertar favoritos
     */
    private function seedFavorites(): void
    {
        // Si hay favoritos en el SQL, insertarlos aquí
        // Buscar favoritos donde profile_id = 1 o 2
    }

    /**
     * Insertar recomendaciones IA
     */
    private function seedIAInsights(): void
    {
        // Si hay recomendaciones IA en el SQL, insertarlas aquí
        // Buscar recomendaciones donde user_id = 1 o 2
    }
}

