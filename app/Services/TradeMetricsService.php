<?php

namespace App\Services;

use App\Models\User;
use App\Models\Trade;
use App\Models\DailyMetric;
use App\Models\ConsistencyMetric;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TradeMetricsService
{
    public function calculateDailyMetrics(User $user, Carbon $date): DailyMetric
    {
        $trades = $user->trades()
            ->where('status', 'closed')
            ->whereDate('exit_date', $date)
            ->get();

        $wins = $trades->filter(fn($t) => $t->p_l > 0);
        $losses = $trades->filter(fn($t) => $t->p_l < 0);
        $dailyPnL = $trades->sum('p_l');

        $metric = DailyMetric::updateOrCreate(
            ['user_id' => $user->id, 'date' => $date],
            [
                'trades_count' => $trades->count(),
                'wins' => $wins->count(),
                'losses' => $losses->count(),
                'win_rate' => $trades->count() > 0 ? ($wins->count() / $trades->count()) * 100 : 0,
                'daily_pnl' => $dailyPnL,
                'daily_pnl_percent' => $trades->count() > 0 ? ($dailyPnL / $trades->sum('capital_used')) * 100 : 0,
                'best_trade' => $trades->maxBy('p_l')?->symbol,
                'worst_trade' => $trades->minBy('p_l')?->symbol,
                'avg_risk_reward' => $trades->avg(fn($t) => $t->getRiskRewardRatio()) ?? 0,
            ]
        );

        return $metric;
    }

    public function calculateAllUserMetrics(User $user): void
    {
        $trades = $user->trades()->where('status', 'closed')->get();

        if ($trades->isEmpty()) {
            return;
        }

        $dates = $trades->groupBy('exit_date')->keys();

        foreach ($dates as $date) {
            $this->calculateDailyMetrics($user, $date);
        }

        $this->updateConsistencyMetrics($user);
    }

    public function updateConsistencyMetrics(User $user): void
    {
        $trades = $user->trades()->where('status', 'closed')->orderBy('exit_date')->get();

        if ($trades->isEmpty()) {
            return;
        }

        $dailyResults = $trades->groupBy('exit_date')->map(fn($day) => $day->sum('p_l'));

        // Calculate win/loss streaks
        $this->calculateStreaks($user, $dailyResults);

        // Calculate best/worst days
        $this->updateBestWorstDays($user, $dailyResults);
    }

    private function calculateStreaks(User $user, Collection $dailyResults): void
    {
        $winStreak = 0;
        $lossStreak = 0;
        $maxWinStreak = 0;
        $maxLossStreak = 0;
        $winStreakStart = null;
        $lossStreakStart = null;

        foreach ($dailyResults as $date => $pnl) {
            if ($pnl > 0) {
                if ($winStreakStart === null) {
                    $winStreakStart = $date;
                }
                $winStreak++;
                $lossStreak = 0;

                if ($winStreak > $maxWinStreak) {
                    $maxWinStreak = $winStreak;
                    ConsistencyMetric::updateOrCreate(
                        ['user_id' => $user->id, 'metric_type' => 'win_streak'],
                        ['count' => $winStreak, 'start_date' => $winStreakStart, 'end_date' => $date]
                    );
                }
            } else {
                if ($lossStreakStart === null) {
                    $lossStreakStart = $date;
                }
                $lossStreak++;
                $winStreak = 0;

                if ($lossStreak > $maxLossStreak) {
                    $maxLossStreak = $lossStreak;
                    ConsistencyMetric::updateOrCreate(
                        ['user_id' => $user->id, 'metric_type' => 'loss_streak'],
                        ['count' => $lossStreak, 'start_date' => $lossStreakStart, 'end_date' => $date]
                    );
                }
            }
        }
    }

    private function updateBestWorstDays(User $user, Collection $dailyResults): void
    {
        $bestDay = $dailyResults->max();
        $worstDay = $dailyResults->min();

        $bestDayDate = $dailyResults->keys()->first(fn($date) => $dailyResults[$date] == $bestDay);
        $worstDayDate = $dailyResults->keys()->first(fn($date) => $dailyResults[$date] == $worstDay);

        if ($bestDay > 0) {
            ConsistencyMetric::updateOrCreate(
                ['user_id' => $user->id, 'metric_type' => 'best_day'],
                ['count' => null, 'start_date' => $bestDayDate, 'end_date' => $bestDayDate]
            );
        }

        if ($worstDay < 0) {
            ConsistencyMetric::updateOrCreate(
                ['user_id' => $user->id, 'metric_type' => 'worst_day'],
                ['count' => null, 'start_date' => $worstDayDate, 'end_date' => $worstDayDate]
            );
        }
    }

    public function recalculateTradeMetrics(Trade $trade): void
    {
        if ($trade->exit_date && $trade->p_l !== null) {
            $this->calculateDailyMetrics($trade->user, Carbon::parse($trade->exit_date));
            $this->updateConsistencyMetrics($trade->user);
        }
    }
}
