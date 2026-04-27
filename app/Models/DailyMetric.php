<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyMetric extends Model
{
    protected $fillable = [
        'user_id', 'date', 'trades_count', 'wins', 'losses', 'win_rate',
        'daily_pnl', 'daily_pnl_percent', 'best_trade', 'worst_trade', 'avg_risk_reward',
    ];

    protected $casts = [
        'date' => 'date',
        'win_rate' => 'decimal:2',
        'daily_pnl' => 'decimal:2',
        'daily_pnl_percent' => 'decimal:4',
        'avg_risk_reward' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
