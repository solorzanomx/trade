@extends('layouts.app')
@section('title', 'Trades - TradeLog')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
    <h1>Trades</h1>
    <div style="display:flex; gap:8px;">
        <a href="{{ route('import.index') }}" class="btn-ghost" style="text-decoration:none;">Import CSV</a>
        <a href="{{ route('trades.create') }}" class="btn-primary" style="text-decoration:none;">+ New Trade</a>
    </div>
</div>

<!-- Filters -->
<div class="card" style="padding:14px 16px; margin-bottom:16px;">
    <form method="GET" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <input type="text" name="symbol" placeholder="Symbol (SOFI, AAPL...)" value="{{ request('symbol') }}" class="form-input" style="width:180px;">
        <select name="type" class="form-input" style="width:140px;">
            <option value="">All Types</option>
            <option value="call" {{ request('type') === 'call' ? 'selected' : '' }}>Call</option>
            <option value="put" {{ request('type') === 'put' ? 'selected' : '' }}>Put</option>
            <option value="stock" {{ request('type') === 'stock' ? 'selected' : '' }}>Stock</option>
        </select>
        <select name="status" class="form-input" style="width:130px;">
            <option value="">All Status</option>
            <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
            <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input" style="width:150px;">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input" style="width:150px;">
        <button type="submit" class="btn-ghost">Filter</button>
        @if(request()->anyFilled(['symbol','type','status','date_from','date_to']))
            <a href="{{ route('trades.index') }}" style="font-size:12px; color:var(--text-muted); text-decoration:none;">Clear</a>
        @endif
    </form>
</div>

<!-- Table -->
<div class="card" style="overflow:hidden;">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Symbol</th>
                <th>Type</th>
                <th style="text-align:right;">Buy</th>
                <th style="text-align:right;">Sell</th>
                <th style="text-align:right;">Contracts</th>
                <th style="text-align:right;">P&L</th>
                <th style="text-align:right;">%</th>
                <th style="text-align:right;">Commission</th>
                <th style="text-align:right;">Net P&L</th>
                <th>Strategy</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($trades as $i => $trade)
                <tr style="cursor:pointer;" onclick="window.location='{{ route('trades.show', $trade) }}'">
                    <td class="text-muted" style="font-size:11px;">{{ $trades->firstItem() + $i }}</td>
                    <td class="text-muted" style="font-size:12px;">{{ $trade->entry_date->format('M d, Y') }}</td>
                    <td style="font-weight:700; color:#fff;">{{ $trade->symbol }}</td>
                    <td>
                        <span class="badge-{{ $trade->trade_type === 'call' ? 'green' : ($trade->trade_type === 'put' ? 'red' : 'blue') }}" style="font-size:10px; font-weight:700; padding:2px 8px; border-radius:4px; text-transform:uppercase; white-space:nowrap;">
                            {{ $trade->trade_type }}
                        </span>
                    </td>
                    <td style="text-align:right; font-size:13px;">${{ number_format($trade->entry_price, 2) }}</td>
                    <td style="text-align:right; font-size:13px;">{{ $trade->exit_price ? '$'.number_format($trade->exit_price,2) : '--' }}</td>
                    <td style="text-align:right; font-size:13px;">{{ $trade->quantity }}</td>
                    <td style="text-align:right; font-weight:700; {{ ($trade->p_l ?? 0) >= 0 ? 'color:var(--green)' : 'color:var(--red)' }}">
                        {{ $trade->p_l !== null ? ($trade->p_l >= 0 ? '+' : '').'$'.number_format($trade->p_l,2) : '--' }}
                    </td>
                    <td style="text-align:right; font-size:12px; {{ ($trade->p_l_percent ?? 0) >= 0 ? 'color:var(--green)' : 'color:var(--red)' }}">
                        {{ $trade->p_l_percent !== null ? ($trade->p_l_percent >= 0 ? '+' : '').number_format($trade->p_l_percent,1).'%' : '--' }}
                    </td>
                    <td style="text-align:right; font-size:12px; color:var(--red);">
                        {{ $trade->commission ? '-$'.number_format($trade->commission,2) : '--' }}
                    </td>
                    <td style="text-align:right; font-weight:700; {{ ($trade->net_p_l ?? 0) >= 0 ? 'color:var(--green)' : 'color:var(--red)' }}">
                        {{ $trade->net_p_l !== null ? ($trade->net_p_l >= 0 ? '+' : '').'$'.number_format($trade->net_p_l,2) : '--' }}
                    </td>
                    <td style="font-size:11px; color:var(--text-secondary); max-width:140px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        {{ $trade->strategy ?: '--' }}
                    </td>
                    <td>
                        <span class="badge-{{ $trade->status === 'closed' ? 'blue' : 'yellow' }}" style="font-size:10px; padding:2px 8px; border-radius:4px; text-transform:uppercase; font-weight:600;">
                            {{ $trade->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" style="text-align:center; color:var(--text-muted); padding:48px; font-size:13px;">
                        No trades found. <a href="{{ route('import.index') }}" style="color:var(--blue);">Import your CSV</a> or <a href="{{ route('trades.create') }}" style="color:var(--blue);">add a trade</a>.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($trades->hasPages())
        <div style="padding:12px 16px; border-top:1px solid var(--border);">
            {{ $trades->links() }}
        </div>
    @endif
</div>
@endsection
