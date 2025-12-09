<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RanchDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ranch_id',
        'certification_type',
        'document_url',
        'original_filename',
        'file_size',
        'order',
    ];

    protected $casts = [
        'ranch_id' => 'int',
        'file_size' => 'int',
        'order' => 'int',
    ];

    /**
     * Relación con Ranch
     */
    public function ranch(): BelongsTo
    {
        return $this->belongsTo(Ranch::class);
    }
}