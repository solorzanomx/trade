<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountTransaction extends Model
{
    protected $fillable = [
        'user_id', 'type', 'amount', 'date', 'notes', 'ibkr_ref',
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Suma de depósitos */
    public static function totalDeposits(int $userId): float
    {
        return (float) static::where('user_id', $userId)
            ->where('type', 'deposit')
            ->sum('amount');
    }

    /** Suma de retiros */
    public static function totalWithdrawals(int $userId): float
    {
        return (float) static::where('user_id', $userId)
            ->where('type', 'withdrawal')
            ->sum('amount');
    }

    /** Capital neto aportado (depósitos - retiros) */
    public static function netCapital(int $userId): float
    {
        return static::totalDeposits($userId) - static::totalWithdrawals($userId);
    }
}
