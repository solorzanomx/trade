<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyNewsSummary extends Model
{
    protected $table = 'daily_news_summaries';

    protected $fillable = [
        'user_id', 'asset_id', 'date', 'summary', 'source', 'key_points', 'sentiment',
    ];

    protected $casts = [
        'date' => 'date',
        'key_points' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}
