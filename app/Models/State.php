<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modelo State - Estados/Provincias
 * 
 * Representa los estados o provincias de un país.
 * Utilizado para la estructura geográfica de direcciones.
 */
class State extends Model
{
    use HasFactory;

    protected $fillable = [
        'countries_id',
        'name',
    ];

    /**
     * Relación N:1 con Country
     * Un estado pertenece a un país
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'countries_id');
    }

    /**
     * Relación 1:N con City
     * Un estado tiene múltiples ciudades
     */
    public function cities(): HasMany
    {
        return $this->hasMany(City::class);
    }
}