@extends('layouts.app')
@section('title', 'Métricas - TradeLog')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
    <div>
        <h1>Métricas de Rendimiento</h1>
        <div class="text-muted" style="font-size:13px; margin-top:2px;">Análisis profesional de tus operaciones</div>
    </div>
    {{-- Date filter --}}
    <form method="GET" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
        <input type="date" name="date_from" value="{{ $dateFrom }}"
               style="background:var(--card); border:1px solid var(--border); color:#fff; padding:6px 10px; border-radius:6px; font-size:13px;">
        <span class="text-muted" style="font-size:13px;">→</span>
        <input type="date" name="date_to" value="{{ $dateTo }}"
               style="background:var(--card); border:1px solid var(--border); color:#fff; padding:6px 10px; border-radius:6px; font-size:13px;">
        <button type="submit" style="background:var(--blue); color:#fff; border:none; padding:6px 16px; border-radius:6px; font-size:13px; cursor:pointer;">Filtrar</button>
    </form>
</div>

@if($stats['total_trades'] === 0)
    <div class="card" style="padding:48px; text-align:center; color:var(--text-muted);">
        <div style="font-size:40px; margin-bottom:12px;">📊</div>
        <div style="font-size:16px;">No hay operaciones cerradas todavía.</div>
    </div>
@else

{{-- ═══════════════════════════════════════════════════════════
     ROW 1: Stats base
═══════════════════════════════════════════════════════════ --}}
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:12px;">
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">Total Operaciones</div>
        <div style="font-size:28px; font-weight:800; color:#fff;">{{ $stats['total_trades'] }}</div>
        <div style="font-size:12px; margin-top:4px;">
            <span style="color:var(--green);">{{ $stats['wins'] }}G</span>
            <span class="text-muted"> / </span>
            <span style="color:var(--red);">{{ $stats['losses'] }}P</span>
        </div>
    </div>
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">% Efectividad</div>
        <div style="font-size:28px; font-weight:800; color:#5b8af5;">{{ number_format($stats['win_rate'], 1) }}%</div>
        <div class="text-muted" style="font-size:12px; margin-top:4px;">Ganancia prom: <span style="color:var(--green);">+${{ number_format($stats['avg_win'], 2) }}</span></div>
    </div>
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">P&L Neto</div>
        <div style="font-size:28px; font-weight:800; {{ $stats['net_pnl'] >= 0 ? 'color:var(--green)' : 'color:var(--red)' }};">
            {{ $stats['net_pnl'] >= 0 ? '+' : '' }}${{ number_format($stats['net_pnl'], 2) }}
        </div>
        <div class="text-muted" style="font-size:12px; margin-top:4px;">Comisiones: <span style="color:var(--red);">-${{ number_format($stats['total_commission'], 2) }}</span></div>
    </div>
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">Pérdida Promedio</div>
        <div style="font-size:28px; font-weight:800; color:var(--red);">${{ number_format($stats['avg_loss'], 2) }}</div>
        <div class="text-muted" style="font-size:12px; margin-top:4px;">Rachas máx: <span style="color:var(--green);">{{ $advanced['max_consec_wins'] }}G</span> / <span style="color:var(--red);">{{ $advanced['max_consec_losses'] }}P</span></div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     ROW 2: Métricas avanzadas
═══════════════════════════════════════════════════════════ --}}
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px;">

    {{-- Profit Factor --}}
    <div class="card" style="padding:20px; position:relative; overflow:hidden;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">Profit Factor</div>
        @if($advanced['profit_factor'] !== null)
            <div style="font-size:28px; font-weight:800; color:{{ $advanced['profit_factor'] >= 1.5 ? '#26a69a' : ($advanced['profit_factor'] >= 1 ? '#f59e0b' : '#ef5350') }};">
                {{ number_format($advanced['profit_factor'], 2) }}
            </div>
            <div class="text-muted" style="font-size:11px; margin-top:4px;">
                @if($advanced['profit_factor'] >= 2) Excelente
                @elseif($advanced['profit_factor'] >= 1.5) Bueno
                @elseif($advanced['profit_factor'] >= 1) Aceptable
                @else Negativo
                @endif
                &nbsp;· >1.5 es profesional
            </div>
        @else
            <div style="font-size:20px; color:var(--text-muted);">N/A</div>
        @endif
    </div>

    {{-- Max Drawdown --}}
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">Max Drawdown</div>
        <div style="font-size:28px; font-weight:800; color:{{ $advanced['max_drawdown'] > -10 ? '#f59e0b' : '#ef5350' }};">
            {{ number_format($advanced['max_drawdown'], 1) }}%
        </div>
        <div class="text-muted" style="font-size:11px; margin-top:4px;">
            Actual: {{ number_format($advanced['current_drawdown'], 1) }}%
        </div>
    </div>

    {{-- Expectancy --}}
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">Expectancy (R)</div>
        @if($advanced['expectancy'] !== null)
            <div style="font-size:28px; font-weight:800; color:{{ $advanced['expectancy'] > 0 ? '#26a69a' : '#ef5350' }};">
                {{ $advanced['expectancy'] > 0 ? '+' : '' }}{{ number_format($advanced['expectancy'], 2) }}R
            </div>
            <div class="text-muted" style="font-size:11px; margin-top:4px;">Ganancia por R arriesgado</div>
        @else
            <div style="font-size:20px; color:var(--text-muted);">N/A</div>
            <div class="text-muted" style="font-size:11px; margin-top:4px;">Registra stop loss para calcular</div>
        @endif
    </div>

    {{-- Sharpe Ratio --}}
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">Sharpe Ratio</div>
        @if($advanced['sharpe'] !== null)
            <div style="font-size:28px; font-weight:800; color:{{ $advanced['sharpe'] >= 1 ? '#26a69a' : ($advanced['sharpe'] >= 0 ? '#f59e0b' : '#ef5350') }};">
                {{ number_format($advanced['sharpe'], 2) }}
            </div>
            <div class="text-muted" style="font-size:11px; margin-top:4px;">
                @if($advanced['sharpe'] >= 2) Excelente
                @elseif($advanced['sharpe'] >= 1) Bueno
                @elseif($advanced['sharpe'] >= 0) Aceptable
                @else Negativo
                @endif
                &nbsp;· >1 es bueno
            </div>
        @else
            <div style="font-size:20px; color:var(--text-muted);">N/A</div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════
     EQUITY CURVE (full width)
═══════════════════════════════════════════════════════════ --}}
@if(count($equityCurve) > 0)
<div class="card" style="padding:20px; margin-bottom:12px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <div>
            <div style="font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--text-muted);">Curva de Capital Acumulada</div>
            <div class="text-muted" style="font-size:11px; margin-top:2px;">P&L neto acumulado por operación</div>
        </div>
        <div style="font-size:22px; font-weight:800; {{ $stats['net_pnl'] >= 0 ? 'color:var(--green)' : 'color:var(--red)' }};">
            {{ $stats['net_pnl'] >= 0 ? '+' : '' }}${{ number_format($stats['net_pnl'], 2) }}
        </div>
    </div>
    <canvas id="equityChart" height="90"></canvas>
</div>

{{-- Drawdown --}}
<div class="card" style="padding:20px; margin-bottom:20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <div>
            <div style="font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--text-muted);">Drawdown (%)</div>
            <div class="text-muted" style="font-size:11px; margin-top:2px;">Caída desde el pico de capital</div>
        </div>
        <div style="font-size:18px; font-weight:700; color:{{ $advanced['max_drawdown'] > -10 ? '#f59e0b' : '#ef5350' }};">
            Máx: {{ number_format($advanced['max_drawdown'], 1) }}%
        </div>
    </div>
    <canvas id="drawdownChart" height="60"></canvas>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════
     CHARTS ROW: Daily P&L + Win Rate + Distribution + Monthly
═══════════════════════════════════════════════════════════ --}}
@if($dailyMetrics->isNotEmpty())
<div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px;">
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:16px;">G/P Diario ($)</div>
        <canvas id="pnlChart" height="160"></canvas>
    </div>
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:16px;">% Efectividad por Día</div>
        <canvas id="winRateChart" height="160"></canvas>
    </div>
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:16px;">Distribución Ganadoras/Perdedoras</div>
        <div style="display:flex; justify-content:center; align-items:center; gap:32px;">
            <canvas id="distributionChart" width="180" height="180"></canvas>
            <div style="font-size:13px;">
                <div style="margin-bottom:8px;"><span style="color:var(--green); font-weight:700;">{{ $stats['wins'] }}</span> Ganadoras &nbsp;<span class="text-muted">·</span>&nbsp; Prom: <span style="color:var(--green);">+${{ number_format($stats['avg_win'],2) }}</span></div>
                <div><span style="color:var(--red); font-weight:700;">{{ $stats['losses'] }}</span> Perdedoras &nbsp;<span class="text-muted">·</span>&nbsp; Prom: <span style="color:var(--red);">${{ number_format($stats['avg_loss'],2) }}</span></div>
                <div style="margin-top:12px; padding-top:12px; border-top:1px solid var(--border); color:var(--text-muted); font-size:11px;">
                    Bruto ganado: <span style="color:var(--green);">+${{ number_format($advanced['gross_wins'],2) }}</span><br>
                    Bruto perdido: <span style="color:var(--red);">-${{ number_format($advanced['gross_losses'],2) }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:16px;">G/P Mensual</div>
        <canvas id="monthlyChart" height="160"></canvas>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════
     STRATEGY BREAKDOWN
═══════════════════════════════════════════════════════════ --}}
@if($strategyBreakdown->isNotEmpty())
<div class="card" style="overflow:hidden; margin-bottom:12px;">
    <div style="padding:16px 20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
        <h2>Rendimiento por Estrategia</h2>
        <span class="text-muted" style="font-size:12px;">{{ $strategyBreakdown->count() }} estrategias</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>Estrategia</th>
                <th style="text-align:right;">Ops</th>
                <th style="text-align:right;">Ganadoras</th>
                <th style="text-align:right;">Efectividad</th>
                <th style="text-align:right;">Prom/Op</th>
                <th style="text-align:right;">P&L Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($strategyBreakdown as $s)
            <tr>
                <td>
                    <span style="font-weight:600; color:#fff;">{{ $s['strategy'] }}</span>
                </td>
                <td style="text-align:right;">{{ $s['trades'] }}</td>
                <td style="text-align:right; color:var(--green);">{{ $s['wins'] }}</td>
                <td style="text-align:right;">
                    <span style="color:{{ $s['win_rate'] >= 50 ? '#26a69a' : '#ef5350' }}; font-weight:600;">
                        {{ $s['win_rate'] }}%
                    </span>
                </td>
                <td style="text-align:right; {{ $s['avg_pnl'] >= 0 ? 'color:var(--green)' : 'color:var(--red)' }}; font-weight:600;">
                    {{ $s['avg_pnl'] >= 0 ? '+' : '' }}${{ number_format($s['avg_pnl'], 2) }}
                </td>
                <td style="text-align:right; font-weight:700; {{ $s['total_pnl'] >= 0 ? 'color:var(--green)' : 'color:var(--red)' }}">
                    {{ $s['total_pnl'] >= 0 ? '+' : '' }}${{ number_format($s['total_pnl'], 2) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════
     PSYCHOLOGY BREAKDOWN
═══════════════════════════════════════════════════════════ --}}
@if($psychBreakdown->isNotEmpty())
<div class="card" style="overflow:hidden; margin-bottom:12px;">
    <div style="padding:16px 20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
        <h2>Estado Emocional vs Rendimiento</h2>
        <span class="text-muted" style="font-size:12px;">Correlación psicología → P&L</span>
    </div>
    <table>
        <thead>
            <tr>
                <th>Estado Emocional</th>
                <th style="text-align:right;">Ops</th>
                <th style="text-align:right;">Efectividad</th>
                <th style="text-align:right;">P&L Promedio</th>
                <th>Barra</th>
            </tr>
        </thead>
        <tbody>
            @php
                $maxAbsPnl = $psychBreakdown->max(fn($p) => abs($p['avg_pnl'])) ?: 1;
            @endphp
            @foreach($psychBreakdown as $p)
            <tr>
                <td>
                    <span style="font-weight:600; color:#fff; text-transform:capitalize;">{{ $p['state'] }}</span>
                </td>
                <td style="text-align:right;">{{ $p['trades'] }}</td>
                <td style="text-align:right; color:{{ $p['win_rate'] >= 50 ? '#26a69a' : '#ef5350' }}; font-weight:600;">
                    {{ $p['win_rate'] }}%
                </td>
                <td style="text-align:right; font-weight:700; {{ $p['avg_pnl'] >= 0 ? 'color:var(--green)' : 'color:var(--red)' }}">
                    {{ $p['avg_pnl'] >= 0 ? '+' : '' }}${{ number_format($p['avg_pnl'], 2) }}
                </td>
                <td style="width:160px; padding:0 16px;">
                    <div style="background:var(--border); border-radius:4px; height:8px; overflow:hidden;">
                        <div style="
                            height:8px; border-radius:4px;
                            width:{{ min(100, abs($p['avg_pnl']) / $maxAbsPnl * 100) }}%;
                            background:{{ $p['avg_pnl'] >= 0 ? 'var(--green)' : 'var(--red)' }};
                        "></div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div style="padding:12px 20px; border-top:1px solid var(--border); font-size:12px; color:var(--text-muted);">
        💡 Registra tu estado emocional en cada operación para mejorar esta sección.
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════
     MONTHLY BREAKDOWN
═══════════════════════════════════════════════════════════ --}}
@if($monthlyMetrics->isNotEmpty())
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
            @foreach($monthlyMetrics as $month)
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

@endif {{-- end if has trades --}}

{{-- ═══════════════════════════════════════════════════════════
     SCRIPTS
═══════════════════════════════════════════════════════════ --}}
@if($stats['total_trades'] > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const GREEN = '#26a69a', RED = '#ef5350', BLUE = '#5b8af5', AMBER = '#f59e0b';
const GREEN_BG = 'rgba(38,166,154,0.15)', RED_BG = 'rgba(239,83,80,0.12)';
const BLUE_BG = 'rgba(91,138,245,0.15)', AMBER_BG = 'rgba(245,158,11,0.15)';
const GRID = 'rgba(42,46,57,0.8)', TEXT = '#787b86';

const baseScales = {
    x: { grid: { color: GRID }, ticks: { color: TEXT, font: { size: 10 }, maxTicksLimit: 12 } },
    y: { grid: { color: GRID }, ticks: { color: TEXT, font: { size: 10 } } },
};

const baseOptions = (extra = {}) => ({
    responsive: true,
    animation: { duration: 600 },
    plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
    scales: baseScales,
    ...extra,
});

// ── Equity Curve ──────────────────────────────────────────────────────────────
@if(count($equityCurve) > 0)
const equity = @json($equityCurve);
const equityValues = equity.map(e => e.value);
const equityFinal = equityValues[equityValues.length - 1] ?? 0;
const equityColor = equityFinal >= 0 ? GREEN : RED;
const equityBg    = equityFinal >= 0 ? 'rgba(38,166,154,0.08)' : 'rgba(239,83,80,0.08)';

new Chart(document.getElementById('equityChart'), {
    type: 'line',
    data: {
        labels: equity.map(e => e.date),
        datasets: [{
            data: equityValues,
            borderColor: equityColor,
            backgroundColor: equityBg,
            borderWidth: 2,
            fill: true,
            tension: 0.2,
            pointRadius: equity.length > 60 ? 0 : 3,
            pointHoverRadius: 5,
            pointBackgroundColor: equityColor,
        }]
    },
    options: {
        ...baseOptions(),
        scales: {
            ...baseScales,
            y: {
                ...baseScales.y,
                ticks: {
                    ...baseScales.y.ticks,
                    callback: v => '$' + v.toLocaleString(),
                }
            }
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ' $' + ctx.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 2 }),
                }
            }
        }
    }
});

// ── Drawdown ──────────────────────────────────────────────────────────────────
const dd = @json($drawdownCurve);
new Chart(document.getElementById('drawdownChart'), {
    type: 'line',
    data: {
        labels: dd.map(d => d.date),
        datasets: [{
            data: dd.map(d => d.value),
            borderColor: RED,
            backgroundColor: 'rgba(239,83,80,0.08)',
            borderWidth: 1.5,
            fill: true,
            tension: 0.2,
            pointRadius: 0,
            pointHoverRadius: 4,
        }]
    },
    options: {
        ...baseOptions(),
        scales: {
            ...baseScales,
            y: {
                ...baseScales.y,
                max: 0,
                ticks: {
                    ...baseScales.y.ticks,
                    callback: v => v + '%',
                }
            }
        },
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ' ' + ctx.parsed.y.toFixed(2) + '%',
                }
            }
        }
    }
});
@endif

// ── Daily P&L ─────────────────────────────────────────────────────────────────
@if($dailyMetrics->isNotEmpty())
const daily = @json($dailyMetrics->map(fn($m) => ['date' => $m->date->format('d/m'), 'win_rate' => $m->win_rate, 'daily_pnl' => $m->daily_pnl]));
const monthly = @json($monthlyMetrics);
const stats = @json($stats);

const pnlData = daily.map(d => parseFloat(d.daily_pnl) || 0);
new Chart(document.getElementById('pnlChart'), {
    type: 'bar',
    data: {
        labels: daily.map(d => d.date),
        datasets: [{
            data: pnlData,
            backgroundColor: pnlData.map(v => v >= 0 ? GREEN_BG : RED_BG),
            borderColor:      pnlData.map(v => v >= 0 ? GREEN : RED),
            borderWidth: 2, borderRadius: 4,
        }]
    },
    options: {
        ...baseOptions(),
        scales: {
            ...baseScales,
            y: { ...baseScales.y, ticks: { ...baseScales.y.ticks, callback: v => '$' + v } }
        }
    }
});

new Chart(document.getElementById('winRateChart'), {
    type: 'line',
    data: {
        labels: daily.map(d => d.date),
        datasets: [{
            data: daily.map(d => parseFloat(d.win_rate) || 0),
            borderColor: BLUE, backgroundColor: BLUE_BG,
            borderWidth: 2, fill: true, tension: 0.3,
            pointBackgroundColor: BLUE,
            pointRadius: daily.length > 60 ? 0 : 4,
        }]
    },
    options: baseOptions({ scales: { ...baseScales, y: { ...baseScales.y, min: 0, max: 100 } } })
});

new Chart(document.getElementById('distributionChart'), {
    type: 'doughnut',
    data: {
        labels: ['Ganadoras', 'Perdedoras'],
        datasets: [{
            data: [stats.wins || 0, stats.losses || 0],
            backgroundColor: [GREEN_BG, RED_BG],
            borderColor: [GREEN, RED],
            borderWidth: 2,
        }]
    },
    options: {
        responsive: false, cutout: '68%',
        plugins: { legend: { display: false } }
    }
});

if (monthly.length) {
    const mPnl = monthly.map(m => parseFloat(m.pnl) || 0);
    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: monthly.map(m => m.month),
            datasets: [{
                data: mPnl,
                backgroundColor: mPnl.map(v => v >= 0 ? GREEN_BG : RED_BG),
                borderColor:      mPnl.map(v => v >= 0 ? GREEN : RED),
                borderWidth: 2, borderRadius: 4,
            }]
        },
        options: {
            ...baseOptions(),
            scales: {
                ...baseScales,
                y: { ...baseScales.y, ticks: { ...baseScales.y.ticks, callback: v => '$' + v } }
            }
        }
    });
}
@endif
</script>
@endif
@endsection
