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
        Schema::create('favorites', function (Blueprint $table) {
            $table->id(); // Identificador único del favorito
            $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete(); // Perfil que marca como favorito
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete(); // Producto marcado como favorito
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['profile_id', 'created_at']); // Favoritos del perfil ordenados por fecha
            $table->index(['product_id', 'created_at']); // Perfiles que marcaron este producto como favorito
            $table->unique(['profile_id', 'product_id']); // Evitar duplicados: un perfil no puede marcar el mismo producto dos veces
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
