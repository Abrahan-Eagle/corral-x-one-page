<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parish extends Model
{
    use HasFactory;

    protected $table = 'parroquias'; // Usar la misma tabla que Parroquia

    protected $fillable = [
        'name',
        'city_id',
    ];

    /**
     * Relación con la ciudad
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
