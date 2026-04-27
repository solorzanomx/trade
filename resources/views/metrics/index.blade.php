@extends('layouts.app')
@section('title', 'Métricas - TradeLog')

@section('content')
<div id="app">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
        <div>
            <h1>Métricas de Rendimiento</h1>
            <div class="text-muted" style="font-size:13px; margin-top:2px;">Análisis de tus operaciones</div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px;">
        <div class="card" style="padding:20px;">
            <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">Total Operaciones</div>
            <div style="font-size:28px; font-weight:800; color:#fff;">{{ $stats['total_trades'] }}</div>
        </div>
        <div class="card" style="padding:20px;">
            <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">Ganadoras / Perdedoras</div>
            <div style="font-size:28px; font-weight:800;">
                <span style="color:var(--green);">{{ $stats['wins'] }}</span>
                <span style="color:var(--text-muted); font-size:18px;"> / </span>
                <span style="color:var(--red);">{{ $stats['losses'] }}</span>
            </div>
        </div>
        <div class="card" style="padding:20px;">
            <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">% Efectividad</div>
            <div style="font-size:28px; font-weight:800; color:#5b8af5;">{{ number_format($stats['win_rate'], 1) }}%</div>
        </div>
        <div class="card" style="padding:20px;">
            <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">P&L Total</div>
            <div style="font-size:28px; font-weight:800; {{ $stats['total_pnl'] >= 0 ? 'color:var(--green)' : 'color:var(--red)' }};">
                {{ $stats['total_pnl'] >= 0 ? '+' : '' }}${{ number_format($stats['total_pnl'], 2) }}
            </div>
        </div>
        <div class="card" style="padding:20px;">
            <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">Ganancia Promedio</div>
            <div style="font-size:28px; font-weight:800; color:var(--green);">+${{ number_format($stats['avg_win'], 2) }}</div>
        </div>
        <div class="card" style="padding:20px;">
            <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">Pérdida Promedio</div>
            <div style="font-size:28px; font-weight:800; color:var(--red);">${{ number_format($stats['avg_loss'], 2) }}</div>
        </div>
        @if(isset($stats['total_commission']))
        <div class="card" style="padding:20px;">
            <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">Total Comisiones</div>
            <div style="font-size:28px; font-weight:800; color:var(--red);">-${{ number_format($stats['total_commission'], 2) }}</div>
        </div>
        <div class="card" style="padding:20px;">
            <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">P&L Neto</div>
            <div style="font-size:28px; font-weight:800; {{ $stats['net_pnl'] >= 0 ? 'color:var(--green)' : 'color:var(--red)' }};">
                {{ $stats['net_pnl'] >= 0 ? '+' : '' }}${{ number_format($stats['net_pnl'], 2) }}
            </div>
        </div>
        @endif
    </div>

    <!-- Charts -->
    <div style="margin-bottom:20px;">
        <metrics-chart
            :daily-metrics="{{ json_encode($dailyMetrics) }}"
            :stats="{{ json_encode($stats) }}"
            :monthly-metrics="{{ json_encode($monthlyMetrics) }}"
        ></metrics-chart>
    </div>

    <!-- Monthly Breakdown -->
    @if ($monthlyMetrics->isNotEmpty())
    <div class="card" style="overflow:hidden;">
        <div style="padding:16px 20px; border-bottom:1px solid var(--border);">
            <h2>Desglose Mensual</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Mes</th>
                    <th style="text-align:right;">Operaciones</th>
                    <th style="text-align:right;">Ganadoras</th>
                    <th style="text-align:right;">Efectividad</th>
                    <th style="text-align:right;">G/P Bruto</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($monthlyMetrics as $month)
                    <tr>
                        <td style="font-weight:600; color:#fff;">{{ $month['month'] }}</td>
                        <td style="text-align:right;">{{ $month['trades'] }}</td>
                        <td style="text-align:right; color:var(--green);">{{ $month['wins'] }}</td>
                        <td style="text-align:right; color:#5b8af5;">
                            {{ $month['trades'] > 0 ? number_format(($month['wins']/$month['trades'])*100, 1) : 0 }}%
                        </td>
                        <td style="text-align:right; font-weight:700; {{ $month['pnl'] >= 0 ? 'color:var(--green)' : 'color:var(--red)' }}">
                            {{ $month['pnl'] >= 0 ? '+' : '' }}${{ number_format($month['pnl'], 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
