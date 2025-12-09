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
        Schema::create('ranch_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ranch_id')->constrained('ranches')->cascadeOnDelete();
            $table->string('certification_type')->nullable()->comment('Tipo de certificación (SENASICA, Libre de Brucelosis, etc.)');
            $table->string('document_url')->comment('URL del documento PDF en storage');
            $table->string('original_filename')->nullable()->comment('Nombre original del archivo');
            $table->unsignedInteger('file_size')->nullable()->comment('Tamaño en bytes');
            $table->integer('order')->default(0)->comment('Orden de visualización');
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index(['ranch_id', 'certification_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ranch_documents');
    }
};