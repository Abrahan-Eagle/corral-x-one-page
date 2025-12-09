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
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            
            // Tipo de publicidad
            $table->enum('type', ['sponsored_product', 'external_ad']);
            
            // Información básica
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_url'); // URL de la imagen (requerido)
            $table->string('target_url')->nullable(); // URL destino al hacer click
            
            // Estado y fechas
            $table->boolean('is_active')->default(true);
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable(); // Si pasa, se desactiva automáticamente
            
            // Prioridad y tracking
            $table->integer('priority')->default(0); // Orden de prioridad si hay múltiples
            $table->integer('clicks')->default(0); // Contador de clicks
            $table->integer('impressions')->default(0); // Contador de visualizaciones
            
            // Campos específicos por tipo
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete(); // Si type = 'sponsored_product'
            $table->string('advertiser_name')->nullable(); // Si type = 'external_ad'
            
            // Admin que crea el anuncio
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['type', 'is_active', 'start_date', 'end_date']); // Consultas de anuncios activos
            $table->index(['priority']); // Ordenamiento por prioridad
            $table->index(['product_id']); // Búsquedas por producto patrocinado
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
