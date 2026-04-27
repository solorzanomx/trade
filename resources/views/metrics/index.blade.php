@extends('layouts.app')

@section('title', 'Metrics - Trading Journal')

@section('content')
<div id="app">
    <div class="mb-6">
        <h1 class="text-3xl font-bold">Performance Metrics</h1>
    </div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-white p-6 rounded shadow">
        <div class="text-gray-600">Total Trades</div>
        <div class="text-3xl font-bold">{{ $stats['total_trades'] }}</div>
    </div>

    <div class="bg-white p-6 rounded shadow">
        <div class="text-gray-600">Wins</div>
        <div class="text-3xl font-bold text-green-600">{{ $stats['wins'] }}</div>
    </div>

    <div class="bg-white p-6 rounded shadow">
        <div class="text-gray-600">Losses</div>
        <div class="text-3xl font-bold text-red-600">{{ $stats['losses'] }}</div>
    </div>

    <div class="bg-white p-6 rounded shadow">
        <div class="text-gray-600">Win Rate</div>
        <div class="text-3xl font-bold">{{ number_format($stats['win_rate'], 1) }}%</div>
    </div>

    <div class="bg-white p-6 rounded shadow">
        <div class="text-gray-600">Total P&L</div>
        <div class="text-3xl font-bold {{ $stats['total_pnl'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
            ${{ number_format($stats['total_pnl'], 2) }}
        </div>
    </div>

    <div class="bg-white p-6 rounded shadow">
        <div class="text-gray-600">Avg Win</div>
        <div class="text-3xl font-bold text-green-600">
            ${{ number_format($stats['avg_win'], 2) }}
        </div>
    </div>

    <div class="bg-white p-6 rounded shadow">
        <div class="text-gray-600">Avg Loss</div>
        <div class="text-3xl font-bold text-red-600">
            ${{ number_format($stats['avg_loss'], 2) }}
        </div>
    </div>
</div>

    <!-- Interactive Charts -->
    <div class="mb-8">
        <metrics-chart
            :daily-metrics="{{ json_encode($dailyMetrics) }}"
            :stats="{{ json_encode($stats) }}"
            :monthly-metrics="{{ json_encode($monthlyMetrics) }}"
        ></metrics-chart>
    </div>

@if ($monthlyMetrics->isNotEmpty())
    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-bold mb-4">Monthly Breakdown</h2>
        <table class="w-full">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left">Month</th>
                    <th class="px-4 py-2 text-right">Trades</th>
                    <th class="px-4 py-2 text-right">Wins</th>
                    <th class="px-4 py-2 text-right">P&L</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($monthlyMetrics as $month)
                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $month['month'] }}</td>
                        <td class="px-4 py-2 text-right">{{ $month['trades'] }}</td>
                        <td class="px-4 py-2 text-right">{{ $month['wins'] }}</td>
                        <td class="px-4 py-2 text-right font-bold {{ $month['pnl'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            ${{ number_format($month['pnl'], 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
</div>
@endsection
