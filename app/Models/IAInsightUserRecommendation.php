<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IAInsightUserRecommendation extends Model
{
    use HasFactory;

    protected $table = 'ia_insight_user_recommendations';

    protected $fillable = [
        'user_id',
        'recommendation_key',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'user_id' => 'int',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

