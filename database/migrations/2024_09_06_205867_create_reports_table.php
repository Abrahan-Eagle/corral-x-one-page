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
        Schema::create('reports', function (Blueprint $table) {
            $table->id(); // Identificador único del reporte
            $table->foreignId('reporter_id')->constrained('profiles')->cascadeOnDelete(); // Perfil que reporta
            $table->morphs('reportable'); // Polimórfico: puede reportar productos, usuarios, ranches, etc.
            $table->enum('report_type', ['spam', 'inappropriate', 'fraud', 'fake_product', 'harassment', 'other']); // Tipo de reporte
            $table->text('description')->nullable(); // Descripción detallada del reporte
            $table->enum('status', ['pending', 'reviewing', 'resolved', 'dismissed'])->default('pending'); // Estado del reporte
            $table->foreignId('admin_id')->nullable()->constrained('profiles')->cascadeOnDelete(); // Admin que revisa el reporte
            $table->text('admin_notes')->nullable(); // Notas del admin sobre la resolución
            $table->timestamp('resolved_at')->nullable(); // Timestamp de resolución
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['status', 'created_at']); // Reportes pendientes ordenados por fecha
            $table->index(['report_type', 'status']); // Filtros por tipo y estado
            $table->index(['reporter_id', 'created_at']); // Reportes realizados por el perfil
            $table->index(['admin_id', 'created_at']); // Reportes asignados al admin
            // El índice para reportable_type y reportable_id se crea automáticamente con morphs()
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
