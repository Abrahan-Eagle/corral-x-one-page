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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->enum('level', ['users', 'ranches', 'cattle'])->default('users');
            $table->string('adressses');
            // $table->string('postal_code');
            $table->decimal('latitude', 10, 7); // Almacena la latitud con 7 decimales de precisión
            $table->decimal('longitude', 10, 7); // Almacena la longitud con 7 decimales de precisión
            $table->enum('status', ['completeData', 'incompleteData', 'notverified'])->default('notverified');
            $table->timestamps();

            // Claves foráneas
            $table->unsignedBigInteger('profile_id');
            $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('cascade');
            $table->unsignedBigInteger('ranch_id')->nullable(); // Relación con ranches (opcional) - FK se agrega después
            $table->unsignedBigInteger('city_id');  // Relación con ciudades
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->unsignedBigInteger('parish_id')->nullable(); // CONSOLIDADO: Parroquia (opcional) - FK se agrega después
            
            // Índices para optimizar consultas
            $table->index(['ranch_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
