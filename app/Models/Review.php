<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Review - Reseñas y Calificaciones
 * 
 * Representa las reseñas y calificaciones de productos y haciendas.
 * Sistema de moderación con aprobación por administradores.
 * 
 * Características principales:
 * - Relación N:1 con Profile (autor de la reseña)
 * - Relación N:1 con Product (producto reseñado)
 * - Relación N:1 con Ranch (hacienda reseñada)
 * - Sistema de rating 1-5 estrellas
 * - Moderación y aprobación
 * - Compra verificada
 */
class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'profile_id',
        'product_id',
        'ranch_id',
        'rating',
        'comment',
        'is_verified_purchase',
        'is_approved',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_verified_purchase' => 'boolean',
        'is_approved' => 'boolean',
    ];

    /**
     * Relación N:1 con Profile
     * Una reseña pertenece a un perfil
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Relación N:1 con Order
     * Una reseña puede originarse en un pedido específico
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relación N:1 con Product
     * Una reseña pertenece a un producto
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Relación N:1 con Ranch
     * Una reseña pertenece a una hacienda
     */
    public function ranch(): BelongsTo
    {
        return $this->belongsTo(Ranch::class);
    }

    /**
     * MÉTODOS DE CONVENIENCIA
     */

    /**
     * Verificar si la reseña está aprobada
     */
    public function isApproved(): bool
    {
        return $this->is_approved;
    }

    /**
     * Verificar si es una compra verificada
     */
    public function isVerifiedPurchase(): bool
    {
        return $this->is_verified_purchase;
    }

    /**
     * Aprobar la reseña
     */
    public function approve(): bool
    {
        $this->is_approved = true;
        return $this->save();
    }

    /**
     * Rechazar la reseña
     */
    public function reject(): bool
    {
        $this->is_approved = false;
        return $this->save();
    }

    /**
     * Obtener el rating como estrellas
     */
    public function getStarsAttribute(): string
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    /**
     * Obtener el rating como texto
     */
    public function getRatingTextAttribute(): string
    {
        $ratings = [
            1 => 'Muy malo',
            2 => 'Malo',
            3 => 'Regular',
            4 => 'Bueno',
            5 => 'Excelente'
        ];

        return $ratings[$this->rating] ?? 'Sin calificar';
    }

    /**
     * Verificar si la reseña tiene comentario
     */
    public function hasComment(): bool
    {
        return !empty($this->comment);
    }

    /**
     * Obtener el comentario truncado
     */
    public function getTruncatedCommentAttribute(int $length = 100): string
    {
        if (!$this->comment) {
            return '';
        }

        return strlen($this->comment) > $length 
            ? substr($this->comment, 0, $length) . '...'
            : $this->comment;
    }

    /**
     * Obtener reseñas de un producto
     */
    public static function getProductReviews(int $productId, bool $approvedOnly = true)
    {
        $query = self::where('product_id', $productId)
                    ->with('profile')
                    ->orderBy('created_at', 'desc');

        if ($approvedOnly) {
            $query->where('is_approved', true);
        }

        return $query->get();
    }

    /**
     * Obtener reseñas de una hacienda
     */
    public static function getRanchReviews(int $ranchId, bool $approvedOnly = true)
    {
        $query = self::where('ranch_id', $ranchId)
                    ->with('profile')
                    ->orderBy('created_at', 'desc');

        if ($approvedOnly) {
            $query->where('is_approved', true);
        }

        return $query->get();
    }

    /**
     * Obtener el rating promedio de un producto
     */
    public static function getProductAverageRating(int $productId): float
    {
        $reviews = self::where('product_id', $productId)
                      ->where('is_approved', true)
                      ->get();

        if ($reviews->count() > 0) {
            return round($reviews->avg('rating'), 2);
        }

        return 0;
    }

    /**
     * Obtener el rating promedio de una hacienda
     */
    public static function getRanchAverageRating(int $ranchId): float
    {
        $reviews = self::where('ranch_id', $ranchId)
                      ->where('is_approved', true)
                      ->get();

        if ($reviews->count() > 0) {
            return round($reviews->avg('rating'), 2);
        }

        return 0;
    }

    /**
     * Obtener el número de reseñas de un producto
     */
    public static function getProductReviewsCount(int $productId): int
    {
        return self::where('product_id', $productId)
                  ->where('is_approved', true)
                  ->count();
    }

    /**
     * Obtener el número de reseñas de una hacienda
     */
    public static function getRanchReviewsCount(int $ranchId): int
    {
        return self::where('ranch_id', $ranchId)
                  ->where('is_approved', true)
                  ->count();
    }

    /**
     * SCOPES PARA CONSULTAS FRECUENTES
     */

    /**
     * Scope para reseñas aprobadas
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope para reseñas pendientes
     */
    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    /**
     * Scope para reseñas por rating
     */
    public function scopeByRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope para reseñas con rating mínimo
     */
    public function scopeMinRating($query, int $rating)
    {
        return $query->where('rating', '>=', $rating);
    }

    /**
     * Scope para reseñas de compras verificadas
     */
    public function scopeVerifiedPurchases($query)
    {
        return $query->where('is_verified_purchase', true);
    }

    /**
     * Scope para reseñas por producto
     */
    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope para reseñas por hacienda
     */
    public function scopeByRanch($query, int $ranchId)
    {
        return $query->where('ranch_id', $ranchId);
    }

    /**
     * Scope para reseñas por perfil
     */
    public function scopeByProfile($query, int $profileId)
    {
        return $query->where('profile_id', $profileId);
    }

    /**
     * Scope para reseñas recientes
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Scope para reseñas con comentarios
     */
    public function scopeWithComments($query)
    {
        return $query->whereNotNull('comment')
                    ->where('comment', '!=', '');
    }
}
