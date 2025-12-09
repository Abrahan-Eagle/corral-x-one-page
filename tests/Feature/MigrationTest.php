<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MigrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test que todas las tablas principales existen
     */
    public function test_all_main_tables_exist(): void
    {
        $expectedTables = [
            'users',
            'profiles',
            'ranches',
            'products',
            'product_images',
            'favorites',
            'reviews',
            'conversations',
            'messages',
            'reports',
            'addresses',
            'phones',
            'categories',
            'product_categories',
            'operator_codes',
            'countries',
            'states',
            'cities',
        ];

        foreach ($expectedTables as $table) {
            $this->assertTrue(
                Schema::hasTable($table),
                "La tabla {$table} no existe"
            );
        }
    }

    /**
     * Test que la tabla users tiene las columnas correctas
     */
    public function test_users_table_has_correct_columns(): void
    {
        $expectedColumns = [
            'id',
            'name',
            'email',
            'password',
            'google_id',
            'given_name',
            'family_name',
            'profile_pic',
            'AccessToken',
            'completed_onboarding',
            'role',
            'remember_token',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        foreach ($expectedColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('users', $column),
                "La columna {$column} no existe en la tabla users"
            );
        }
    }

    /**
     * Test que la tabla profiles tiene las columnas correctas
     */
    public function test_profiles_table_has_correct_columns(): void
    {
        $expectedColumns = [
            'id',
            'user_id',
            'firstName',
            'lastName',
            'user_type',
            'is_verified',
            'rating',
            'ratings_count',
            'accepts_calls',
            'accepts_whatsapp',
            'accepts_emails',
            'created_at',
            'updated_at',
        ];

        foreach ($expectedColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('profiles', $column),
                "La columna {$column} no existe en la tabla profiles"
            );
        }
    }

    /**
     * Test que la tabla products tiene las columnas específicas de ganado
     */
    public function test_products_table_has_cattle_columns(): void
    {
        $expectedColumns = [
            'id',
            'ranch_id',
            'title',
            'description',
            'type',
            'breed',
            'age',
            'quantity',
            'price',
            'currency',
            'weight_avg',
            'weight_min',
            'weight_max',
            'sex',
            'purpose',
            'health_certificate_url',
            'vaccines_applied',
            'documentation_included',
            'genetic_test_results',
            'is_vaccinated',
            'delivery_method',
            'delivery_cost',
            'delivery_radius_km',
            'negotiable',
            'status',
            'views',
            'created_at',
            'updated_at',
        ];

        foreach ($expectedColumns as $column) {
            $this->assertTrue(
                Schema::hasColumn('products', $column),
                "La columna {$column} no existe en la tabla products"
            );
        }
    }

    /**
     * Test que las relaciones de claves foráneas están configuradas
     */
    public function test_foreign_key_relationships(): void
    {
        // Test relación profiles -> users
        $this->assertTrue(
            Schema::hasColumn('profiles', 'user_id'),
            "La relación profiles->users no está configurada"
        );

        // Test relación ranches -> profiles
        $this->assertTrue(
            Schema::hasColumn('ranches', 'profile_id'),
            "La relación ranches->profiles no está configurada"
        );

        // Test relación products -> ranches
        $this->assertTrue(
            Schema::hasColumn('products', 'ranch_id'),
            "La relación products->ranches no está configurada"
        );

        // Test relación addresses -> profiles
        $this->assertTrue(
            Schema::hasColumn('addresses', 'profile_id'),
            "La relación addresses->profiles no está configurada"
        );

        // Test relación addresses -> ranches
        $this->assertTrue(
            Schema::hasColumn('addresses', 'ranch_id'),
            "La relación addresses->ranches no está configurada"
        );
    }
}
