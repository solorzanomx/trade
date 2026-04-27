@extends('layouts.app')
@section('title', 'Métricas - TradeLog')

@section('content')
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
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">Total Comisiones</div>
        <div style="font-size:28px; font-weight:800; color:var(--red);">-${{ number_format($stats['total_commission'] ?? 0, 2) }}</div>
    </div>
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">P&L Neto</div>
        <div style="font-size:28px; font-weight:800; {{ ($stats['net_pnl'] ?? 0) >= 0 ? 'color:var(--green)' : 'color:var(--red)' }};">
            {{ ($stats['net_pnl'] ?? 0) >= 0 ? '+' : '' }}${{ number_format($stats['net_pnl'] ?? 0, 2) }}
        </div>
    </div>
</div>

<!-- Charts -->
@if($dailyMetrics->isNotEmpty())
<div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px;">
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:16px;">% Efectividad por Día</div>
        <canvas id="winRateChart" height="160"></canvas>
    </div>
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:16px;">G/P Diario ($)</div>
        <canvas id="pnlChart" height="160"></canvas>
    </div>
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:16px;">Ganadoras vs Perdedoras</div>
        <div style="display:flex; justify-content:center;">
            <canvas id="distributionChart" width="200" height="200"></canvas>
        </div>
    </div>
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:16px;">G/P Mensual</div>
        <canvas id="monthlyChart" height="160"></canvas>
    </div>
</div>
@endif

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
                        {{ $month['trades'] > 0 ? number_format(($month['wins']/$month['trades'])*100,1) : 0 }}%
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

@if($dailyMetrics->isNotEmpty())
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const GREEN = '#26a69a', RED = '#ef5350', BLUE = '#5b8af5';
const GREEN_BG = 'rgba(38,166,154,0.15)', RED_BG = 'rgba(239,83,80,0.15)', BLUE_BG = 'rgba(91,138,245,0.15)';
const GRID = 'rgba(42,46,57,0.8)', TEXT = '#787b86';

const scales = {
    x: { grid: { color: GRID }, ticks: { color: TEXT, font: { size: 11 } } },
    y: { grid: { color: GRID }, ticks: { color: TEXT, font: { size: 11 } } },
};

const daily = @json($dailyMetrics->map(fn($m) => ['date' => $m->date->format('d/m'), 'win_rate' => $m->win_rate, 'daily_pnl' => $m->daily_pnl]));
const monthly = @json($monthlyMetrics);
const stats = @json($stats);

// Win Rate
new Chart(document.getElementById('winRateChart'), {
    type: 'line',
    data: {
        labels: daily.map(d => d.date),
        datasets: [{ data: daily.map(d => parseFloat(d.win_rate)||0), borderColor: BLUE,
            backgroundColor: BLUE_BG, borderWidth: 2, fill: true, tension: 0.3,
            pointBackgroundColor: BLUE, pointRadius: 5, label: '% Efectividad' }]
    },
    options: { responsive: true, plugins: { legend: { display: false } },
        scales: { ...scales, y: { ...scales.y, min: 0, max: 100 } } }
});

// Daily P&L
const pnlData = daily.map(d => parseFloat(d.daily_pnl)||0);
new Chart(document.getElementById('pnlChart'), {
    type: 'bar',
    data: {
        labels: daily.map(d => d.date),
        datasets: [{ data: pnlData, label: 'G/P $',
            backgroundColor: pnlData.map(v => v >= 0 ? GREEN_BG : RED_BG),
            borderColor: pnlData.map(v => v >= 0 ? GREEN : RED),
            borderWidth: 2, borderRadius: 4 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, scales }
});

// Distribution
new Chart(document.getElementById('distributionChart'), {
    type: 'doughnut',
    data: {
        labels: ['Ganadoras', 'Perdedoras'],
        datasets: [{ data: [stats.wins||0, stats.losses||0],
            backgroundColor: [GREEN_BG, RED_BG],
            borderColor: [GREEN, RED], borderWidth: 2 }]
    },
    options: { responsive: false, cutout: '65%',
        plugins: { legend: { position: 'bottom', labels: { color: TEXT, padding: 16 } } } }
});

// Monthly
if (monthly.length) {
    const mPnl = monthly.map(m => parseFloat(m.pnl)||0);
    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: monthly.map(m => m.month),
            datasets: [{ data: mPnl, label: 'G/P $',
                backgroundColor: mPnl.map(v => v >= 0 ? GREEN_BG : RED_BG),
                borderColor: mPnl.map(v => v >= 0 ? GREEN : RED),
                borderWidth: 2, borderRadius: 4 }]
        },
        options: { responsive: true, plugins: { legend: { display: false } }, scales }
    });
}
</script>
@endif
@endsection
