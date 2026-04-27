<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyMetric;
use App\Models\ConsistencyMetric;
use App\Models\Trade;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class MetricsApiController extends Controller
{
    public function daily(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $query = $request->user()->dailyMetrics();

        if ($validated['date_from'] ?? null) {
            $query->whereDate('date', '>=', $validated['date_from']);
        }
        if ($validated['date_to'] ?? null) {
            $query->whereDate('date', '<=', $validated['date_to']);
        }

        $metrics = $query->orderByDesc('date')->get();
        return response()->json($metrics);
    }

    public function consistency(Request $request): JsonResponse
    {
        $user = $request->user();
        $trades = $user->trades()->where('status', 'closed')->orderBy('exit_date')->get();

        if ($trades->isEmpty()) {
            return response()->json([
                'current_streak' => 0,
                'streak_type' => null,
                'best_day_pnl' => null,
                'worst_day_pnl' => null,
            ]);
        }

        $dailyPnL = $trades->groupBy('exit_date')->map(fn($day) => $day->sum('p_l'));
        $winDays = $dailyPnL->filter(fn($pnl) => $pnl > 0)->count();
        $lossDays = $dailyPnL->filter(fn($pnl) => $pnl < 0)->count();

        $currentStreak = 0;
        $streakType = null;
        foreach ($dailyPnL->reverse() as $pnl) {
            if ($pnl > 0 && ($streakType === null || $streakType === 'win')) {
                $streakType = 'win';
                $currentStreak++;
            } elseif ($pnl < 0 && ($streakType === null || $streakType === 'loss')) {
                $streakType = 'loss';
                $currentStreak++;
            } else {
                break;
            }
        }

        return response()->json([
            'current_streak' => $currentStreak,
            'streak_type' => $streakType,
            'win_days' => $winDays,
            'loss_days' => $lossDays,
            'best_day_pnl' => $dailyPnL->max(),
            'worst_day_pnl' => $dailyPnL->min(),
            'best_day_date' => $dailyPnL->keys()->first(),
            'worst_day_date' => $dailyPnL->keys()->last(),
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $trades = $user->trades()->where('status', 'closed')->get();

        if ($trades->isEmpty()) {
            return response()->json([
                'total_trades' => 0,
                'win_count' => 0,
                'loss_count' => 0,
                'win_rate' => 0,
                'total_pnl' => 0,
                'total_pnl_percent' => 0,
                'avg_win' => 0,
                'avg_loss' => 0,
                'profit_factor' => 0,
            ]);
        }

        $wins = $trades->filter(fn($t) => $t->p_l > 0);
        $losses = $trades->filter(fn($t) => $t->p_l < 0);
        $totalPnL = $trades->sum('p_l');
        $totalCapital = $trades->sum('capital_used');

        return response()->json([
            'total_trades' => $trades->count(),
            'win_count' => $wins->count(),
            'loss_count' => $losses->count(),
            'win_rate' => ($wins->count() / $trades->count()) * 100,
            'total_pnl' => round($totalPnL, 2),
            'total_pnl_percent' => $totalCapital > 0 ? round(($totalPnL / $totalCapital) * 100, 2) : 0,
            'avg_win' => $wins->isNotEmpty() ? round($wins->sum('p_l') / $wins->count(), 2) : 0,
            'avg_loss' => $losses->isNotEmpty() ? round($losses->sum('p_l') / $losses->count(), 2) : 0,
            'profit_factor' => $losses->isNotEmpty() ? round(abs($wins->sum('p_l') / $losses->sum('p_l')), 2) : 0,
        ]);
    }
}
