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
     * Actualiza la restricción única para incluir product_id.
     * Esto permite que un comprador cree dos reviews en el mismo pedido:
     * - Una para el producto (con product_id)
     * - Una para el vendedor (con product_id = null)
     */
    public function up(): void
    {
        $connection = Schema::getConnection()->getName();

        // Eliminar la restricción única actual (order_id, profile_id)
        $currentConstraint = DB::connection($connection)->selectOne(
            "SELECT CONSTRAINT_NAME 
             FROM information_schema.TABLE_CONSTRAINTS 
             WHERE TABLE_SCHEMA = DATABASE() 
             AND TABLE_NAME = 'reviews' 
             AND CONSTRAINT_NAME = 'reviews_order_id_profile_id_unique'
             LIMIT 1"
        );

        if ($currentConstraint) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropUnique(['order_id', 'profile_id']);
            });
        }

        // Crear nueva restricción única que incluya product_id
        // Esto permite múltiples reviews del mismo usuario en el mismo pedido
        // si son de diferentes tipos (producto vs vendedor/comprador)
        Schema::table('reviews', function (Blueprint $table) {
            $table->unique(['order_id', 'profile_id', 'product_id'], 'reviews_order_profile_product_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = Schema::getConnection()->getName();

        // Eliminar la nueva restricción única (manejar tanto nombre antiguo como nuevo)
        $newConstraint = DB::connection($connection)->selectOne(
            "SELECT CONSTRAINT_NAME 
             FROM information_schema.TABLE_CONSTRAINTS 
             WHERE TABLE_SCHEMA = DATABASE() 
             AND TABLE_NAME = 'reviews' 
             AND CONSTRAINT_NAME IN ('reviews_order_profile_product_unique', 'reviews_order_id_profile_id_product_id_unique')
             LIMIT 1"
        );

        if ($newConstraint) {
            Schema::table('reviews', function (Blueprint $table) {
                // Usar el nombre explícito del índice para evitar conflictos con nombres antiguos
                $table->dropUnique('reviews_order_profile_product_unique');
            });
        }

        // Restaurar la restricción única anterior
        Schema::table('reviews', function (Blueprint $table) {
            $table->unique(['order_id', 'profile_id'], 'reviews_order_id_profile_id_unique');
        });
    }
};
