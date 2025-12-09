<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Favorite - Productos Favoritos
 * 
 * Representa los productos marcados como favoritos por los usuarios.
 * Relación N:N entre Profile y Product con tabla pivote.
 * 
 * Características principales:
 * - Relación N:1 con Profile
 * - Relación N:1 con Product
 * - Constraint único para evitar duplicados
 * - Timestamps para seguimiento
 */
class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'product_id',
    ];

    /**
     * Relación N:1 con Profile
     * Un favorito pertenece a un perfil
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Relación N:1 con Product
     * Un favorito pertenece a un producto
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * MÉTODOS DE CONVENIENCIA
     */

    /**
     * Verificar si un producto es favorito de un perfil
     */
    public static function isFavorite(int $profileId, int $productId): bool
    {
        return self::where('profile_id', $profileId)
                  ->where('product_id', $productId)
                  ->exists();
    }

    /**
     * Agregar producto a favoritos
     */
    public static function addToFavorites(int $profileId, int $productId): bool
    {
        if (self::isFavorite($profileId, $productId)) {
            return false; // Ya es favorito
        }

        return self::create([
            'profile_id' => $profileId,
            'product_id' => $productId,
        ]) !== null;
    }

    /**
     * Remover producto de favoritos
     */
    public static function removeFromFavorites(int $profileId, int $productId): bool
    {
        return self::where('profile_id', $profileId)
                  ->where('product_id', $productId)
                  ->delete() > 0;
    }

    /**
     * Toggle favorito (agregar si no existe, remover si existe)
     */
    public static function toggleFavorite(int $profileId, int $productId): bool
    {
        if (self::isFavorite($profileId, $productId)) {
            return self::removeFromFavorites($profileId, $productId);
        } else {
            return self::addToFavorites($profileId, $productId);
        }
    }

    /**
     * Obtener favoritos de un perfil
     */
    public static function getProfileFavorites(int $profileId)
    {
        return self::where('profile_id', $profileId)
                  ->with('product.ranch.profile', 'product.images')
                  ->orderBy('created_at', 'desc')
                  ->get();
    }

    /**
     * Obtener favoritos de un perfil con paginación
     */
    public static function getProfileFavoritesPaginated(int $profileId, int $perPage = 15)
    {
        return self::where('profile_id', $profileId)
                  ->with('product.ranch.profile', 'product.images')
                  ->orderBy('created_at', 'desc')
                  ->paginate($perPage);
    }

    /**
     * Obtener el número de favoritos de un perfil
     */
    public static function getProfileFavoritesCount(int $profileId): int
    {
        return self::where('profile_id', $profileId)->count();
    }

    /**
     * Obtener el número de veces que un producto ha sido marcado como favorito
     */
    public static function getProductFavoritesCount(int $productId): int
    {
        return self::where('product_id', $productId)->count();
    }

    /**
     * SCOPES PARA CONSULTAS FRECUENTES
     */

    /**
     * Scope para favoritos de un perfil
     */
    public function scopeByProfile($query, int $profileId)
    {
        return $query->where('profile_id', $profileId);
    }

    /**
     * Scope para favoritos de un producto
     */
    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope para favoritos recientes
     */
    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Scope para favoritos con productos activos
     */
    public function scopeWithActiveProducts($query)
    {
        return $query->whereHas('product', function ($q) {
            $q->where('status', 'active');
        });
    }

    /**
     * Scope para favoritos con productos destacados
     */
    public function scopeWithFeaturedProducts($query)
    {
        return $query->whereHas('product', function ($q) {
            $q->where('is_featured', true);
        });
    }
}
