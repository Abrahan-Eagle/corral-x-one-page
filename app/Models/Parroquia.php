<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Parroquia extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city_id',
    ];

    /**
     * Get the city that owns the parroquia.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}
