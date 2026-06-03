<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioPosition extends Model
{
    protected $fillable = [
        'user_id', 'symbol', 'name', 'asset_type', 'sector',
        'shares', 'avg_cost', 'current_price', 'target_price', 'stop_loss',
        'dividends_received', 'entry_date', 'thesis', 'notes',
        'status', 'exit_price', 'exit_date',
    ];

    protected $casts = [
        'entry_date'          => 'date',
        'exit_date'           => 'date',
        'shares'              => 'decimal:6',
        'avg_cost'            => 'decimal:4',
        'current_price'       => 'decimal:4',
        'target_price'        => 'decimal:4',
        'stop_loss'           => 'decimal:4',
        'dividends_received'  => 'decimal:2',
        'exit_price'          => 'decimal:4',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Valor actual de la posición
    public function getCurrentValue(): float
    {
        $price = $this->exit_price ?? $this->current_price ?? $this->avg_cost;
        return (float) $this->shares * (float) $price;
    }

    // Costo total de la posición
    public function getCostBasis(): float
    {
        return (float) $this->shares * (float) $this->avg_cost;
    }

    // P&L no realizado (o realizado si está cerrada)
    public function getUnrealizedPnl(): float
    {
        return $this->getCurrentValue() - $this->getCostBasis() + (float) $this->dividends_received;
    }

    // % de retorno
    public function getReturnPercent(): float
    {
        $basis = $this->getCostBasis();
        return $basis > 0 ? ($this->getUnrealizedPnl() / $basis) * 100 : 0;
    }

    // % hacia el target
    public function getTargetProgress(): ?float
    {
        if (!$this->target_price || !$this->current_price) return null;
        $range = (float) $this->target_price - (float) $this->avg_cost;
        if ($range <= 0) return null;
        $achieved = (float) $this->current_price - (float) $this->avg_cost;
        return min(100, max(0, ($achieved / $range) * 100));
    }
}
