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
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); // Identificador único de la categoría
            $table->string('name'); // Nombre de la categoría (ej. "Ganado Bovino", "Equipos", "Alimentos")
            $table->string('slug')->unique(); // Slug único para URLs (ej. "ganado-bovino", "equipos")
            $table->text('description')->nullable(); // Descripción de la categoría
            $table->foreignId('parent_id')->nullable()->constrained('categories')->cascadeOnDelete(); // Categoría padre (para subcategorías)
            $table->unsignedInteger('sort_order')->default(0); // Orden de visualización
            $table->boolean('is_active')->default(true); // Categoría activa o inactiva
            $table->string('icon')->nullable(); // Icono de la categoría (ej. "cow", "tractor")
            $table->string('color')->nullable(); // Color de la categoría (ej. "#FF5733")
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['parent_id', 'sort_order']); // Categorías ordenadas por padre
            $table->index(['is_active', 'sort_order']); // Categorías activas ordenadas
            $table->index('slug'); // Búsquedas por slug
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};