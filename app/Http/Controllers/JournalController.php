<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\TradingPlan;
use App\Services\TradingInsightsService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    public function index(Request $request)
    {
        $user    = $request->user();
        $entries = $user->journalEntries()
            ->orderByDesc('entry_date')
            ->paginate(30);

        // Streak de entradas de diario
        $journalStreak = $this->getJournalStreak($user);

        return view('journal.index', compact('entries', 'journalStreak'));
    }

    public function day(Request $request, string $date = null)
    {
        $user = $request->user();
        $date = $date ? Carbon::parse($date) : now()->setTimezone('America/Mexico_City');

        $entry = $user->journalEntries()
            ->whereDate('entry_date', $date)
            ->first();

        // Trades de ese día
        $dayTrades = $user->trades()
            ->where(function($q) use ($date) {
                $q->whereDate('entry_date', $date)
                  ->orWhereDate('exit_date', $date);
            })
            ->get();

        $dayPnl     = $dayTrades->filter(fn($t) => $t->exit_date?->isSameDay($date))->sum('p_l');
        $plan       = $user->tradingPlan;
        $prevDay    = $date->copy()->subWeekday();
        $nextDay    = $date->copy()->addWeekday();

        return view('journal.day', compact('entry', 'date', 'dayTrades', 'dayPnl', 'plan', 'prevDay', 'nextDay'));
    }

    public function save(Request $request, string $date)
    {
        $user = $request->user();
        $dt   = Carbon::parse($date);

        $data = $request->validate([
            'pre_bias'              => 'nullable|in:bullish,bearish,neutral',
            'pre_plan'              => 'nullable|string',
            'watchlist_raw'         => 'nullable|string',
            'pre_goals'             => 'nullable|string',
            'mood_start'            => 'nullable|integer|min:1|max:10',
            'market_conditions'     => 'nullable|string',
            'grade'                 => 'nullable|in:A+,A,B,C,D,F',
            'what_went_well'        => 'nullable|string',
            'what_to_improve'       => 'nullable|string',
            'lesson_learned'        => 'nullable|string',
            'goals_tomorrow'        => 'nullable|string',
            'mood_end'              => 'nullable|integer|min:1|max:10',
            'post_review'           => 'nullable|string',
            'followed_plan'         => 'nullable|boolean',
        ]);

        // Parsear watchlist
        if (!empty($data['watchlist_raw'])) {
            $data['watchlist'] = array_map('strtoupper', array_filter(
                array_map('trim', explode(',', $data['watchlist_raw']))
            ));
        }
        unset($data['watchlist_raw']);

        $data['user_id']    = $user->id;
        $data['entry_date'] = $dt->toDateString();

        JournalEntry::updateOrCreate(
            ['user_id' => $user->id, 'entry_date' => $dt->toDateString()],
            $data
        );

        return redirect()->route('journal.day', $dt->toDateString())
            ->with('success', 'Entrada guardada correctamente.');
    }

    private function getJournalStreak(mixed $user): int
    {
        $streak = 0;
        $date   = now()->setTimezone('America/Mexico_City')->startOfDay();

        while (true) {
            if ($date->isWeekend()) { $date->subDay(); continue; }
            $exists = $user->journalEntries()->whereDate('entry_date', $date)->exists();
            if (!$exists) break;
            $streak++;
            $date->subDay();
        }
        return $streak;
    }
}
