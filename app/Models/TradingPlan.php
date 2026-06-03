<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradingPlan extends Model
{
    protected $fillable = [
        'user_id', 'max_daily_loss', 'max_weekly_loss', 'max_position_size_pct',
        'max_trades_per_day', 'rules', 'pre_trade_checklist', 'allowed_setups',
        'trading_schedule', 'market_conditions_allowed',
    ];

    protected $casts = [
        'pre_trade_checklist' => 'array',
        'allowed_setups'      => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
