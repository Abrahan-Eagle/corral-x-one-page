<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Category - Categorías de Productos
 * 
 * Representa las categorías para clasificar los productos de ganado.
 * Soporta estructura jerárquica con categorías padre e hijas.
 * 
 * Características principales:
 * - Estructura jerárquica (parent_id)
 * - Relación N:N con Product
 * - Campos de personalización (icono, color)
 * - Sistema de ordenamiento
 * - Estado activo/inactivo
 */
class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'sort_order',
        'is_active',
        'icon',
        'color',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Relación N:1 con Category (categoría padre)
     * Una categoría puede tener una categoría padre
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Relación 1:N con Category (categorías hijas)
     * Una categoría puede tener múltiples categorías hijas
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Relación N:N con Product
     * Una categoría puede tener múltiples productos
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_categories');
    }

    /**
     * MÉTODOS DE CONVENIENCIA
     */

    /**
     * Verificar si la categoría está activa
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Verificar si es una categoría padre
     */
    public function isParent(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Verificar si es una categoría hija
     */
    public function isChild(): bool
    {
        return !is_null($this->parent_id);
    }

    /**
     * Obtener todas las categorías hijas recursivamente
     */
    public function getAllChildren()
    {
        $children = collect();
        
        foreach ($this->children as $child) {
            $children->push($child);
            $children = $children->merge($child->getAllChildren());
        }
        
        return $children;
    }

    /**
     * Obtener la ruta completa de la categoría
     */
    public function getFullPathAttribute(): string
    {
        $path = [$this->name];
        $parent = $this->parent;
        
        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }
        
        return implode(' > ', $path);
    }

    /**
     * Obtener el número de productos en esta categoría
     */
    public function getProductsCount(): int
    {
        return $this->products()->count();
    }

    /**
     * Obtener el número de productos activos en esta categoría
     */
    public function getActiveProductsCount(): int
    {
        return $this->products()->where('status', 'active')->count();
    }

    /**
     * Verificar si la categoría tiene productos
     */
    public function hasProducts(): bool
    {
        return $this->getProductsCount() > 0;
    }

    /**
     * Verificar si la categoría tiene categorías hijas
     */
    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    /**
     * SCOPES PARA CONSULTAS FRECUENTES
     */

    /**
     * Scope para categorías activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para categorías padre
     */
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope para categorías hijas
     */
    public function scopeChildren($query)
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Scope para categorías por padre
     */
    public function scopeByParent($query, int $parentId)
    {
        return $query->where('parent_id', $parentId);
    }

    /**
     * Scope para categorías ordenadas
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope para categorías por slug
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Scope para categorías con productos
     */
    public function scopeWithProducts($query)
    {
        return $query->whereHas('products');
    }

    /**
     * Scope para categorías con productos activos
     */
    public function scopeWithActiveProducts($query)
    {
        return $query->whereHas('products', function ($q) {
            $q->where('status', 'active');
        });
    }
}
