<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportTemplate extends Model
{
    protected $fillable = [
        'user_id', 'name', 'slug', 'prompt', 'symbol', 'is_active', 'schedule_time',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(DailyReport::class);
    }

    public function latestReport(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(DailyReport::class)->latestOfMany('report_date');
    }
}
