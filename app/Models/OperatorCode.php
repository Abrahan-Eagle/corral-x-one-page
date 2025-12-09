<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo OperatorCode - Códigos de Operadora
 * 
 * Representa los códigos de operadoras telefónicas.
 * Utilizado para validar números de teléfono.
 */
class OperatorCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relación 1:N con Phone
     * Un código de operadora puede ser usado por múltiples teléfonos
     */
    public function phones(): HasMany
    {
        return $this->hasMany(Phone::class);
    }

    /**
     * Scope para códigos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
