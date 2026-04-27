<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class MetricsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Get date range from request or default to last 30 days
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        // Daily metrics for chart
        $dailyMetrics = $user->dailyMetrics()
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->orderBy('date')
            ->get();

        // Overall stats
        $allClosedTrades = $user->trades()->where('status', 'closed')->get();

        if ($allClosedTrades->isEmpty()) {
            return view('metrics.index', [
                'dailyMetrics' => collect(),
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'stats' => [
                    'total_trades' => 0,
                    'wins' => 0,
                    'losses' => 0,
                    'win_rate' => 0,
                    'total_pnl' => 0,
                    'avg_win' => 0,
                    'avg_loss' => 0,
                ],
            ]);
        }

        $wins = $allClosedTrades->filter(fn($t) => $t->p_l > 0);
        $losses = $allClosedTrades->filter(fn($t) => $t->p_l < 0);
        $totalPnL = $allClosedTrades->sum('p_l');

        $stats = [
            'total_trades' => $allClosedTrades->count(),
            'wins' => $wins->count(),
            'losses' => $losses->count(),
            'win_rate' => ($wins->count() / $allClosedTrades->count()) * 100,
            'total_pnl' => round($totalPnL, 2),
            'avg_win' => $wins->isNotEmpty() ? round($wins->sum('p_l') / $wins->count(), 2) : 0,
            'avg_loss' => $losses->isNotEmpty() ? round($losses->sum('p_l') / $losses->count(), 2) : 0,
        ];

        // Monthly breakdown
        $monthlyMetrics = $user->trades()
            ->where('status', 'closed')
            ->get()
            ->groupBy(fn($t) => $t->exit_date->format('Y-m'))
            ->map(fn($trades) => [
                'month' => $trades->first()->exit_date->format('M Y'),
                'pnl' => round($trades->sum('p_l'), 2),
                'trades' => $trades->count(),
                'wins' => $trades->filter(fn($t) => $t->p_l > 0)->count(),
            ])
            ->values();

        return view('metrics.index', compact(
            'dailyMetrics', 'stats', 'monthlyMetrics', 'dateFrom', 'dateTo'
        ));
    }
}
