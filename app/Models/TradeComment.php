<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradeComment extends Model
{
    protected $fillable = ['trade_id', 'comment'];

    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }
}
