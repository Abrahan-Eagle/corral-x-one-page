<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Modelo Address - Direcciones
 * 
 * Representa las direcciones de usuarios y haciendas.
 * Incluye coordenadas GPS para funcionalidades de geolocalización.
 */
class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'ranch_id',
        'city_id',
        'parish_id',
        'adressses',
        'latitude',
        'longitude',
        'status',
        'level', // users, ranches, cattle
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    /**
     * Relación N:1 con Profile
     * Una dirección pertenece a un perfil
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Relación N:1 con Ranch
     * Una dirección puede pertenecer a una hacienda
     */
    public function ranch(): BelongsTo
    {
        return $this->belongsTo(Ranch::class);
    }

    /**
     * Relación N:1 con City
     * Una dirección pertenece a una ciudad
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Relación 1:1 con Ranch (dirección principal de la hacienda)
     * Una dirección puede ser la dirección principal de una hacienda
     */
    public function ranchAsPrimary(): HasOne
    {
        return $this->hasOne(Ranch::class, 'address_id');
    }

    /**
     * Obtener la dirección completa formateada
     */
    public function getFullAddressAttribute(): string
    {
        return ($this->adressses ?: '') . ', ' .
               ($this->city->name ?? '') . ', ' .
               ($this->city->state->name ?? '') . ', ' .
               ($this->city->state->country->name ?? '');
    }

    /**
     * Obtener coordenadas como array
     */
    public function getCoordinatesAttribute(): array
    {
        return [
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }

    /**
     * Scope para direcciones verificadas
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'completeData');
    }

    /**
     * Scope para direcciones por ciudad
     */
    public function scopeByCity($query, int $cityId)
    {
        return $query->where('city_id', $cityId);
    }

    /**
     * Scope para direcciones por estado
     */
    public function scopeByState($query, int $stateId)
    {
        return $query->whereHas('city', function ($q) use ($stateId) {
            $q->where('state_id', $stateId);
        });
    }
}