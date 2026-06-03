<?php

namespace App\Services;

use App\Models\User;
use App\Models\TradingPlan;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TradingInsightsService
{
    private User $user;
    private Collection $trades;

    public function __construct(User $user)
    {
        $this->user   = $user;
        $this->trades = $user->trades()->where('status', 'closed')->get();
    }

    public function getAlerts(TradingPlan $plan = null): array
    {
        $alerts = [];
        $today = now()->setTimezone('America/Mexico_City');

        // Trades de hoy
        $todayTrades = $this->trades->filter(
            fn($t) => $t->exit_date && $t->exit_date->isToday()
        );
        $todayPnl    = $todayTrades->sum('p_l');
        $todayCount  = $todayTrades->count();

        // Trades de esta semana
        $weekTrades = $this->trades->filter(
            fn($t) => $t->exit_date && $t->exit_date->gte($today->copy()->startOfWeek())
        );
        $weekPnl = $weekTrades->sum('p_l');

        // ── Alertas de riesgo ─────────────────────────────────────────────────
        if ($plan) {
            // Límite de pérdida diaria
            if ($todayPnl < 0 && abs($todayPnl) >= $plan->max_daily_loss * 0.8) {
                $pct = round(abs($todayPnl) / $plan->max_daily_loss * 100);
                $alerts[] = [
                    'type'  => abs($todayPnl) >= $plan->max_daily_loss ? 'danger' : 'warning',
                    'icon'  => '🚨',
                    'title' => abs($todayPnl) >= $plan->max_daily_loss
                        ? 'LÍMITE DIARIO ALCANZADO — Para de tradear'
                        : "Cerca del límite diario ({$pct}%)",
                    'msg'   => "Pérdida hoy: $" . number_format(abs($todayPnl), 2) . " / Límite: $" . number_format($plan->max_daily_loss, 2),
                ];
            }

            // Límite de pérdida semanal
            if ($weekPnl < 0 && abs($weekPnl) >= $plan->max_weekly_loss * 0.7) {
                $alerts[] = [
                    'type'  => 'warning',
                    'icon'  => '⚠️',
                    'title' => 'Límite semanal al ' . round(abs($weekPnl) / $plan->max_weekly_loss * 100) . '%',
                    'msg'   => "Pérdida esta semana: $" . number_format(abs($weekPnl), 2),
                ];
            }

            // Máximo de trades por día
            if ($todayCount >= $plan->max_trades_per_day) {
                $alerts[] = [
                    'type'  => 'warning',
                    'icon'  => '🔢',
                    'title' => "Máximo de operaciones alcanzado ({$todayCount}/{$plan->max_trades_per_day})",
                    'msg'   => 'Tu plan indica máximo ' . $plan->max_trades_per_day . ' operaciones por día.',
                ];
            }
        }

        // ── Racha perdedora ───────────────────────────────────────────────────
        $streak = $this->getCurrentStreak();
        if ($streak['type'] === 'loss' && $streak['count'] >= 3) {
            $alerts[] = [
                'type'  => 'danger',
                'icon'  => '📉',
                'title' => "Racha de {$streak['count']} pérdidas consecutivas",
                'msg'   => 'Considera reducir tamaño de posición o tomar un descanso.',
            ];
        }

        // Win rate cayendo esta semana
        if ($weekTrades->count() >= 3) {
            $weekWinRate = $weekTrades->filter(fn($t) => $t->p_l > 0)->count() / $weekTrades->count() * 100;
            $allWinRate  = $this->trades->count() > 0
                ? $this->trades->filter(fn($t) => $t->p_l > 0)->count() / $this->trades->count() * 100
                : 0;
            if ($weekWinRate < $allWinRate - 20) {
                $alerts[] = [
                    'type'  => 'warning',
                    'icon'  => '📊',
                    'title' => 'Win rate esta semana por debajo de tu promedio',
                    'msg'   => 'Esta semana: ' . round($weekWinRate) . '% vs tu promedio: ' . round($allWinRate) . '%',
                ];
            }
        }

        return $alerts;
    }

    public function getInsights(): array
    {
        if ($this->trades->count() < 10) {
            return [['icon' => '📊', 'text' => 'Necesitas al menos 10 operaciones cerradas para generar insights personalizados.']];
        }

        $insights = [];

        // ── Mejor día de la semana ────────────────────────────────────────────
        $byDow = $this->trades
            ->filter(fn($t) => $t->exit_date)
            ->groupBy(fn($t) => $t->exit_date->dayOfWeek) // 0=Sun, 1=Mon...
            ->map(fn($group) => [
                'count'    => $group->count(),
                'wins'     => $group->filter(fn($t) => $t->p_l > 0)->count(),
                'pnl'      => $group->sum('p_l'),
                'win_rate' => $group->filter(fn($t) => $t->p_l > 0)->count() / $group->count() * 100,
            ]);

        $days = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $best = $byDow->sortByDesc('win_rate')->first();
        $bestDow = $byDow->keys()->sortByDesc(fn($k) => $byDow[$k]['win_rate'])->first();
        if ($best && $best['count'] >= 3) {
            $insights[] = [
                'icon'  => '📅',
                'label' => 'Mejor día',
                'text'  => "Los {$days[$bestDow]} tienes " . round($best['win_rate']) . "% de efectividad",
                'value' => round($best['win_rate']) . '%',
                'color' => 'var(--green)',
            ];
        }

        $worst = $byDow->sortBy('win_rate')->first();
        $worstDow = $byDow->keys()->sortBy(fn($k) => $byDow[$k]['win_rate'])->first();
        if ($worst && $worst['count'] >= 3 && $worst['win_rate'] < 40) {
            $insights[] = [
                'icon'  => '⚠️',
                'label' => 'Día a evitar',
                'text'  => "Los {$days[$worstDow]} solo tienes " . round($worst['win_rate']) . "% de efectividad",
                'value' => round($worst['win_rate']) . '%',
                'color' => 'var(--red)',
            ];
        }

        // ── Mejor estrategia ──────────────────────────────────────────────────
        $byStrategy = $this->trades
            ->filter(fn($t) => !empty($t->strategy) && $t->strategy !== 'IBKR Import')
            ->groupBy('strategy')
            ->filter(fn($g) => $g->count() >= 3)
            ->map(fn($group) => [
                'count'    => $group->count(),
                'pnl'      => $group->sum('p_l'),
                'win_rate' => $group->filter(fn($t) => $t->p_l > 0)->count() / $group->count() * 100,
            ]);

        if ($byStrategy->isNotEmpty()) {
            $bestStrat = $byStrategy->sortByDesc('win_rate')->first();
            $bestStratName = $byStrategy->sortByDesc('win_rate')->keys()->first();
            $insights[] = [
                'icon'  => '🏆',
                'label' => 'Mejor estrategia',
                'text'  => "\"{$bestStratName}\" tiene " . round($bestStrat['win_rate']) . "% efectividad",
                'value' => round($bestStrat['win_rate']) . '%',
                'color' => 'var(--green)',
            ];

            $worstStrat = $byStrategy->sortBy('win_rate')->first();
            $worstStratName = $byStrategy->sortBy('win_rate')->keys()->first();
            if ($worstStrat['win_rate'] < 35 && $worstStratName !== $bestStratName) {
                $insights[] = [
                    'icon'  => '🚫',
                    'label' => 'Estrategia débil',
                    'text'  => "\"{$worstStratName}\" solo " . round($worstStrat['win_rate']) . "% efectividad",
                    'value' => round($worstStrat['win_rate']) . '%',
                    'color' => 'var(--red)',
                ];
            }
        }

        // ── Tipo de operación ─────────────────────────────────────────────────
        $byType = $this->trades
            ->groupBy('trade_type')
            ->map(fn($g) => [
                'count'    => $g->count(),
                'pnl'      => $g->sum('p_l'),
                'win_rate' => $g->filter(fn($t) => $t->p_l > 0)->count() / $g->count() * 100,
            ]);

        foreach ($byType as $type => $data) {
            if ($data['count'] >= 5) {
                $typeLabel = ['call' => 'Calls', 'put' => 'Puts', 'stock' => 'Stocks'][$type] ?? ucfirst($type);
                if ($data['win_rate'] > 60) {
                    $insights[] = [
                        'icon'  => '📈',
                        'label' => "Fuerte en {$typeLabel}",
                        'text'  => "Tus {$typeLabel}: " . round($data['win_rate']) . "% de ganancia",
                        'value' => round($data['win_rate']) . '%',
                        'color' => 'var(--green)',
                    ];
                } elseif ($data['win_rate'] < 35) {
                    $insights[] = [
                        'icon'  => '⬇️',
                        'label' => "Débil en {$typeLabel}",
                        'text'  => "Tus {$typeLabel}: " . round($data['win_rate']) . "% de ganancia — considera reducirlos",
                        'value' => round($data['win_rate']) . '%',
                        'color' => 'var(--red)',
                    ];
                }
            }
        }

        // ── Estado emocional ─────────────────────────────────────────────────
        $byEmotion = $this->trades
            ->filter(fn($t) => !empty($t->emotional_state))
            ->groupBy('emotional_state')
            ->filter(fn($g) => $g->count() >= 3)
            ->map(fn($g) => [
                'count'    => $g->count(),
                'pnl'      => round($g->sum('p_l'), 2),
                'win_rate' => round($g->filter(fn($t) => $t->p_l > 0)->count() / $g->count() * 100, 1),
            ]);

        if ($byEmotion->isNotEmpty()) {
            $bestEmotion     = $byEmotion->sortByDesc('win_rate')->keys()->first();
            $bestEmotionData = $byEmotion->sortByDesc('win_rate')->first();
            $worstEmotion    = $byEmotion->sortBy('win_rate')->keys()->first();
            $worstData       = $byEmotion->sortBy('win_rate')->first();

            if ($bestEmotionData['win_rate'] > 55) {
                $insights[] = [
                    'icon'  => '🧘',
                    'label' => 'Estado ideal',
                    'text'  => "Cuando estás \"{$bestEmotion}\": " . $bestEmotionData['win_rate'] . "% efectividad",
                    'value' => $bestEmotionData['win_rate'] . '%',
                    'color' => 'var(--green)',
                ];
            }
            if ($worstData['win_rate'] < 40 && $worstEmotion !== $bestEmotion) {
                $insights[] = [
                    'icon'  => '🚨',
                    'label' => 'Estado peligroso',
                    'text'  => "Cuando estás \"{$worstEmotion}\": " . $worstData['win_rate'] . "% efectividad — no tradees",
                    'value' => $worstData['win_rate'] . '%',
                    'color' => 'var(--red)',
                ];
            }
        }

        // ── Profit factor general ─────────────────────────────────────────────
        $wins   = $this->trades->filter(fn($t) => $t->p_l > 0)->sum('p_l');
        $losses = abs($this->trades->filter(fn($t) => $t->p_l < 0)->sum('p_l'));
        if ($losses > 0) {
            $pf = round($wins / $losses, 2);
            $insights[] = [
                'icon'  => $pf >= 1.5 ? '💪' : ($pf >= 1 ? '⚖️' : '📉'),
                'label' => 'Profit Factor',
                'text'  => "Por cada $1 perdido, ganas $" . $pf . " — " . ($pf >= 1.5 ? 'excelente' : ($pf >= 1 ? 'positivo' : 'necesitas mejorar')),
                'value' => $pf,
                'color' => $pf >= 1.5 ? 'var(--green)' : ($pf >= 1 ? '#f9a825' : 'var(--red)'),
            ];
        }

        return array_slice($insights, 0, 8);
    }

    private function getCurrentStreak(): array
    {
        $sorted = $this->trades->sortByDesc('exit_date');
        $count = 0;
        $type = null;

        foreach ($sorted as $trade) {
            $isWin = $trade->p_l > 0;
            if ($type === null) {
                $type = $isWin ? 'win' : 'loss';
                $count = 1;
            } elseif (($isWin && $type === 'win') || (!$isWin && $type === 'loss')) {
                $count++;
            } else {
                break;
            }
        }

        return ['type' => $type ?? 'none', 'count' => $count];
    }
}
