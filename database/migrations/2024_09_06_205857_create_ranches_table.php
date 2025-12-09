<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ranches', function (Blueprint $table) {
            $table->id();

            // 1 perfil dueño -> muchas haciendas
            $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();

            // Datos básicos de la hacienda / rancho
            $table->string('name');                // nombre comercial: "Hacienda El Samán"
            $table->string('legal_name')->nullable(); // razón social si aplica
            $table->string('tax_id')->nullable();     // RIF/NIT u otro
            // $table->text('description')->nullable();
            $table->text('business_description')->nullable()->comment('Descripción del negocio o actividad ganadera');
            
            // Certificaciones y documentos - CONSOLIDADO: add_certifications_and_documents
            $table->json('certifications')->nullable()->comment('Lista de certificaciones sanitarias y de calidad');
            $table->string('business_license_url')->nullable()->comment('URL del documento de licencia comercial');
            

            // Relación 1:1 con addresses (cada ranch tiene una address única)
            $table->foreignId('address_id')->nullable()->constrained('addresses')->cascadeOnDelete(); // CONSOLIDADO: make_nullable
            // No usar unique porque puede ser null

            $table->boolean('is_primary')->default(false); // hacienda principal del perfil
            
            // Configuración de ventas
            // $table->boolean('accepts_orders')->default(true)->comment('Indica si la hacienda acepta nuevas órdenes de compra');
            $table->text('delivery_policy')->nullable()->comment('Política de entrega de la hacienda');
            $table->text('return_policy')->nullable()->comment('Política de devoluciones de la hacienda');
            // $table->decimal('min_order_amount', 10, 2)->nullable()->comment('Monto mínimo para realizar una orden a esta hacienda');
            // $table->integer('max_delivery_distance_km')->nullable()->comment('Distancia máxima de entrega ofrecida por la hacienda en km');
            $table->boolean('accepts_visits')->default(false)->comment('Indica si la hacienda acepta visitas de compradores'); // CONSOLIDADO: add_certifications_and_documents
            $table->string('contact_hours')->nullable()->comment('Horario preferido para contacto o visitas');
            
            // Métricas de ventas
            $table->decimal('avg_rating', 3, 2)->default(0)->comment('Calificación promedio de la hacienda');
            $table->unsignedInteger('total_sales')->default(0)->comment('Número total de ventas realizadas');
            $table->timestamp('last_sale_at')->nullable()->comment('Fecha de la última venta');

            $table->timestamps();
            $table->softDeletes();

            // Evitar duplicados de nombre por perfil
            $table->unique(['profile_id', 'name']);
            $table->index(['profile_id', 'is_primary']);
            
            // Índices adicionales para marketplace (solo columnas existentes)
            $table->index('avg_rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranches');
    }
};
