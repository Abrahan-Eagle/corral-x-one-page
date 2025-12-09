<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Message - Mensajes de Chat
 * 
 * Representa los mensajes individuales dentro de las conversaciones.
 * Soporte para texto, imágenes, videos y documentos.
 * 
 * Características principales:
 * - Relación N:1 con Conversation
 * - Relación N:1 con Profile (sender)
 * - Soporte para múltiples tipos de mensaje
 * - Sistema de lectura con timestamps
 * - Soft delete para mensajes eliminados
 */
class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'content',
        'message_type',
        'attachment_url',
        'read_at',
        'is_deleted',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'is_deleted' => 'boolean',
    ];

    /**
     * Relación N:1 con Conversation
     * Un mensaje pertenece a una conversación
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Relación N:1 con Profile
     * Un mensaje pertenece a un perfil (sender)
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'sender_id');
    }

    /**
     * MÉTODOS DE CONVENIENCIA
     */

    /**
     * Verificar si el mensaje ha sido leído
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Verificar si el mensaje está eliminado
     */
    public function isDeleted(): bool
    {
        return $this->is_deleted;
    }

    /**
     * Marcar el mensaje como leído
     */
    public function markAsRead(): bool
    {
        if (!$this->isRead()) {
            $this->read_at = now();
            return $this->save();
        }

        return true;
    }

    /**
     * Marcar el mensaje como eliminado
     */
    public function markAsDeleted(): bool
    {
        $this->is_deleted = true;
        return $this->save();
    }

    /**
     * Restaurar el mensaje eliminado
     */
    public function restore(): bool
    {
        $this->is_deleted = false;
        return $this->save();
    }

    /**
     * Verificar si el mensaje tiene archivo adjunto
     */
    public function hasAttachment(): bool
    {
        return !empty($this->attachment_url);
    }

    /**
     * Verificar si es un mensaje de texto
     */
    public function isText(): bool
    {
        return $this->message_type === 'text';
    }

    /**
     * Verificar si es un mensaje con imagen
     */
    public function isImage(): bool
    {
        return $this->message_type === 'image';
    }

    /**
     * Verificar si es un mensaje con video
     */
    public function isVideo(): bool
    {
        return $this->message_type === 'video';
    }

    /**
     * Verificar si es un mensaje con documento
     */
    public function isDocument(): bool
    {
        return $this->message_type === 'document';
    }

    /**
     * Obtener el tipo de mensaje como texto
     */
    public function getMessageTypeTextAttribute(): string
    {
        $types = [
            'text' => 'Texto',
            'image' => 'Imagen',
            'video' => 'Video',
            'document' => 'Documento'
        ];

        return $types[$this->message_type] ?? 'Desconocido';
    }

    /**
     * Obtener el contenido truncado
     */
    public function getTruncatedContentAttribute(int $length = 50): string
    {
        if (!$this->content) {
            return '';
        }

        return strlen($this->content) > $length 
            ? substr($this->content, 0, $length) . '...'
            : $this->content;
    }

    /**
     * Obtener el tiempo transcurrido desde que se envió
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Crear un nuevo mensaje
     */
    public static function createMessage(int $conversationId, int $senderId, string $content, string $messageType = 'text', ?string $attachmentUrl = null): self
    {
        $message = self::create([
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'content' => $content,
            'message_type' => $messageType,
            'attachment_url' => $attachmentUrl,
        ]);

        // Actualizar el timestamp del último mensaje en la conversación
        $message->conversation->updateLastMessageAt();

        return $message;
    }

    /**
     * Obtener mensajes de una conversación
     */
    public static function getConversationMessages(int $conversationId, int $limit = 50)
    {
        return self::where('conversation_id', $conversationId)
                  ->where('is_deleted', false)
                  ->with('sender')
                  ->orderBy('created_at', 'asc')
                  ->limit($limit)
                  ->get();
    }

    /**
     * Obtener mensajes no leídos de un perfil
     */
    public static function getUnreadMessages(int $profileId)
    {
        return self::whereHas('conversation', function ($query) use ($profileId) {
            $query->where(function ($q) use ($profileId) {
                $q->where('profile_id_1', $profileId)
                  ->orWhere('profile_id_2', $profileId);
            });
        })
        ->where('sender_id', '!=', $profileId)
        ->whereNull('read_at')
        ->where('is_deleted', false)
        ->with(['sender', 'conversation'])
        ->orderBy('created_at', 'desc')
        ->get();
    }

    /**
     * Obtener el número de mensajes no leídos de un perfil
     */
    public static function getUnreadMessagesCount(int $profileId): int
    {
        return self::whereHas('conversation', function ($query) use ($profileId) {
            $query->where(function ($q) use ($profileId) {
                $q->where('profile_id_1', $profileId)
                  ->orWhere('profile_id_2', $profileId);
            });
        })
        ->where('sender_id', '!=', $profileId)
        ->whereNull('read_at')
        ->where('is_deleted', false)
        ->count();
    }

    /**
     * SCOPES PARA CONSULTAS FRECUENTES
     */

    /**
     * Scope para mensajes no eliminados
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('is_deleted', false);
    }

    /**
     * Scope para mensajes eliminados
     */
    public function scopeDeleted($query)
    {
        return $query->where('is_deleted', true);
    }

    /**
     * Scope para mensajes leídos
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Scope para mensajes no leídos
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope para mensajes por tipo
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('message_type', $type);
    }

    /**
     * Scope para mensajes de texto
     */
    public function scopeText($query)
    {
        return $query->where('message_type', 'text');
    }

    /**
     * Scope para mensajes con archivos
     */
    public function scopeWithAttachments($query)
    {
        return $query->whereNotNull('attachment_url');
    }

    /**
     * Scope para mensajes por conversación
     */
    public function scopeByConversation($query, int $conversationId)
    {
        return $query->where('conversation_id', $conversationId);
    }

    /**
     * Scope para mensajes por remitente
     */
    public function scopeBySender($query, int $senderId)
    {
        return $query->where('sender_id', $senderId);
    }

    /**
     * Scope para mensajes recientes
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}
