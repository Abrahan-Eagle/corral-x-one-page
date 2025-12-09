<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Elimina la restricción única antigua basada en (profile_id, product_id)
     * y crea una nueva basada en (order_id, profile_id) para permitir
     * que un usuario califique el mismo producto en diferentes pedidos.
     */
    public function up(): void
    {
        $connection = Schema::getConnection()->getName();
        $database = DB::connection($connection)->getDatabaseName();

        // Eliminar la restricción única antigua basada en (profile_id, product_id)
        $oldConstraint = DB::connection($connection)->selectOne(
            "SELECT CONSTRAINT_NAME 
             FROM information_schema.TABLE_CONSTRAINTS 
             WHERE TABLE_SCHEMA = ? 
             AND TABLE_NAME = 'reviews' 
             AND CONSTRAINT_NAME = 'reviews_profile_id_product_id_unique'
             LIMIT 1",
            [$database]
        );

        if ($oldConstraint) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropUnique(['profile_id', 'product_id']);
            });
        }

        // Crear nueva restricción única basada en (order_id, profile_id) cuando order_id no es null
        // Esto permite que un usuario califique el mismo producto en diferentes pedidos,
        // pero no dos veces el mismo pedido
        // Nota: MySQL permite NULLs en índices únicos, pero no los considera duplicados
        // Solo aplicaremos la restricción cuando order_id no sea null
        Schema::table('reviews', function (Blueprint $table) {
            // Crear índice único en (order_id, profile_id)
            // Los NULLs en order_id no se considerarán duplicados entre sí
            $table->unique(['order_id', 'profile_id'], 'reviews_order_id_profile_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = Schema::getConnection()->getName();

        // Eliminar la nueva restricción única
        $newConstraint = DB::connection($connection)->selectOne(
            "SELECT CONSTRAINT_NAME 
             FROM information_schema.TABLE_CONSTRAINTS 
             WHERE TABLE_SCHEMA = DATABASE() 
             AND TABLE_NAME = 'reviews' 
             AND CONSTRAINT_NAME = 'reviews_order_id_profile_id_unique'
             LIMIT 1"
        );

        if ($newConstraint) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropUnique(['order_id', 'profile_id']);
            });
        }

        // Restaurar la restricción única antigua
        Schema::table('reviews', function (Blueprint $table) {
            $table->unique(['profile_id', 'product_id']);
        });
    }
};
