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
        Schema::create('messages', function (Blueprint $table) {
            $table->id(); // Identificador único del mensaje
            $table->foreignId('conversation_id')->constrained('conversations')->cascadeOnDelete(); // Conversación a la que pertenece
            $table->foreignId('sender_id')->constrained('profiles')->cascadeOnDelete(); // Perfil que envía el mensaje
            $table->text('content'); // Contenido del mensaje
            $table->enum('message_type', ['text', 'image', 'video', 'document'])->default('text'); // Tipo de mensaje
            $table->string('attachment_url')->nullable(); // URL del archivo adjunto (imagen, video, documento)
            $table->timestamp('read_at')->nullable(); // Timestamp de cuando fue leído
            $table->boolean('is_deleted')->default(false); // Mensaje eliminado (soft delete)
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['conversation_id', 'created_at']); // Mensajes de la conversación ordenados cronológicamente
            $table->index(['sender_id', 'created_at']); // Mensajes enviados por el perfil
            $table->index(['read_at', 'conversation_id']); // Mensajes no leídos por conversación
            $table->index(['message_type', 'created_at']); // Filtros por tipo de mensaje
            $table->index(['is_deleted', 'created_at']); // Mensajes no eliminados
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
