<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Today's metrics
        $today = Carbon::today();
        $todayMetric = $user->dailyMetrics()->whereDate('date', $today)->first();

        // Recent trades
        $recentTrades = $user->trades()
            ->with('asset', 'comments')
            ->orderByDesc('entry_date')
            ->limit(5)
            ->get();

        // Overall stats
        $allClosedTrades = $user->trades()->where('status', 'closed')->get();
        $totalPnL = $allClosedTrades->sum('p_l');
        $winRate = $allClosedTrades->isNotEmpty()
            ? ($allClosedTrades->filter(fn($t) => $t->p_l > 0)->count() / $allClosedTrades->count()) * 100
            : 0;

        // Today's news
        $todayNews = $user->newsSummaries()
            ->with('asset')
            ->whereDate('date', $today)
            ->limit(3)
            ->get();

        // Current streak
        $trades = $user->trades()->where('status', 'closed')->orderBy('exit_date')->get();
        $currentStreak = 0;
        $streakType = null;

        if ($trades->isNotEmpty()) {
            $dailyPnL = $trades->groupBy('exit_date')->map(fn($day) => $day->sum('p_l'));
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
        }

        return view('dashboard.index', compact(
            'todayMetric', 'recentTrades', 'totalPnL', 'winRate',
            'todayNews', 'currentStreak', 'streakType'
        ));
    }
}
