<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo City - Ciudades
 * 
 * Representa las ciudades de un estado.
 * Utilizado para la estructura geográfica de direcciones.
 */
class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'state_id',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relación N:1 con State
     * Una ciudad pertenece a un estado
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Relación 1:N con Address
     * Una ciudad tiene múltiples direcciones
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Relación 1:N con Parish
     * Una ciudad tiene múltiples parroquias
     */
    public function parishes(): HasMany
    {
        return $this->hasMany(Parish::class);
    }

    /**
     * Scope para ciudades activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}