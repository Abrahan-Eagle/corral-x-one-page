<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo ProductImage - Imágenes y Videos de Productos
 * 
 * Representa las imágenes y videos de los productos de ganado.
 * Soporta tanto imágenes como videos cortos (15 segundos).
 * 
 * Características principales:
 * - Relación N:1 con Product
 * - Soporte para imágenes y videos
 * - Metadatos completos (resolución, formato, compresión)
 * - Orden de visualización y imagen principal
 * - Duración para videos
 */
class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'file_url',
        'file_type',
        'alt_text',
        'is_primary',
        'sort_order',
        'duration',
        'file_size',
        'resolution',
        'format',
        'compression',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
        'duration' => 'integer',
        'file_size' => 'integer',
    ];

    /**
     * Relación N:1 con Product
     * Una imagen/video pertenece a un producto
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * MÉTODOS DE CONVENIENCIA
     */

    /**
     * Verificar si es una imagen
     */
    public function isImage(): bool
    {
        return $this->file_type === 'image';
    }

    /**
     * Verificar si es un video
     */
    public function isVideo(): bool
    {
        return $this->file_type === 'video';
    }

    /**
     * Verificar si es la imagen principal
     */
    public function isPrimary(): bool
    {
        return $this->is_primary;
    }

    /**
     * Obtener el tamaño del archivo formateado
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (!$this->file_size) {
            return 'No especificado';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Obtener la duración formateada (solo para videos)
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration || $this->file_type !== 'video') {
            return 'N/A';
        }

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;
        
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * Obtener la resolución formateada
     */
    public function getFormattedResolutionAttribute(): string
    {
        if (!$this->resolution) {
            return 'No especificada';
        }

        return $this->resolution;
    }

    /**
     * Verificar si el archivo es válido para el marketplace
     */
    public function isValidForMarketplace(): bool
    {
        // Verificar que el archivo existe
        if (!$this->file_url) {
            return false;
        }

        // Verificar duración para videos (máximo 15 segundos)
        if ($this->file_type === 'video' && $this->duration > 15) {
            return false;
        }

        // Verificar formatos permitidos
        $allowedImageFormats = ['jpg', 'jpeg', 'png', 'webp'];
        $allowedVideoFormats = ['mp4', 'mov', 'avi'];
        
        if ($this->file_type === 'image' && !in_array($this->format, $allowedImageFormats)) {
            return false;
        }
        
        if ($this->file_type === 'video' && !in_array($this->format, $allowedVideoFormats)) {
            return false;
        }

        return true;
    }

    /**
     * SCOPES PARA CONSULTAS FRECUENTES
     */

    /**
     * Scope para imágenes
     */
    public function scopeImages($query)
    {
        return $query->where('file_type', 'image');
    }

    /**
     * Scope para videos
     */
    public function scopeVideos($query)
    {
        return $query->where('file_type', 'video');
    }

    /**
     * Scope para archivos principales
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope para archivos ordenados
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope para archivos por producto
     */
    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope para archivos por formato
     */
    public function scopeByFormat($query, string $format)
    {
        return $query->where('format', $format);
    }

    /**
     * Scope para archivos por tipo
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('file_type', $type);
    }
}
