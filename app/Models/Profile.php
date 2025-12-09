<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Modelo Profile - Datos del marketplace de ganado
 * 
 * Este modelo representa el perfil completo de un usuario en el marketplace.
 * Contiene toda la información personal, comercial y de verificación necesaria
 * para operar en la plataforma de compra/venta de ganado.
 * 
 * Características principales:
 * - Separación clara entre autenticación (User) y datos marketplace (Profile)
 * - Soporte para usuarios compradores, vendedores o ambos
 * - Sistema de verificación para vendedores y compradores
 * - Métricas de rating y experiencia comercial
 * - Configuración de comunicación y notificaciones
 * - Información de contacto y preferencias
 */
class Profile extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla en la base de datos
     */
    protected $table = 'profiles';

    /**
     * Campos que pueden ser asignados masivamente
     */
    protected $fillable = [
        'user_id',
        'firstName',
        'middleName',
        'lastName',
        'secondLastName',
        'photo_users',
        'bio',
        'date_of_birth',
        'maritalStatus',
        'sex',
        'ci_number',
        'status',
        'is_verified',
        'rating',
        'ratings_count',
        'has_unread_messages',
        'user_type',
        'is_both_verified',
        'accepts_calls',
        'accepts_whatsapp',
        'accepts_emails',
        'whatsapp_number',
        'is_premium_seller',
        'premium_expires_at',
        'fcm_device_token',
        // KYC básico (persona)
        'kyc_status',
        'kyc_rejection_reason',
        'kyc_document_type',
        'kyc_document_number',
        'kyc_country_code',
        'kyc_doc_front_path',
        'kyc_rif_path',
        'kyc_selfie_path',
        'kyc_selfie_with_doc_path',
        'kyc_liveness_selfies_paths',
        'kyc_verified_at',
        // Campos deprecated (mantener por compatibilidad):
        'ranch',
        'phone',
        'address',
    ];

    /**
     * Campos que deben ser ocultos en las respuestas JSON
     */
    protected $hidden = [
        'business_license', // Información sensible
    ];

    /**
     * Casts para tipos de datos específicos
     */
    protected $casts = [
        'id' => 'int',
        'user_id' => 'int',
        'date_of_birth' => 'date',
        'rating' => 'decimal:2',
        'premium_expires_at' => 'datetime',
        'is_verified' => 'boolean',
        'is_both_verified' => 'boolean',
        'has_unread_messages' => 'boolean',
        'accepts_calls' => 'boolean',
        'accepts_whatsapp' => 'boolean',
        'accepts_emails' => 'boolean',
        'is_premium_seller' => 'boolean',
        'kyc_verified_at' => 'datetime',
        'kyc_liveness_selfies_paths' => 'array',
    ];

    /**
     * Relación 1:1 con User (autenticación)
     * Un perfil pertenece a un usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación 1:N con Ranch (haciendas)
     * Un perfil puede tener múltiples haciendas
     */
    public function ranches(): HasMany
    {
        return $this->hasMany(Ranch::class);
    }

    /**
     * Relación 1:N con Phone (teléfonos)
     * Un perfil puede tener múltiples números de teléfono
     */
    public function phones(): HasMany
    {
        return $this->hasMany(Phone::class);
    }

    /**
     * Relación 1:N con Address (direcciones)
     * Un perfil puede tener múltiples direcciones
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    // Relación con CiDocument eliminada (tabla no existe en el esquema actual)

    /**
     * Relación 1:N con Favorite (productos favoritos)
     * Un perfil puede tener múltiples productos favoritos
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Relación 1:N con Review (reseñas escritas)
     * Un perfil puede escribir múltiples reseñas
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Relación 1:N con Conversation (conversaciones como participante 1)
     * Un perfil puede participar en múltiples conversaciones
     */
    public function conversationsAsParticipant1(): HasMany
    {
        return $this->hasMany(Conversation::class, 'profile_id_1');
    }

    /**
     * Relación 1:N con Conversation (conversaciones como participante 2)
     * Un perfil puede participar en múltiples conversaciones
     */
    public function conversationsAsParticipant2(): HasMany
    {
        return $this->hasMany(Conversation::class, 'profile_id_2');
    }

    /**
     * Relación 1:N con Message (mensajes enviados)
     * Un perfil puede enviar múltiples mensajes
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Relación 1:N con Report (reportes realizados)
     * Un perfil puede realizar múltiples reportes
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    /**
     * Relación 1:N con Report (reportes revisados como admin)
     * Un perfil admin puede revisar múltiples reportes
     */
    public function adminReports(): HasMany
    {
        return $this->hasMany(Report::class, 'admin_id');
    }

    /**
     * MÉTODOS DE CONVENIENCIA
     */

    /**
     * Verificar si el perfil está completamente verificado
     */
    public function isVerified(): bool
    {
        return $this->is_verified;
    }

    /**
     * Verificar si el perfil tiene KYC aprobado (identidad verificada)
     */
    public function isKycVerified(): bool
    {
        return $this->kyc_status === 'verified';
    }

    /**
     * Verificar si el perfil es un vendedor verificado
     * (usa is_both_verified como indicador principal)
     */
    public function isSellerVerified(): bool
    {
        return $this->is_both_verified && ($this->user_type === 'seller' || $this->user_type === 'both');
    }

    /**
     * Verificar si el perfil es un comprador verificado
     * (usa is_both_verified como indicador principal)
     */
    public function isBuyerVerified(): bool
    {
        return $this->is_both_verified && ($this->user_type === 'buyer' || $this->user_type === 'both');
    }

    /**
     * Verificar si el perfil es premium
     */
    public function isPremium(): bool
    {
        return $this->is_premium_seller && 
               $this->premium_expires_at && 
               $this->premium_expires_at->isFuture();
    }

    /**
     * Obtener el nombre completo del perfil
     */
    public function getFullNameAttribute(): string
    {
        $name = $this->firstName;
        
        if ($this->middleName) {
            $name .= ' ' . $this->middleName;
        }
        
        $name .= ' ' . $this->lastName;
        
        if ($this->secondLastName) {
            $name .= ' ' . $this->secondLastName;
        }
        
        return $name;
    }

    /**
     * Obtener el nombre comercial (firstName + lastName)
     */
    public function getBusinessNameAttribute(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * Verificar si el perfil tiene datos completos
     */
    public function hasCompleteData(): bool
    {
        return $this->status === 'completeData';
    }

    /**
     * Obtener el teléfono principal
     */
    public function getPrimaryPhone(): ?Phone
    {
        return $this->phones()->where('is_primary', true)->first();
    }

    /**
     * Obtener la dirección principal
     */
    public function getPrimaryAddress(): ?Address
    {
        return $this->addresses()->first();
    }

    /**
     * Obtener la hacienda principal
     */
    public function getPrimaryRanch(): ?Ranch
    {
        return $this->ranches()->where('is_primary', true)->first();
    }

    /**
     * Actualizar el rating del perfil
     */
    public function updateRating(): void
    {
        $reviews = $this->reviews()->where('is_approved', true)->get();
        
        if ($reviews->count() > 0) {
            $this->rating = $reviews->avg('rating');
            $this->ratings_count = $reviews->count();
        } else {
            $this->rating = 0;
            $this->ratings_count = 0;
        }
        
        $this->save();
    }

    /**
     * Verificar si el perfil puede publicar productos
     */
    public function canPublishProducts(): bool
    {
        return $this->user_type === 'seller' || $this->user_type === 'both';
    }

    /**
     * Verificar si el perfil puede comprar productos
     */
    public function canBuyProducts(): bool
    {
        return $this->user_type === 'buyer' || $this->user_type === 'both';
    }

    /**
     * SCOPES PARA CONSULTAS FRECUENTES
     */

    /**
     * Scope para perfiles verificados
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope para vendedores verificados
     */
    public function scopeVerifiedSellers($query)
    {
        return $query->where('is_both_verified', true)
                    ->whereIn('user_type', ['seller', 'both']);
    }

    /**
     * Scope para compradores verificados
     */
    public function scopeVerifiedBuyers($query)
    {
        return $query->where('is_both_verified', true)
                    ->whereIn('user_type', ['buyer', 'both']);
    }

    /**
     * Scope para perfiles premium
     */
    public function scopePremium($query)
    {
        return $query->where('is_premium_seller', true)
                    ->where('premium_expires_at', '>', now());
    }

    /**
     * Scope para perfiles con datos completos
     */
    public function scopeCompleteData($query)
    {
        return $query->where('status', 'completeData');
    }

    /**
     * Scope para perfiles por tipo de usuario
     */
    public function scopeByUserType($query, string $type)
    {
        return $query->where('user_type', $type);
    }
}