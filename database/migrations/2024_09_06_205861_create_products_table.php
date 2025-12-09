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
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // Identificador único del producto
            $table->foreignId('ranch_id')->constrained('ranches')->cascadeOnDelete(); // Hacienda a la que pertenece (obligatorio)
            $table->string('title'); // Título breve y descriptivo del producto
            $table->text('description'); // Descripción detallada del producto
            $table->enum('type', ['engorde', 'lechero', 'padrote', 'equipment', 'feed', 'other'])->default('engorde'); // Tipo: ganado, equipos, alimentos u otros
            $table->enum('breed', [
                'Brahman', 'Holstein', 'Guzerat', 'Gyr', 'Nelore', 'Jersey', 'Angus', 'Simmental', 
                'Pardo Suizo', 'Charolais', 'Limousin', 'Santa Gertrudis', 'Brangus', 'Girolando',
                'Carora', 'Criollo Limonero', 'Mosaico Perijanero', 'Indubrasil', 'Sardo Negro',
                'Senepol', 'Romosinuano', 'Sahiwal', 'Búfalo Murrah', 'Búfalo Jafarabadi',
                'Búfalo Mediterráneo', 'Búfalo Carabao', 'Búfalo Nili-Ravi', 'Búfalo Surti',
                'Búfalo Pandharpuri', 'Búfalo Nagpuri', 'Búfalo Mehsana', 'Búfalo Bhadawari',
                'Búfalo Toda', 'Búfalo Kundi', 'Búfalo Nili', 'Búfalo Ravi', 'Otra'
            ])->default('Brahman'); // Raza del ganado bovino en Venezuela
            $table->integer('age')->nullable(); // Edad en meses (nullable si no aplica)
            $table->integer('quantity'); // Cantidad de animales o ítems ofrecidos
            $table->decimal('price', 10, 2); // Precio unitario o total
            $table->string('currency', 3)->default('USD'); // Moneda ISO-4217 (ej. USD, VES)
            $table->enum('status', ['active', 'paused', 'sold', 'expired'])->default('active'); // Estado del producto
            
            // Campos específicos de ganado
            $table->decimal('weight_avg', 8, 2)->nullable()->comment('Peso promedio en kg'); // Peso promedio en kg
            $table->decimal('weight_min', 8, 2)->nullable()->comment('Peso mínimo del lote en kg'); // Peso mínimo del lote en kg
            $table->decimal('weight_max', 8, 2)->nullable()->comment('Peso máximo del lote en kg'); // Peso máximo del lote en kg
            $table->enum('sex', ['male', 'female', 'mixed'])->nullable()->comment('Sexo del ganado'); // Sexo del ganado
            $table->enum('purpose', ['breeding', 'meat', 'dairy', 'mixed'])->nullable()->comment('Finalidad: reproducción, carne, leche, mixto'); // Finalidad: reproducción, carne, leche, mixto
            
            // Información sanitaria
            $table->string('health_certificate_url')->nullable()->comment('URL del certificado sanitario'); // URL del certificado sanitario
            $table->json('vaccines_applied')->nullable()->comment('Vacunas aplicadas (JSON)'); // Vacunas aplicadas (JSON)
            $table->date('last_vaccination')->nullable()->comment('Fecha de la última vacunación'); // Fecha de la última vacunación
            $table->boolean('is_vaccinated')->default(false)->comment('Indica si el ganado está vacunado'); // Indica si el ganado está vacunado
            
            // Información de manejo
            $table->text('feeding_info')->nullable()->comment('Información sobre la alimentación del ganado'); // Información sobre la alimentación del ganado
            $table->text('handling_info')->nullable()->comment('Información sobre el manejo del ganado'); // Información sobre el manejo del ganado
            $table->string('origin_farm')->nullable()->comment('Finca de origen del ganado'); // Finca de origen del ganado
            
            // Disponibilidad y entrega
            $table->date('available_from')->nullable()->comment('Fecha de inicio de disponibilidad'); // Fecha de inicio de disponibilidad
            $table->date('available_until')->nullable()->comment('Fecha de fin de disponibilidad'); // Fecha de fin de disponibilidad
            $table->enum('delivery_method', ['pickup', 'delivery', 'both'])->default('pickup')->comment('Método de entrega'); // Método de entrega
            $table->decimal('delivery_cost', 8, 2)->nullable()->comment('Costo de entrega'); // Costo de entrega
            $table->integer('delivery_radius_km')->nullable()->comment('Radio de entrega en km'); // Radio de entrega en km
            
            // Información comercial
            $table->enum('price_type', ['per_unit', 'per_lot', 'per_kg'])->default('per_unit')->comment('Tipo de precio'); // Tipo de precio
            $table->boolean('negotiable')->default(true)->comment('Indica si el precio es negociable'); // Indica si el precio es negociable
            $table->decimal('min_order_quantity', 8, 2)->nullable()->comment('Cantidad mínima de compra'); // Cantidad mínima de compra
            $table->boolean('is_featured')->default(false); // Flag de producto destacado
            $table->unsignedInteger('views')->default(0); // Contador de vistas (no suma para el dueño)


            $table->enum('transportation_included', ['yes', 'no', 'negotiable'])->default('negotiable'); // Transporte incluido
            $table->text('documentation_included')->nullable()->comment('Documentos incluidos con el ganado'); // Documentos incluidos con el ganado
            $table->boolean('genetic_tests_available')->default(false); // Pruebas genéticas disponibles
            $table->json('genetic_test_results')->nullable(); // Resultados de las pruebas genéticas
            $table->string('bloodline')->nullable()->comment('Línea genética del ganado'); // Línea genética del ganado


            $table->timestamps();

            // Índices para optimizar consultas y filtros frecuentes
            $table->index(['ranch_id', 'created_at']); // Consultas por hacienda y orden cronológico
            $table->index(['type', 'breed']); // Filtros por tipo y raza
            $table->index(['status', 'created_at']); // Productos activos/pausados ordenados por fecha
            $table->index('is_featured'); // Búsquedas de destacados
            $table->index('sex'); // Filtros por sexo del ganado
            $table->index('purpose'); // Filtros por finalidad
            $table->index('is_vaccinated'); // Filtros por vacunación
            $table->index('delivery_method'); // Filtros por método de entrega
            $table->index('negotiable'); // Filtros por precio negociable

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
