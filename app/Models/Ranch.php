<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo Ranch - Haciendas/Ranchos
 * 
 * Representa las haciendas o ranchos donde se cría y vende ganado.
 * Es el "vendedor" en el marketplace - cada hacienda puede tener múltiples productos.
 * 
 * Características principales:
 * - Relación 1:1 con Address (dirección principal)
 * - Relación 1:N con Product (productos de ganado)
 * - Relación 1:N con Phone (teléfonos de contacto)
 * - Sistema de verificación y métricas de ventas
 * - Políticas de entrega y contacto
 */
class Ranch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'profile_id',
        'name',
        'legal_name',
        'tax_id',
        'business_description',
        'certifications',
        'business_license_url',
        'address_id',
        'is_primary',
        'delivery_policy',
        'return_policy',
        'contact_hours',
        'accepts_visits',
        'avg_rating',
        'total_sales',
        'last_sale_at',
    ];

    protected $casts = [
        'id' => 'int',
        'profile_id' => 'int',
        'address_id' => 'int',
        'is_primary' => 'boolean',
        'certifications' => 'array',
        'accepts_visits' => 'boolean',
        'avg_rating' => 'decimal:2',
        'total_sales' => 'integer',
        'last_sale_at' => 'datetime',
    ];

    /**
     * Relación N:1 con Profile
     * Una hacienda pertenece a un perfil
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Relación N:1 con Address
     * Una hacienda pertenece a una dirección
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    /**
     * Relación 1:N con Product
     * Una hacienda puede tener múltiples productos
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Relación 1:N con Phone
     * Una hacienda puede tener múltiples teléfonos
     */
    public function phones(): HasMany
    {
        return $this->hasMany(Phone::class);
    }

    /**
     * Relación 1:1 con Phone (teléfono principal)
     * Una hacienda tiene un teléfono principal
     */
    public function phone(): HasOne
    {
        return $this->hasOne(Phone::class);
    }

    // Relación con RifDocument eliminada (tabla no existe en el esquema actual)

    /**
     * Relación 1:N con RanchDocument
     * Una hacienda puede tener múltiples documentos (hasta 5)
     */
    public function documents(): HasMany
    {
        return $this->hasMany(RanchDocument::class)->orderBy('order');
    }

    /**
     * Relación 1:N con Review
     * Una hacienda puede recibir múltiples reseñas
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Relación 1:N con Conversation
     * Una hacienda puede participar en múltiples conversaciones
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * MÉTODOS DE CONVENIENCIA
     */

    /**
     * Verificar si la hacienda acepta órdenes
     */
    // Campo accepts_orders no existe en el esquema actual

    /**
     * Verificar si la hacienda acepta visitas
     */
    public function acceptsVisits(): bool
    {
        return $this->accepts_visits;
    }

    /**
     * Verificar si la hacienda es la principal del perfil
     */
    public function isPrimary(): bool
    {
        return $this->is_primary;
    }

    /**
     * Obtener el teléfono principal de la hacienda
     */
    public function getPrimaryPhone(): ?Phone
    {
        return $this->phones()->where('is_primary', true)->first();
    }

    /**
     * Obtener productos activos de la hacienda
     */
    public function getActiveProducts()
    {
        return $this->products()->where('status', 'active');
    }

    /**
     * Obtener productos destacados de la hacienda
     */
    public function getFeaturedProducts()
    {
        return $this->products()->where('is_featured', true);
    }

    /**
     * Actualizar métricas de la hacienda
     */
    public function updateMetrics(): void
    {
        // Actualizar rating promedio
        $reviews = $this->reviews()->where('is_approved', true)->get();
        
        if ($reviews->count() > 0) {
            $this->avg_rating = $reviews->avg('rating');
        } else {
            $this->avg_rating = 0;
        }

        // Actualizar total de ventas
        $this->total_sales = $this->products()
            ->where('status', 'sold')
            ->count();

        // Actualizar última venta
        $lastSale = $this->products()
            ->where('status', 'sold')
            ->orderBy('updated_at', 'desc')
            ->first();

        $this->last_sale_at = $lastSale ? $lastSale->updated_at : null;

        $this->save();
    }

    /**
     * Verificar si la hacienda tiene certificaciones
     */
    public function hasCertifications(): bool
    {
        return !empty($this->certifications);
    }

    /**
     * Obtener certificaciones como array
     */
    public function getCertificationsArray(): array
    {
        return $this->certifications ?? [];
    }

    /**
     * SCOPES PARA CONSULTAS FRECUENTES
     */

    /**
     * Scope para haciendas que aceptan órdenes
     */
    // Scope acceptsOrders removido (columna no existe)

    /**
     * Scope para haciendas que aceptan visitas
     */
    public function scopeAcceptsVisits($query)
    {
        return $query->where('accepts_visits', true);
    }

    /**
     * Scope para haciendas principales
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope para haciendas por especialización
     */
    // Scope bySpecialization removido (columna no existe)

    /**
     * Scope para haciendas con rating mínimo
     */
    public function scopeMinRating($query, float $rating)
    {
        return $query->where('avg_rating', '>=', $rating);
    }

    /**
     * Scope para haciendas por ubicación (radio en km)
     */
    public function scopeWithinRadius($query, float $latitude, float $longitude, int $radiusKm)
    {
        return $query->whereHas('address', function ($q) use ($latitude, $longitude, $radiusKm) {
            $q->whereRaw("
                (6371 * acos(cos(radians(?)) 
                * cos(radians(latitude)) 
                * cos(radians(longitude) - radians(?)) 
                + sin(radians(?)) 
                * sin(radians(latitude)))) <= ?
            ", [$latitude, $longitude, $latitude, $radiusKm]);
        });
    }
}
