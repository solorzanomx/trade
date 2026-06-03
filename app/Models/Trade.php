<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trade extends Model
{
    protected $fillable = [
        'user_id', 'asset_id', 'symbol', 'trade_type', 'position_direction',
        'entry_price', 'entry_date', 'entry_time', 'entry_reason',
        'exit_price', 'exit_date', 'exit_time', 'exit_reason',
        'quantity', 'capital_used', 'stop_loss', 'take_profit',
        'commission', 'p_l', 'p_l_percent', 'net_p_l',
        'status', 'emotional_state', 'mistakes_made',
        'strategy', 'setup_quality', 'followed_plan',
        'ibkr_order_id', 'ibkr_exec_id',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'entry_time' => 'string',
        'exit_date' => 'date',
        'exit_time' => 'string',
        'entry_price' => 'decimal:4',
        'exit_price' => 'decimal:4',
        'stop_loss' => 'decimal:4',
        'take_profit' => 'decimal:4',
        'p_l' => 'decimal:2',
        'p_l_percent' => 'decimal:4',
        'commission' => 'decimal:2',
        'net_p_l' => 'decimal:2',
        'followed_plan' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TradeComment::class);
    }

    public function calculatePnL(): void
    {
        if ($this->exit_price && $this->entry_price) {
            $this->p_l = ($this->exit_price - $this->entry_price) * $this->quantity;
            $this->p_l_percent = (($this->exit_price - $this->entry_price) / $this->entry_price) * 100;
            $this->net_p_l = $this->p_l - abs($this->commission ?? 0);
        }
    }

    public function getRiskRewardRatio(): ?float
    {
        if (!$this->take_profit || !$this->stop_loss) {
            return null;
        }
        $reward = abs($this->take_profit - $this->entry_price);
        $risk = abs($this->entry_price - $this->stop_loss);
        return $risk > 0 ? $reward / $risk : null;
    }
}
