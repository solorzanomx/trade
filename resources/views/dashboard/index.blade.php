@extends('layouts.app')

@section('title', 'Dashboard - Trading Journal')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white p-6 rounded shadow">
        <div class="text-gray-600">Total P&L</div>
        <div class="text-3xl font-bold {{ $totalPnL >= 0 ? 'text-green-600' : 'text-red-600' }}">
            ${{ number_format($totalPnL, 2) }}
        </div>
    </div>

    <div class="bg-white p-6 rounded shadow">
        <div class="text-gray-600">Win Rate</div>
        <div class="text-3xl font-bold text-blue-600">
            {{ number_format($winRate, 1) }}%
        </div>
    </div>

    <div class="bg-white p-6 rounded shadow">
        <div class="text-gray-600">Today's P&L</div>
        @if ($todayMetric)
            <div class="text-3xl font-bold {{ $todayMetric->daily_pnl >= 0 ? 'text-green-600' : 'text-red-600' }}">
                ${{ number_format($todayMetric->daily_pnl, 2) }}
            </div>
            <div class="text-sm text-gray-500">{{ $todayMetric->trades_count }} trades</div>
        @else
            <div class="text-3xl font-bold text-gray-400">--</div>
        @endif
    </div>

    <div class="bg-white p-6 rounded shadow">
        <div class="text-gray-600">Current Streak</div>
        @if ($currentStreak > 0)
            <div class="text-3xl font-bold {{ $streakType === 'win' ? 'text-green-600' : 'text-red-600' }}">
                {{ $currentStreak }} {{ $streakType === 'win' ? '✓' : '✗' }}
            </div>
        @else
            <div class="text-3xl font-bold text-gray-400">--</div>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold mb-4">Recent Trades</h2>
        <div class="space-y-3">
            @forelse ($recentTrades as $trade)
                <a href="{{ route('trades.show', $trade) }}" class="block p-3 border rounded hover:bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="font-semibold">{{ $trade->symbol }}</div>
                            <div class="text-sm text-gray-600">{{ $trade->entry_date->format('M d, Y') }}</div>
                        </div>
                        @if ($trade->p_l !== null)
                            <div class="font-bold {{ $trade->p_l >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ${{ number_format($trade->p_l, 2) }}
                            </div>
                        @else
                            <div class="text-gray-500">Open</div>
                        @endif
                    </div>
                </a>
            @empty
                <p class="text-gray-500">No trades yet</p>
            @endforelse
        </div>

        <a href="{{ route('trades.create') }}" class="mt-4 inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            New Trade
        </a>
    </div>

    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold mb-4">Today's News</h2>
        <div class="space-y-3">
            @forelse ($todayNews as $news)
                <a href="{{ route('news.index', ['asset_id' => $news->asset_id]) }}" class="block p-3 border rounded hover:bg-gray-50">
                    <div class="font-semibold">{{ $news->asset->symbol }}</div>
                    <p class="text-sm text-gray-600 line-clamp-2">{{ $news->summary }}</p>
                    <div class="text-xs text-gray-500 mt-2">{{ ucfirst($news->sentiment) }} • {{ $news->source }}</div>
                </a>
            @empty
                <p class="text-gray-500">No news summaries available</p>
            @endforelse
        </div>

        <a href="{{ route('news.index') }}" class="mt-4 inline-block text-blue-600 hover:text-blue-700">
            View All News →
        </a>
    </div>
</div>
@endsection
