@extends('layouts.app')

@section('title', 'Trades - Trading Journal')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold">Trades</h1>
    <a href="{{ route('trades.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        New Trade
    </a>
</div>

<div class="bg-white p-4 rounded shadow mb-6">
    <form method="GET" class="flex gap-4">
        <input type="text" name="symbol" placeholder="Symbol" value="{{ request('symbol') }}" class="px-3 py-2 border rounded">
        <select name="status" class="px-3 py-2 border rounded">
            <option value="">All Status</option>
            <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
            <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
        </select>
        <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
            Filter
        </button>
    </form>
</div>

<div class="bg-white rounded shadow overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-100 border-b">
            <tr>
                <th class="px-6 py-3 text-left">Symbol</th>
                <th class="px-6 py-3 text-left">Type</th>
                <th class="px-6 py-3 text-right">Entry</th>
                <th class="px-6 py-3 text-right">Exit</th>
                <th class="px-6 py-3 text-right">P&L</th>
                <th class="px-6 py-3 text-left">Status</th>
                <th class="px-6 py-3 text-left">Date</th>
                <th class="px-6 py-3 text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($trades as $trade)
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-6 py-4 font-semibold">{{ $trade->symbol }}</td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">
                            {{ ucfirst($trade->trade_type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">${{ number_format($trade->entry_price, 2) }}</td>
                    <td class="px-6 py-4 text-right">
                        {{ $trade->exit_price ? '$' . number_format($trade->exit_price, 2) : '--' }}
                    </td>
                    <td class="px-6 py-4 text-right font-bold {{ $trade->p_l >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $trade->p_l !== null ? '$' . number_format($trade->p_l, 2) : '--' }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 {{ $trade->status === 'closed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }} rounded text-xs">
                            {{ ucfirst($trade->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm">{{ $trade->entry_date->format('M d, Y') }}</td>
                    <td class="px-6 py-4">
                        <a href="{{ route('trades.show', $trade) }}" class="text-blue-600 hover:text-blue-800">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">No trades found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $trades->links() }}
</div>
@endsection
