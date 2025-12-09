<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Modelo Product - Productos de Ganado
 * 
 * Representa los productos/lotes de ganado disponibles en el marketplace.
 * Es el elemento central del e-commerce - cada producto pertenece a una hacienda.
 * 
 * Características principales:
 * - Relación N:1 con Ranch (vendedor)
 * - Relación 1:N con ProductImage (galería de fotos/videos)
 * - Relación N:N con Category (categorización)
 * - Campos específicos para ganado (raza, peso, salud, etc.)
 * - Sistema de precios y disponibilidad
 * - Métricas de visualización y destacado
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'ranch_id',
        'state_id', // Estado donde se encuentra el producto
        'title',
        'description',
        'type',
        'breed',
        'age',
        'quantity',
        'price',
        'currency',
        'status',
        'weight_avg',
        'weight_min',
        'weight_max',
        'sex',
        'purpose',
        'feeding_type', // ✅ NUEVO: Tipo de alimento
        'health_certificate_url',
        'vaccines_applied',
        'last_vaccination',
        'is_vaccinated',
        'feeding_info',
        'handling_info',
        'origin_farm',
        'available_from',
        'available_until',
        'delivery_method',
        'delivery_cost',
        'delivery_radius_km',
        'price_type',
        'negotiable',
        'min_order_quantity',
        'is_featured',
        'views',
        'transportation_included',
        'documentation_included',
        'genetic_tests_available',
        'genetic_test_results',
        'bloodline',
    ];

    protected $casts = [
        'id' => 'int',
        'ranch_id' => 'int',
        'state_id' => 'int',
        'price' => 'decimal:2',
        'weight_avg' => 'decimal:2',
        'weight_min' => 'decimal:2',
        'weight_max' => 'decimal:2',
        'delivery_cost' => 'decimal:2',
        'min_order_quantity' => 'decimal:2',
        'last_vaccination' => 'date',
        'available_from' => 'date',
        'available_until' => 'date',
        'vaccines_applied' => 'array',
        'genetic_test_results' => 'array',
        'is_vaccinated' => 'boolean',
        'negotiable' => 'boolean',
        'is_featured' => 'boolean',
        'views' => 'integer',
        'genetic_tests_available' => 'boolean',
        'documentation_included' => 'array', // JSON array
        'transportation_included' => 'string',
    ];

    /**
     * Relación N:1 con Ranch
     * Un producto pertenece a una hacienda
     */
    public function ranch(): BelongsTo
    {
        return $this->belongsTo(Ranch::class);
    }

    /**
     * Relación N:1 con State
     * Un producto pertenece a un estado
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Relación 1:N con ProductImage
     * Un producto puede tener múltiples imágenes/videos
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Relación N:N con Category
     * Un producto puede pertenecer a múltiples categorías
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    /**
     * Relación 1:N con Favorite
     * Un producto puede ser favorito de múltiples usuarios
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /**
     * Relación 1:N con Review
     * Un producto puede recibir múltiples reseñas
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Relación 1:N con Conversation
     * Un producto puede generar múltiples conversaciones
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Relación polimórfica con Report
     * Un producto puede ser reportado múltiples veces
     */
    public function reports(): MorphMany
    {
        return $this->morphMany(Report::class, 'reportable');
    }

    /**
     * MÉTODOS DE CONVENIENCIA
     */

    /**
     * Verificar si el producto está activo
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Verificar si el producto está destacado
     */
    public function isFeatured(): bool
    {
        return $this->is_featured;
    }

    /**
     * Verificar si el producto está vacunado
     */
    public function isVaccinated(): bool
    {
        return $this->is_vaccinated;
    }

    /**
     * Verificar si el precio es negociable
     */
    public function isNegotiable(): bool
    {
        return $this->negotiable;
    }

    /**
     * Verificar si el producto tiene pruebas genéticas
     */
    public function hasGeneticTests(): bool
    {
        return $this->genetic_tests_available;
    }

    /**
     * Obtener la imagen principal del producto
     */
    public function getPrimaryImage(): ?ProductImage
    {
        return $this->images()->where('is_primary', true)->first();
    }

    /**
     * Obtener todas las imágenes del producto ordenadas
     */
    public function getOrderedImages()
    {
        return $this->images()->orderBy('sort_order')->get();
    }

    /**
     * Obtener el rating promedio del producto
     */
    public function getAverageRating(): float
    {
        $reviews = $this->reviews()->where('is_approved', true)->get();
        
        if ($reviews->count() > 0) {
            return round($reviews->avg('rating'), 2);
        }
        
        return 0;
    }

    /**
     * Obtener el número de reseñas aprobadas
     */
    public function getReviewsCount(): int
    {
        return $this->reviews()->where('is_approved', true)->count();
    }

    /**
     * Incrementar el contador de vistas
     */
    public function incrementViews(): void
    {
        $this->increment('views');
    }

    /**
     * Verificar si el producto está disponible
     */
    public function isAvailable(): bool
    {
        // Verificar status - solo active está disponible
        if ($this->status !== 'active') {
            return false;
        }
        
        $now = now();
        
        // Verificar fechas de disponibilidad
        if ($this->available_from && $this->available_from->isFuture()) {
            return false;
        }
        
        if ($this->available_until && $this->available_until->isPast()) {
            return false;
        }
        
        // Verificar cantidad disponible
        return $this->quantity > 0;
    }

    /**
     * Obtener el precio formateado con moneda
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->price, 2);
    }

    /**
     * Obtener el peso promedio formateado
     */
    public function getFormattedWeightAttribute(): string
    {
        if ($this->weight_avg) {
            return number_format($this->weight_avg, 2) . ' kg';
        }
        
        return 'No especificado';
    }

    /**
     * SCOPES PARA CONSULTAS FRECUENTES
     */

    /**
     * Scope para productos activos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para productos destacados
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope para productos por tipo
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para productos por raza
     */
    public function scopeByBreed($query, string $breed)
    {
        return $query->where('breed', $breed);
    }

    /**
     * Scope para productos por sexo
     */
    public function scopeBySex($query, string $sex)
    {
        return $query->where('sex', $sex);
    }

    /**
     * Scope para productos por propósito
     */
    public function scopeByPurpose($query, string $purpose)
    {
        return $query->where('purpose', $purpose);
    }

    /**
     * Scope para productos vacunados
     */
    public function scopeVaccinated($query)
    {
        return $query->where('is_vaccinated', true);
    }

    /**
     * Scope para productos por rango de precio
     */
    public function scopePriceRange($query, float $minPrice, float $maxPrice)
    {
        return $query->whereBetween('price', [$minPrice, $maxPrice]);
    }

    /**
     * Scope para productos por rango de peso
     */
    public function scopeWeightRange($query, float $minWeight, float $maxWeight)
    {
        return $query->whereBetween('weight_avg', [$minWeight, $maxWeight]);
    }

    /**
     * Scope para productos por edad
     */
    public function scopeByAge($query, int $age)
    {
        return $query->where('age', $age);
    }

    /**
     * Scope para productos por ubicación (radio en km)
     */
    public function scopeWithinRadius($query, float $latitude, float $longitude, int $radiusKm)
    {
        return $query->whereHas('ranch.address', function ($q) use ($latitude, $longitude, $radiusKm) {
            $q->whereRaw("
                (6371 * acos(cos(radians(?)) 
                * cos(radians(latitude)) 
                * cos(radians(longitude) - radians(?)) 
                + sin(radians(?)) 
                * sin(radians(latitude)))) <= ?
            ", [$latitude, $longitude, $latitude, $radiusKm]);
        });
    }

    /**
     * Scope para productos por categoría
     */
    public function scopeByCategory($query, int $categoryId)
    {
        return $query->whereHas('categories', function ($q) use ($categoryId) {
            $q->where('category_id', $categoryId);
        });
    }

    /**
     * Scope para productos más vistos
     */
    public function scopeMostViewed($query, int $limit = 10)
    {
        return $query->orderBy('views', 'desc')->limit($limit);
    }

    /**
     * Scope para productos más recientes
     */
    public function scopeLatest($query, int $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }
}