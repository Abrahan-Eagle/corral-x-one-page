<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

/**
 * Modelo Advertisement - Sistema de Publicidad en Marketplace
 * 
 * Representa los anuncios que se muestran en el marketplace.
 * Dos tipos: productos patrocinados y publicidad externa de terceros.
 * 
 * Características principales:
 * - Relación con Product (si es sponsored_product)
 * - Relación con User (admin que crea)
 * - Validación automática de fechas (start_date, end_date)
 * - Tracking de clicks e impressions
 * - Desactivación automática por fecha de expiración
 */
class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'description',
        'image_url',
        'target_url',
        'is_active',
        'start_date',
        'end_date',
        'priority',
        'clicks',
        'impressions',
        'product_id',
        'advertiser_name',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'priority' => 'integer',
        'clicks' => 'integer',
        'impressions' => 'integer',
        'product_id' => 'integer',
        'created_by' => 'integer',
    ];

    /**
     * Relación con Product (si es sponsored_product)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relación con User (admin que crea el anuncio)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope para obtener solo anuncios activos y dentro de rango de fechas
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('start_date', '<=', now())
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Scope para filtrar por tipo
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Verificar si el anuncio está activo (considerando fechas)
     */
    public function isActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->start_date->isFuture()) {
            return false;
        }

        if ($this->end_date && $this->end_date->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Incrementar contador de clicks
     */
    public function incrementClicks(): void
    {
        $this->increment('clicks');
    }

    /**
     * Incrementar contador de impressions
     */
    public function incrementImpressions(): void
    {
        $this->increment('impressions');
    }

    /**
     * Verificar si es un producto patrocinado
     */
    public function isSponsoredProduct(): bool
    {
        return $this->type === 'sponsored_product';
    }

    /**
     * Verificar si es publicidad externa
     */
    public function isExternalAd(): bool
    {
        return $this->type === 'external_ad';
    }
}
