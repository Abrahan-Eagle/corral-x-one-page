<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Conversation - Conversaciones de Chat
 * 
 * Representa las conversaciones 1:1 entre compradores y vendedores.
 * Sistema de chat en tiempo real para comunicación sobre productos.
 * 
 * Características principales:
 * - Relación N:1 con Profile (participante 1 y 2)
 * - Relación N:1 con Product (producto relacionado)
 * - Relación N:1 con Ranch (hacienda relacionada)
 * - Sistema de mensajes con timestamps
 * - Estado activo/archivado
 */
class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id_1',
        'profile_id_2',
        'product_id',
        'ranch_id',
        'last_message_at',
        'is_active',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Relación N:1 con Profile (participante 1)
     * Una conversación pertenece a un perfil como participante 1
     */
    public function participant1(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id_1');
    }

    /**
     * Relación N:1 con Profile (participante 2)
     * Una conversación pertenece a un perfil como participante 2
     */
    public function participant2(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id_2');
    }

    /**
     * Relación N:1 con Product
     * Una conversación puede estar relacionada con un producto
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relación N:1 con Ranch
     * Una conversación puede estar relacionada con una hacienda
     */
    public function ranch(): BelongsTo
    {
        return $this->belongsTo(Ranch::class);
    }

    /**
     * Relación 1:N con Message
     * Una conversación tiene múltiples mensajes
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * MÉTODOS DE CONVENIENCIA
     */

    /**
     * Verificar si la conversación está activa
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Activar la conversación
     */
    public function activate(): bool
    {
        $this->is_active = true;
        return $this->save();
    }

    /**
     * Archivar la conversación
     */
    public function archive(): bool
    {
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Obtener el otro participante de la conversación
     */
    public function getOtherParticipant(int $currentProfileId): ?Profile
    {
        // Usar comparación no estricta (==) para manejar String vs Int
        if ($this->profile_id_1 == $currentProfileId) {
            return $this->participant2;
        } elseif ($this->profile_id_2 == $currentProfileId) {
            return $this->participant1;
        }

        return null;
    }

    /**
     * Verificar si un perfil es participante de la conversación
     * Usa comparación no estricta (==) para manejar String vs Int desde la BD
     */
    public function hasParticipant(int $profileId): bool
    {
        // Usar comparación no estricta para manejar casos donde profile_id_1/2 pueden ser strings desde la BD
        return (int)$this->profile_id_1 === (int)$profileId || (int)$this->profile_id_2 === (int)$profileId;
    }

    /**
     * Obtener el último mensaje de la conversación
     */
    public function getLastMessage(): ?Message
    {
        return $this->messages()->orderBy('created_at', 'desc')->first();
    }

    /**
     * Obtener el número de mensajes no leídos para un perfil
     */
    public function getUnreadMessagesCount(int $profileId): int
    {
        return $this->messages()
                  ->where('sender_id', '!=', $profileId)
                  ->whereNull('read_at')
                  ->count();
    }

    /**
     * Marcar todos los mensajes como leídos para un perfil
     */
    public function markAsRead(int $profileId): bool
    {
        $updated = $this->messages()
                       ->where('sender_id', '!=', $profileId)
                       ->whereNull('read_at')
                       ->update(['read_at' => now()]);

        return $updated > 0;
    }

    /**
     * Actualizar el timestamp del último mensaje
     */
    public function updateLastMessageAt(): bool
    {
        $lastMessage = $this->getLastMessage();
        
        if ($lastMessage) {
            $this->last_message_at = $lastMessage->created_at;
            return $this->save();
        }

        return false;
    }

    /**
     * Crear o obtener conversación entre dos perfiles
     */
    public static function findOrCreate(int $profileId1, int $profileId2, ?int $productId = null, ?int $ranchId = null): self
    {
        // Asegurar que profile_id_1 sea el menor para evitar duplicados
        if ($profileId1 > $profileId2) {
            [$profileId1, $profileId2] = [$profileId2, $profileId1];
        }

        $conversation = self::where('profile_id_1', $profileId1)
                           ->where('profile_id_2', $profileId2)
                           ->where('product_id', $productId)
                           ->where('ranch_id', $ranchId)
                           ->first();

        if (!$conversation) {
            $conversation = self::create([
                'profile_id_1' => $profileId1,
                'profile_id_2' => $profileId2,
                'product_id' => $productId,
                'ranch_id' => $ranchId,
                'is_active' => true,
            ]);
        }

        return $conversation;
    }

    /**
     * Obtener conversaciones de un perfil
     */
    public static function getProfileConversations(int $profileId)
    {
        return self::where(function ($query) use ($profileId) {
            $query->where('profile_id_1', $profileId)
                  ->orWhere('profile_id_2', $profileId);
        })
        ->with(['participant1', 'participant2', 'product', 'ranch'])
        ->orderBy('last_message_at', 'desc')
        ->get();
    }

    /**
     * SCOPES PARA CONSULTAS FRECUENTES
     */

    /**
     * Scope para conversaciones activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para conversaciones archivadas
     */
    public function scopeArchived($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope para conversaciones de un perfil
     */
    public function scopeByProfile($query, int $profileId)
    {
        return $query->where(function ($q) use ($profileId) {
            $q->where('profile_id_1', $profileId)
              ->orWhere('profile_id_2', $profileId);
        });
    }

    /**
     * Scope para conversaciones por producto
     */
    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope para conversaciones por hacienda
     */
    public function scopeByRanch($query, int $ranchId)
    {
        return $query->where('ranch_id', $ranchId);
    }

    /**
     * Scope para conversaciones recientes
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('last_message_at', 'desc')->limit($limit);
    }

    /**
     * Scope para conversaciones con mensajes no leídos
     */
    public function scopeWithUnreadMessages($query, int $profileId)
    {
        return $query->whereHas('messages', function ($q) use ($profileId) {
            $q->where('sender_id', '!=', $profileId)
              ->whereNull('read_at');
        });
    }
}
