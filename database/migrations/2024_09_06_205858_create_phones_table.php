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
        Schema::create('phones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->onDelete('cascade');
            $table->foreignId('ranch_id')->nullable()->constrained('ranches')->onDelete('cascade'); // Relación con ranches (opcional)
            $table->foreignId('operator_code_id')->constrained('operator_codes')->onDelete('cascade');
            $table->string('number', 7); // Número local con longitud fija
            $table->boolean('is_primary')->default(false);
            $table->boolean('status')->default(true); // se muestra el correo solo si esta activo
            // $table->boolean('approved')->default(false);// significa si el documento esta aprovado
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['ranch_id', 'is_primary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phones');
    }
};
