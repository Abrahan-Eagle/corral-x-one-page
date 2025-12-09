<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modelo Phone - Teléfonos
 * 
 * Representa los números de teléfono de usuarios y haciendas.
 * Soporta múltiples números por perfil y hacienda.
 */
class Phone extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'ranch_id',
        'operator_code_id',
        'number',
        'is_primary',
        'status',
        'approved',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'status' => 'boolean',
        'approved' => 'boolean',
    ];

    /**
     * Relación N:1 con Profile
     * Un teléfono pertenece a un perfil
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Relación N:1 con Ranch
     * Un teléfono puede pertenecer a una hacienda
     */
    public function ranch(): BelongsTo
    {
        return $this->belongsTo(Ranch::class);
    }

    /**
     * Relación N:1 con OperatorCode
     * Un teléfono pertenece a un código de operadora
     */
    public function operatorCode(): BelongsTo
    {
        return $this->belongsTo(OperatorCode::class);
    }

    /**
     * Obtener el número completo con código de operadora
     */
    public function getFullNumberAttribute(): string
    {
        return $this->operatorCode->code . $this->number;
    }

    /**
     * Scope para teléfonos activos
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope para teléfonos aprobados
     */
    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    /**
     * Scope para teléfonos principales
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}