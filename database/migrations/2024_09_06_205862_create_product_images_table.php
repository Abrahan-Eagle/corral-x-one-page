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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id(); // Identificador único del archivo multimedia
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete(); // Producto al que pertenece
            $table->string('file_url'); // URL/path del archivo (imagen o video)
            $table->enum('file_type', ['image', 'video']); // Tipo de archivo: imagen o video
            $table->string('alt_text')->nullable(); // Texto alternativo para accesibilidad
            $table->boolean('is_primary')->default(false); // Flag para archivo principal (thumbnail)
            $table->unsignedInteger('sort_order')->default(0); // Orden de visualización en galería
            $table->unsignedInteger('duration')->nullable(); // Duración en segundos (solo para videos)
            $table->unsignedBigInteger('file_size')->nullable(); // Tamaño en bytes
            $table->string('resolution')->nullable(); // Resolución (ej: 1920x1080, 1080x720)
            $table->string('format')->nullable(); // Formato del archivo (jpg, png, mp4, mov)
            $table->string('compression')->nullable(); // Nivel de compresión aplicado
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['product_id', 'sort_order']); // Consultas por producto ordenadas
            $table->index(['product_id', 'is_primary']); // Búsqueda de imagen principal
            $table->index(['file_type', 'product_id']); // Filtros por tipo de archivo
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
