@extends('layouts.app')
@section('title', 'Dashboard - TradeLog')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <h1>Dashboard</h1>
        <div class="text-muted" style="font-size:13px; margin-top:2px;">{{ now()->format('l, M d Y') }}</div>
    </div>
    <a href="{{ route('trades.create') }}" class="btn-primary" style="text-decoration:none;">+ New Trade</a>
</div>

<!-- Stats Row -->
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px;">
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">Total P&L</div>
        <div style="font-size:28px; font-weight:800; {{ $totalPnL >= 0 ? 'color:var(--green)' : 'color:var(--red)' }};">
            {{ $totalPnL >= 0 ? '+' : '' }}${{ number_format($totalPnL, 2) }}
        </div>
        <div class="text-muted" style="font-size:11px; margin-top:4px;">All time</div>
    </div>

    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">Win Rate</div>
        <div style="font-size:28px; font-weight:800; color:#5b8af5;">{{ number_format($winRate, 1) }}%</div>
        <div class="text-muted" style="font-size:11px; margin-top:4px;">Closed trades</div>
    </div>

    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">Today's P&L</div>
        @if ($todayMetric)
            <div style="font-size:28px; font-weight:800; {{ $todayMetric->daily_pnl >= 0 ? 'color:var(--green)' : 'color:var(--red)' }};">
                {{ $todayMetric->daily_pnl >= 0 ? '+' : '' }}${{ number_format($todayMetric->daily_pnl, 2) }}
            </div>
            <div class="text-muted" style="font-size:11px; margin-top:4px;">{{ $todayMetric->trades_count }} trades today</div>
        @else
            <div style="font-size:28px; font-weight:800; color:var(--text-muted);">--</div>
            <div class="text-muted" style="font-size:11px; margin-top:4px;">No trades today</div>
        @endif
    </div>

    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">Streak</div>
        @if ($currentStreak > 0)
            <div style="font-size:28px; font-weight:800; {{ $streakType === 'win' ? 'color:var(--green)' : 'color:var(--red)' }};">
                {{ $currentStreak }}{{ $streakType === 'win' ? 'W' : 'L' }}
            </div>
            <div class="text-muted" style="font-size:11px; margin-top:4px;">{{ $streakType === 'win' ? 'Winning' : 'Losing' }} streak</div>
        @else
            <div style="font-size:28px; font-weight:800; color:var(--text-muted);">--</div>
            <div class="text-muted" style="font-size:11px; margin-top:4px;">No streak</div>
        @endif
    </div>
</div>

<!-- Content Grid -->
<div style="display:grid; grid-template-columns:1fr 380px; gap:16px;">
    <!-- Recent Trades -->
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding:16px 20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
            <h2>Recent Trades</h2>
            <a href="{{ route('trades.index') }}" style="font-size:12px; color:var(--blue); text-decoration:none;">View all</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Symbol</th>
                    <th>Type</th>
                    <th>Entry</th>
                    <th>Exit</th>
                    <th style="text-align:right;">P&L</th>
                    <th style="text-align:right;">P&L Net</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentTrades as $trade)
                    <tr style="cursor:pointer;" onclick="window.location='{{ route('trades.show', $trade) }}'">
                        <td>
                            <span style="font-weight:700; color:#fff;">{{ $trade->symbol }}</span>
                        </td>
                        <td>
                            <span class="badge-{{ $trade->trade_type === 'call' ? 'green' : ($trade->trade_type === 'put' ? 'red' : 'blue') }}" style="font-size:10px; font-weight:700; padding:2px 8px; border-radius:4px; text-transform:uppercase;">
                                {{ $trade->trade_type }}
                            </span>
                        </td>
                        <td style="font-size:13px;">${{ number_format($trade->entry_price, 2) }}</td>
                        <td style="font-size:13px;">{{ $trade->exit_price ? '$'.number_format($trade->exit_price,2) : '<span style="color:var(--yellow)">Open</span>' }}</td>
                        <td style="text-align:right; font-weight:700; {{ $trade->p_l >= 0 ? 'color:var(--green)' : 'color:var(--red)' }}">
                            {{ $trade->p_l !== null ? ($trade->p_l >= 0 ? '+' : '').'$'.number_format($trade->p_l,2) : '--' }}
                        </td>
                        <td style="text-align:right; font-size:12px; {{ ($trade->net_p_l ?? 0) >= 0 ? 'color:var(--green)' : 'color:var(--red)' }}">
                            {{ $trade->net_p_l !== null ? ($trade->net_p_l >= 0 ? '+' : '').'$'.number_format($trade->net_p_l,2) : '--' }}
                        </td>
                        <td class="text-muted" style="font-size:12px;">{{ $trade->entry_date->format('M d') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align:center; color:var(--text-muted); padding:32px;">No trades yet. <a href="{{ route('import.index') }}" style="color:var(--blue);">Import your CSV</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Right Column -->
    <div style="display:flex; flex-direction:column; gap:12px;">
        <!-- Quick Actions -->
        <div class="card" style="padding:16px 20px;">
            <h2 style="margin-bottom:12px;">Quick Actions</h2>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <a href="{{ route('trades.create') }}" class="btn-primary" style="text-decoration:none; text-align:center;">+ Log Trade</a>
                <a href="{{ route('import.index') }}" class="btn-ghost" style="text-decoration:none; text-align:center;">Import CSV</a>
            </div>
        </div>

        <!-- Today's News -->
        <div class="card" style="padding:0; overflow:hidden; flex:1;">
            <div style="padding:16px 20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                <h2>Market News</h2>
                <a href="{{ route('news.index') }}" style="font-size:12px; color:var(--blue); text-decoration:none;">View all</a>
            </div>
            <div style="padding:12px;">
                @forelse ($todayNews as $news)
                    <a href="{{ route('news.index', ['asset_id' => $news->asset_id]) }}" style="text-decoration:none; display:block; padding:10px; border-radius:6px; margin-bottom:4px;" class="card-hover">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                            <span style="font-weight:700; color:#fff; font-size:12px;">{{ $news->asset->symbol }}</span>
                            <span class="badge-{{ $news->sentiment === 'positive' ? 'green' : ($news->sentiment === 'negative' ? 'red' : 'blue') }}" style="font-size:10px; padding:1px 6px; border-radius:3px;">
                                {{ ucfirst($news->sentiment) }}
                            </span>
                        </div>
                        <p style="font-size:12px; color:var(--text-secondary); margin:0; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">{{ $news->summary }}</p>
                    </a>
                @empty
                    <div style="padding:20px; text-align:center; color:var(--text-muted); font-size:12px;">No news available</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
