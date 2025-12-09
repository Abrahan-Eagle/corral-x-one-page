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
     * Hace que product_id sea nullable en la tabla reviews.
     * Esto permite que el comprador cree dos reviews en el mismo pedido:
     * - Una para el producto (con product_id)
     * - Una para el vendedor (con product_id = null)
     */
    public function up(): void
    {
        $connection = Schema::getConnection()->getName();
        $database = DB::connection($connection)->getDatabaseName();

        // Obtener el nombre de la foreign key constraint
        $foreignKey = DB::connection($connection)->selectOne(
            "SELECT CONSTRAINT_NAME 
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = ? 
             AND TABLE_NAME = 'reviews' 
             AND COLUMN_NAME = 'product_id' 
             AND REFERENCED_TABLE_NAME IS NOT NULL 
             LIMIT 1",
            [$database]
        );

        // Eliminar la foreign key constraint si existe
        if ($foreignKey) {
            DB::statement("ALTER TABLE `reviews` DROP FOREIGN KEY `{$foreignKey->CONSTRAINT_NAME}`");
        }

        // Modificar la columna para que sea nullable
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('product_id')
                ->nullable()
                ->change()
                ->constrained('products')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = Schema::getConnection()->getName();
        $database = DB::connection($connection)->getDatabaseName();

        // Obtener el nombre de la foreign key constraint
        $foreignKey = DB::connection($connection)->selectOne(
            "SELECT CONSTRAINT_NAME 
             FROM information_schema.KEY_COLUMN_USAGE 
             WHERE TABLE_SCHEMA = ? 
             AND TABLE_NAME = 'reviews' 
             AND COLUMN_NAME = 'product_id' 
             AND REFERENCED_TABLE_NAME IS NOT NULL 
             LIMIT 1",
            [$database]
        );

        // Eliminar la foreign key constraint si existe
        if ($foreignKey) {
            DB::statement("ALTER TABLE `reviews` DROP FOREIGN KEY `{$foreignKey->CONSTRAINT_NAME}`");
        }

        // Primero eliminar cualquier registro con product_id NULL antes de hacer la columna NOT NULL
        // Esto es necesario porque no podemos hacer una columna NOT NULL si tiene valores NULL
        DB::table('reviews')->whereNull('product_id')->delete();

        // Eliminar registros con product_id que no existen en products (puede pasar durante rollback)
        // Solo si la tabla products existe
        if (Schema::hasTable('products')) {
            $existingProductIds = DB::table('products')->pluck('id')->toArray();
            if (!empty($existingProductIds)) {
                DB::table('reviews')
                    ->whereNotNull('product_id')
                    ->whereNotIn('product_id', $existingProductIds)
                    ->delete();
            } else {
                // Si no hay productos, eliminar todas las reviews con product_id
                DB::table('reviews')->whereNotNull('product_id')->delete();
            }
        } else {
            // Si la tabla products no existe, eliminar todas las reviews
            DB::table('reviews')->delete();
        }

        // Ahora hacer la columna NOT NULL usando DB::statement directamente
        DB::statement('ALTER TABLE `reviews` MODIFY `product_id` BIGINT UNSIGNED NOT NULL');

        // Agregar la foreign key constraint solo si la tabla products existe
        if (Schema::hasTable('products')) {
        Schema::table('reviews', function (Blueprint $table) {
                $table->foreign('product_id')
                    ->references('id')
                    ->on('products')
                    ->onDelete('cascade');
        });
        }
    }
};
