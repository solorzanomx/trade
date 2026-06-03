<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class MetricsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $earliestTrade = $user->trades()->where('status', 'closed')->orderBy('entry_date')->first();
        $defaultFrom = $earliestTrade ? $earliestTrade->entry_date->format('Y-m-d') : now()->subYear()->format('Y-m-d');
        $dateFrom = $request->input('date_from', $defaultFrom);
        $dateTo   = $request->input('date_to', now()->format('Y-m-d'));

        $dailyMetrics = $user->dailyMetrics()
            ->whereDate('date', '>=', $dateFrom)
            ->whereDate('date', '<=', $dateTo)
            ->orderBy('date')
            ->get();

        $allClosedTrades = $user->trades()->where('status', 'closed')->get();

        if ($allClosedTrades->isEmpty()) {
            return view('metrics.index', [
                'dailyMetrics'      => collect(),
                'monthlyMetrics'    => collect(),
                'equityCurve'       => [],
                'drawdownCurve'     => [],
                'strategyBreakdown' => collect(),
                'psychBreakdown'    => collect(),
                'dateFrom'          => $dateFrom,
                'dateTo'            => $dateTo,
                'stats'             => $this->emptyStats(),
                'advanced'          => $this->emptyAdvanced(),
            ]);
        }

        // ── Base stats ────────────────────────────────────────────────────────
        $wins   = $allClosedTrades->filter(fn($t) => $t->p_l > 0);
        $losses = $allClosedTrades->filter(fn($t) => $t->p_l < 0);
        $totalPnL       = $allClosedTrades->sum('p_l');
        $totalCommission = $allClosedTrades->sum('commission');

        $stats = [
            'total_trades'     => $allClosedTrades->count(),
            'wins'             => $wins->count(),
            'losses'           => $losses->count(),
            'win_rate'         => ($wins->count() / $allClosedTrades->count()) * 100,
            'total_pnl'        => round($totalPnL, 2),
            'avg_win'          => $wins->isNotEmpty()   ? round($wins->avg('p_l'), 2)   : 0,
            'avg_loss'         => $losses->isNotEmpty() ? round($losses->avg('p_l'), 2) : 0,
            'total_commission' => round($totalCommission, 2),
            'net_pnl'          => round($totalPnL - $totalCommission, 2),
        ];

        // ── Trades sorted for time-series ─────────────────────────────────────
        $sorted = $allClosedTrades->sortBy(fn($t) => $t->exit_date->format('Y-m-d') . ($t->exit_time ?? ''));

        // ── Equity curve & drawdown ───────────────────────────────────────────
        $equityCurve   = [];
        $drawdownCurve = [];
        $cumPnl          = 0;
        $peak            = PHP_INT_MIN;
        $maxDrawdownAbs  = 0; // max drawdown in dollars (negative)
        $currentDrawdown = 0;

        foreach ($sorted as $trade) {
            $cumPnl += (float) $trade->p_l;
            $label   = $trade->exit_date->format('d/m/y');

            if ($cumPnl > $peak) {
                $peak = $cumPnl;
            }

            // Dollar-based drawdown from peak — avoids division by tiny peak values
            $dd = $peak !== PHP_INT_MIN ? $cumPnl - $peak : 0;

            if ($dd < $maxDrawdownAbs) {
                $maxDrawdownAbs = $dd;
            }

            $currentDrawdown = $dd;

            $equityCurve[]   = ['date' => $label, 'value' => round($cumPnl, 2)];
            $drawdownCurve[] = ['date' => $label, 'value' => round($dd, 2)];
        }
        $maxDrawdown = $maxDrawdownAbs; // dollar value (negative means loss from peak)

        // ── R-Multiple & Expectancy ───────────────────────────────────────────
        $rMultiples = [];
        foreach ($sorted as $trade) {
            if ($trade->stop_loss && $trade->entry_price && $trade->quantity) {
                $riskPerUnit = abs((float)$trade->entry_price - (float)$trade->stop_loss);
                $riskTotal   = $riskPerUnit * (float)$trade->quantity;
                if ($riskTotal > 0) {
                    $rMultiples[] = (float)$trade->p_l / $riskTotal;
                }
            }
        }
        $expectancy = count($rMultiples) > 0 ? array_sum($rMultiples) / count($rMultiples) : null;

        // ── Profit Factor ─────────────────────────────────────────────────────
        $grossWins   = $wins->sum('p_l');
        $grossLosses = abs($losses->sum('p_l'));
        $profitFactor = $grossLosses > 0 ? round($grossWins / $grossLosses, 2) : null;

        // ── Sharpe Ratio (annualised, daily returns) ──────────────────────────
        $dailyReturns = $sorted
            ->groupBy(fn($t) => $t->exit_date->format('Y-m-d'))
            ->map(fn($trades) => (float) $trades->sum('p_l'))
            ->values()
            ->toArray();

        $sharpe = null;
        if (count($dailyReturns) > 1) {
            $avg = array_sum($dailyReturns) / count($dailyReturns);
            $variance = array_sum(array_map(fn($r) => ($r - $avg) ** 2, $dailyReturns)) / (count($dailyReturns) - 1);
            $stdDev = sqrt($variance);
            if ($stdDev > 0) {
                $sharpe = round(($avg / $stdDev) * sqrt(252), 2);
            }
        }

        // ── Consecutive losses / wins ─────────────────────────────────────────
        $maxConsecWins = $maxConsecLosses = 0;
        $curWins = $curLosses = 0;
        foreach ($sorted as $trade) {
            if ($trade->p_l > 0) {
                $curWins++;
                $curLosses = 0;
                if ($curWins > $maxConsecWins) $maxConsecWins = $curWins;
            } else {
                $curLosses++;
                $curWins = 0;
                if ($curLosses > $maxConsecLosses) $maxConsecLosses = $curLosses;
            }
        }

        $advanced = [
            'max_drawdown'      => round($maxDrawdown, 2),
            'current_drawdown'  => round($currentDrawdown, 2),
            'expectancy'        => $expectancy !== null ? round($expectancy, 2) : null,
            'profit_factor'     => $profitFactor,
            'sharpe'            => $sharpe,
            'max_consec_wins'   => $maxConsecWins,
            'max_consec_losses' => $maxConsecLosses,
            'gross_wins'        => round($grossWins, 2),
            'gross_losses'      => round($grossLosses, 2),
        ];

        // ── Strategy breakdown ────────────────────────────────────────────────
        $strategyBreakdown = $sorted
            ->filter(fn($t) => !empty($t->strategy))
            ->groupBy('strategy')
            ->map(function ($trades) {
                $w = $trades->filter(fn($t) => $t->p_l > 0);
                return [
                    'strategy' => $trades->first()->strategy,
                    'trades'   => $trades->count(),
                    'wins'     => $w->count(),
                    'win_rate' => round($w->count() / $trades->count() * 100, 1),
                    'total_pnl'=> round($trades->sum('p_l'), 2),
                    'avg_pnl'  => round($trades->avg('p_l'), 2),
                ];
            })
            ->sortByDesc('total_pnl')
            ->values();

        // ── Psychology breakdown ──────────────────────────────────────────────
        $psychBreakdown = $sorted
            ->filter(fn($t) => !empty($t->emotional_state))
            ->groupBy('emotional_state')
            ->map(function ($trades) {
                $w = $trades->filter(fn($t) => $t->p_l > 0);
                return [
                    'state'    => $trades->first()->emotional_state,
                    'trades'   => $trades->count(),
                    'win_rate' => round($w->count() / $trades->count() * 100, 1),
                    'avg_pnl'  => round($trades->avg('p_l'), 2),
                ];
            })
            ->sortByDesc('avg_pnl')
            ->values();

        // ── Monthly breakdown ─────────────────────────────────────────────────
        $monthlyMetrics = $sorted
            ->groupBy(fn($t) => $t->exit_date->format('Y-m'))
            ->map(fn($trades) => [
                'month'    => $trades->first()->exit_date->format('M Y'),
                'pnl'      => round($trades->sum('p_l'), 2),
                'trades'   => $trades->count(),
                'wins'     => $trades->filter(fn($t) => $t->p_l > 0)->count(),
            ])
            ->values();

        return view('metrics.index', compact(
            'dailyMetrics', 'stats', 'advanced', 'monthlyMetrics',
            'equityCurve', 'drawdownCurve',
            'strategyBreakdown', 'psychBreakdown',
            'dateFrom', 'dateTo'
        ));
    }

    private function emptyStats(): array
    {
        return [
            'total_trades' => 0, 'wins' => 0, 'losses' => 0,
            'win_rate' => 0, 'total_pnl' => 0, 'avg_win' => 0,
            'avg_loss' => 0, 'total_commission' => 0, 'net_pnl' => 0,
        ];
    }

    private function emptyAdvanced(): array
    {
        return [
            'max_drawdown' => 0, 'current_drawdown' => 0,
            'expectancy' => null, 'profit_factor' => null,
            'sharpe' => null, 'max_consec_wins' => 0, 'max_consec_losses' => 0,
            'gross_wins' => 0, 'gross_losses' => 0,
        ];
    }
}
