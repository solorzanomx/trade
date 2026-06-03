<?php

namespace App\Http\Controllers;

use App\Models\AccountTransaction;
use App\Models\Trade;
use App\Services\TradingInsightsService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user  = $request->user();
        $today = Carbon::today();

        $todayMetric = $user->dailyMetrics()->whereDate('date', $today)->first();

        $recentTrades = $user->trades()
            ->with('asset', 'comments')
            ->orderByDesc('entry_date')
            ->limit(5)
            ->get();

        $allClosedTrades = $user->trades()->where('status', 'closed')->get();
        $totalPnL = $allClosedTrades->sum('p_l');
        $winRate  = $allClosedTrades->isNotEmpty()
            ? ($allClosedTrades->filter(fn($t) => $t->p_l > 0)->count() / $allClosedTrades->count()) * 100
            : 0;

        $todayNews = $user->newsSummaries()
            ->with('asset')
            ->whereDate('date', $today)
            ->limit(3)
            ->get();

        $currentStreak = 0;
        $streakType    = null;
        $trades = $user->trades()->where('status', 'closed')->orderBy('exit_date')->get();
        if ($trades->isNotEmpty()) {
            $dailyPnL = $trades->groupBy('exit_date')->map(fn($day) => $day->sum('p_l'));
            foreach ($dailyPnL->reverse() as $pnl) {
                if ($pnl > 0 && ($streakType === null || $streakType === 'win')) {
                    $streakType = 'win'; $currentStreak++;
                } elseif ($pnl < 0 && ($streakType === null || $streakType === 'loss')) {
                    $streakType = 'loss'; $currentStreak++;
                } else { break; }
            }
        }

        // Reporte QQQ
        $todayReport   = null;
        $reportBias    = null;
        $reportAgenda  = [];
        $reportLevels  = [];
        $reportSummary = null;
        $template      = null;

        $template = $user->reportTemplates()->first();
        if ($template) {
            $todayReport = $user->dailyReports()
                ->where('report_template_id', $template->id)
                ->where('status', 'generated')
                ->whereDate('report_date', $today)
                ->first()
                ?? $user->dailyReports()
                    ->where('report_template_id', $template->id)
                    ->where('status', 'generated')
                    ->orderByDesc('report_date')
                    ->first();

            if ($todayReport) {
                $content       = $todayReport->content;
                $reportBias    = $this->parseBias($content);
                $reportAgenda  = $this->parseAgenda($content);
                $reportLevels  = $this->parseLevels($content);
                $reportSummary = $this->parseSummary($content);
            }
        }

        // Capital summary
        $accountDeposits    = AccountTransaction::totalDeposits($user->id);
        $accountWithdrawals = AccountTransaction::totalWithdrawals($user->id);
        $accountNetCapital  = $accountDeposits - $accountWithdrawals;
        $accountNetPnl      = (float) Trade::where('user_id', $user->id)->where('status', 'closed')->sum('net_p_l');
        $accountBalance     = AccountTransaction::where('user_id', $user->id)->where('type', 'adjustment')->orderByDesc('date')->orderByDesc('id')->first();
        $accountCurrentBalance = $accountBalance ? (float) $accountBalance->amount : null;

        // Smart alerts + insights
        $plan     = $user->tradingPlan;
        $service  = new TradingInsightsService($user);
        $alerts   = $service->getAlerts($plan);
        $insights = $service->getInsights();

        // Entrada del diario de hoy
        $todayJournal = $user->journalEntries()
            ->whereDate('entry_date', $today)
            ->first();

        return view('dashboard.index', compact(
            'todayMetric', 'recentTrades', 'totalPnL', 'winRate',
            'todayNews', 'currentStreak', 'streakType',
            'todayReport', 'reportBias', 'reportAgenda', 'reportLevels', 'reportSummary',
            'template', 'alerts', 'insights', 'todayJournal', 'plan',
            'accountDeposits', 'accountWithdrawals', 'accountNetCapital',
            'accountNetPnl', 'accountCurrentBalance'
        ));
    }

    private function parseBias(string $content): ?array
    {
        if (preg_match('/\*\*BIAS:\*\*\s*(.+)/i', $content, $m)) {
            $raw = trim($m[1]);
            if (str_contains(strtoupper($raw), 'ALCISTA') || str_contains(strtoupper($raw), 'BULLISH')) {
                $color = 'green'; $icon = '🟢'; $label = 'ALCISTA';
            } elseif (str_contains(strtoupper($raw), 'BAJISTA') || str_contains(strtoupper($raw), 'BEARISH')) {
                $color = 'red'; $icon = '🔴'; $label = 'BAJISTA';
            } else {
                $color = 'yellow'; $icon = '🟡'; $label = 'NEUTRAL';
            }
            $reason = null;
            if (preg_match('/\*\*Razón[^:]*:\*\*\s*(.+)/i', $content, $rm)) {
                $reason = trim($rm[1]);
            }
            $conviction = null;
            if (preg_match('/\*\*Convicción:\*\*\s*(.+)/i', $content, $cm)) {
                $conviction = trim($cm[1]);
            }
            return compact('label', 'color', 'icon', 'reason', 'conviction');
        }
        return null;
    }

    private function parseAgenda(string $content): array
    {
        $events = [];
        if (!preg_match('/AGENDA[^\n]*\n([\s\S]*?)(?=\n---|\n## )/i', $content, $section)) {
            return [];
        }
        $lines = explode("\n", $section[1]);
        foreach ($lines as $line) {
            if (!preg_match('/^\|(.+)\|$/', trim($line), $m)) continue;
            $cols = array_map('trim', explode('|', $m[1]));
            if (count($cols) < 4) continue;
            if (str_contains($cols[0], 'Hora') || str_contains($cols[0], ':---') || str_contains($cols[0], '---')) continue;
            if (empty($cols[0]) || !preg_match('/\d{1,2}:\d{2}/', $cols[0])) continue;
            $impact = $cols[3] ?? '';
            $impactLevel = 'low';
            if (str_contains($impact, 'Alto') || str_contains($impact, 'Alto') || str_contains($impact, '🔴')) $impactLevel = 'high';
            elseif (str_contains($impact, 'Medio') || str_contains($impact, '🟡')) $impactLevel = 'medium';
            $events[] = [
                'time_ct' => $cols[0],
                'time_et' => $cols[1] ?? '',
                'event'   => strip_tags($cols[2] ?? ''),
                'impact'  => $impactLevel,
                'detail'  => strip_tags($cols[4] ?? ''),
            ];
        }
        usort($events, fn($a, $b) => strcmp($a['time_ct'], $b['time_ct']));
        return array_slice($events, 0, 10);
    }

    private function parseLevels(string $content): array
    {
        $levels = [];
        if (preg_match('/### Resistencias\n([\s\S]*?)(?=###|---)/i', $content, $section)) {
            foreach (explode("\n", $section[1]) as $line) {
                if (preg_match('/^\|(.+)\|$/', trim($line), $m)) {
                    $cols = array_map('trim', explode('|', $m[1]));
                    if (count($cols) >= 2 && preg_match('/\$?([\d.]+)/', $cols[0], $pm) && !str_contains($cols[0], '---')) {
                        $levels[] = ['type' => 'R', 'price' => $pm[1], 'label' => $cols[1] ?? 'Resistencia'];
                    }
                }
            }
        }
        if (preg_match('/### Soportes\n([\s\S]*?)(?=###|---)/i', $content, $section)) {
            foreach (explode("\n", $section[1]) as $line) {
                if (preg_match('/^\|(.+)\|$/', trim($line), $m)) {
                    $cols = array_map('trim', explode('|', $m[1]));
                    if (count($cols) >= 2 && preg_match('/\$?([\d.]+)/', $cols[0], $pm) && !str_contains($cols[0], '---')) {
                        $levels[] = ['type' => 'S', 'price' => $pm[1], 'label' => $cols[1] ?? 'Soporte'];
                    }
                }
            }
        }
        usort($levels, fn($a, $b) => (float)$b['price'] <=> (float)$a['price']);
        return $levels;
    }

    private function parseSummary(string $content): ?string
    {
        if (preg_match('/```\n([\s\S]*?)```/', $content, $m)) {
            return trim($m[1]);
        }
        return null;
    }
}
