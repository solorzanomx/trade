<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntry extends Model
{
    protected $fillable = [
        'user_id', 'entry_date',
        'pre_bias', 'pre_plan', 'watchlist', 'pre_goals', 'mood_start', 'market_conditions',
        'grade', 'what_went_well', 'what_to_improve', 'lesson_learned',
        'goals_tomorrow', 'mood_end', 'post_review', 'followed_plan',
    ];

    protected $casts = [
        'entry_date'    => 'date',
        'watchlist'     => 'array',
        'followed_plan' => 'boolean',
        'mood_start'    => 'integer',
        'mood_end'      => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getGradeColor(): string
    {
        return match($this->grade) {
            'A+', 'A' => 'var(--green)',
            'B'       => '#5b8af5',
            'C'       => '#f9a825',
            'D', 'F'  => 'var(--red)',
            default   => 'var(--text-muted)',
        };
    }

    public function getMoodEmoji(int $mood): string
    {
        return match(true) {
            $mood >= 9 => '🔥',
            $mood >= 7 => '😊',
            $mood >= 5 => '😐',
            $mood >= 3 => '😕',
            default    => '😞',
        };
    }
}
