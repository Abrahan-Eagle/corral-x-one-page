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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id(); // Identificador único de la conversación
            $table->foreignId('profile_id_1')->constrained('profiles')->cascadeOnDelete(); // Primer participante (comprador)
            $table->foreignId('profile_id_2')->constrained('profiles')->cascadeOnDelete(); // Segundo participante (vendedor)
            $table->foreignId('product_id')->nullable()->constrained('products')->cascadeOnDelete(); // Producto relacionado (opcional)
            $table->foreignId('ranch_id')->nullable()->constrained('ranches')->cascadeOnDelete(); // Ranch relacionado (opcional)
            $table->timestamp('last_message_at')->nullable(); // Timestamp del último mensaje
            $table->boolean('is_active')->default(true); // Conversación activa o archivada
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['profile_id_1', 'last_message_at']); // Conversaciones del perfil 1 ordenadas por actividad
            $table->index(['profile_id_2', 'last_message_at']); // Conversaciones del perfil 2 ordenadas por actividad
            $table->index(['product_id', 'created_at']); // Conversaciones relacionadas a un producto
            $table->index(['ranch_id', 'created_at']); // Conversaciones relacionadas a un ranch
            $table->index(['is_active', 'last_message_at']); // Conversaciones activas ordenadas por actividad
            $table->unique(['profile_id_1', 'profile_id_2', 'product_id']); // Evitar conversaciones duplicadas entre los mismos perfiles sobre el mismo producto
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
