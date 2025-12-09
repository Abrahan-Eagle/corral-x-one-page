<?php

namespace Database\Seeders;

use App\Models\Profile;
use App\Models\Commerce;
use App\Models\DeliveryAgent;
use App\Models\DeliveryCompany;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\OrderItem;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Solo ejecutar seeders básicos y el seeder de Corral X
        $this->call([
            OperatorCodeSeeder::class,
            CountriesSeeder::class,
            StatesSeeder::class,
            CitiesSeeder::class,
            ParishesSeeder::class,
            //TestUsersSeeder::class,  // Descomentar para cargar usuarios de prueba
            //CorralXSeeder::class, // Seeder específico para el marketplace
        ]);
    }
}
