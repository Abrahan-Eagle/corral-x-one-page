<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id(); // Identificador único de la reseña
            $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete(); // Perfil que escribe la reseña
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete(); // Producto reseñado
            $table->foreignId('ranch_id')->constrained('ranches')->cascadeOnDelete(); // Ranch/vendedor reseñado
            $table->unsignedTinyInteger('rating')->comment('Calificación de 1 a 5 estrellas'); // Rating de 1-5
            $table->text('comment')->nullable(); // Comentario opcional de la reseña
            $table->boolean('is_verified_purchase')->default(false); // Indica si es una compra verificada
            $table->boolean('is_approved')->default(false); // Moderación: reseña aprobada por admin
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['product_id', 'is_approved', 'created_at']); // Reseñas del producto aprobadas ordenadas
            $table->index(['ranch_id', 'is_approved', 'created_at']); // Reseñas del ranch aprobadas ordenadas
            $table->index(['profile_id', 'created_at']); // Reseñas escritas por el perfil
            $table->index(['rating', 'is_approved']); // Filtros por calificación
            $table->unique(['profile_id', 'product_id']); // Un perfil solo puede reseñar un producto una vez
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
