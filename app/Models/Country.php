<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo Country - Países
 * 
 * Representa los países disponibles en el sistema.
 * Utilizado para la estructura geográfica de direcciones.
 */
class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'phone_code',
        'currency',
        'currency_symbol',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relación 1:N con State
     * Un país tiene múltiples estados
     */
    public function states(): HasMany
    {
        return $this->hasMany(State::class, 'countries_id');
    }

    /**
     * Scope para países activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}