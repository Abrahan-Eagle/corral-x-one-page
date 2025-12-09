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
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id(); // Identificador único de la relación
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete(); // Producto al que pertenece
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete(); // Categoría asignada
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['product_id', 'category_id']); // Consultas por producto y categoría
            $table->index(['category_id', 'created_at']); // Productos por categoría ordenados por fecha
            $table->unique(['product_id', 'category_id']); // Evitar duplicados: un producto no puede estar en la misma categoría dos veces
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};