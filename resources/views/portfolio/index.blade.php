@extends('layouts.app')
@section('title', 'Portafolio - TradeLog')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
    <div>
        <h1>Portafolio Largo Plazo</h1>
        <div class="text-muted" style="font-size:13px; margin-top:2px;">Posiciones de inversión · actualiza el precio manualmente</div>
    </div>
    <a href="{{ route('portfolio.create') }}" class="btn-primary" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5v14M5 12h14"/></svg>
        Nueva Posición
    </a>
</div>

{{-- ═══════ SUMMARY CARDS ═══════ --}}
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:12px;">
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">Valor del Portafolio</div>
        <div style="font-size:26px; font-weight:800; color:#fff;">${{ number_format($totalValue, 2) }}</div>
        <div class="text-muted" style="font-size:12px; margin-top:4px;">Costo base: ${{ number_format($totalCost, 2) }}</div>
    </div>
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">G/P No Realizado</div>
        <div style="font-size:26px; font-weight:800; {{ $totalPnl >= 0 ? 'color:var(--green)' : 'color:var(--red)' }};">
            {{ $totalPnl >= 0 ? '+' : '' }}${{ number_format($totalPnl, 2) }}
        </div>
        <div style="font-size:12px; margin-top:4px; {{ $returnPct >= 0 ? 'color:var(--green)' : 'color:var(--red)' }};">
            {{ $returnPct >= 0 ? '+' : '' }}{{ number_format($returnPct, 2) }}% retorno total
        </div>
    </div>
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">Dividendos Recibidos</div>
        <div style="font-size:26px; font-weight:800; color:var(--green);">+${{ number_format($totalDivs, 2) }}</div>
        <div class="text-muted" style="font-size:12px; margin-top:4px;">G/P Realizado: <span style="{{ $realizedPnl >= 0 ? 'color:var(--green)' : 'color:var(--red)' }};">{{ $realizedPnl >= 0 ? '+' : '' }}${{ number_format($realizedPnl, 2) }}</span></div>
    </div>
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">Posiciones</div>
        <div style="font-size:26px; font-weight:800; color:#fff;">{{ $open->count() }}</div>
        <div class="text-muted" style="font-size:12px; margin-top:4px;">
            @if($best)
                Mejor: <span style="color:var(--green);">{{ $best->symbol }} {{ $best->getReturnPercent() >= 0 ? '+' : '' }}{{ number_format($best->getReturnPercent(), 1) }}%</span>
            @endif
        </div>
    </div>
</div>

{{-- ═══════ CHARTS ROW ═══════ --}}
@if($open->count() > 0)
<div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px;">

    {{-- Allocación por posición --}}
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:16px;">Allocación por Posición</div>
        <div style="display:flex; align-items:center; gap:24px;">
            <canvas id="allocationChart" width="180" height="180" style="flex-shrink:0;"></canvas>
            <div style="flex:1; font-size:12px;">
                @foreach($allocation->take(8) as $a)
                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                    <span style="color:#fff; font-weight:600;">{{ $a['symbol'] }}</span>
                    <span class="text-muted">{{ $a['pct'] }}% · ${{ number_format($a['value'],0) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Allocación por sector --}}
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:16px;">Allocación por Sector</div>
        <div style="display:flex; align-items:center; gap:24px;">
            <canvas id="sectorChart" width="180" height="180" style="flex-shrink:0;"></canvas>
            <div style="flex:1; font-size:12px;">
                @foreach($bySector as $s)
                <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                    <span style="color:#fff; font-weight:600;">{{ $s['sector'] }}</span>
                    <span class="text-muted">{{ $s['pct'] }}% · ${{ number_format($s['value'],0) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

{{-- ═══════ POSICIONES ABIERTAS ═══════ --}}
@if($open->count() > 0)
<div class="card" style="overflow:hidden; margin-bottom:16px;">
    <div style="padding:16px 20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
        <h2>Posiciones Abiertas <span style="color:var(--green); font-size:13px; margin-left:8px;">{{ $open->count() }}</span></h2>
        <span class="text-muted" style="font-size:12px;">Precio actualizado manualmente</span>
    </div>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Símbolo</th>
                    <th>Tipo</th>
                    <th style="text-align:right;">Acciones</th>
                    <th style="text-align:right;">Costo Prom</th>
                    <th style="text-align:right;">Precio Actual</th>
                    <th style="text-align:right;">Valor</th>
                    <th style="text-align:right;">G/P $</th>
                    <th style="text-align:right;">G/P %</th>
                    <th style="text-align:right;">Target</th>
                    <th style="text-align:right;">Progreso</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($open as $pos)
                @php
                    $pnl    = $pos->getUnrealizedPnl();
                    $retPct = $pos->getReturnPercent();
                    $prog   = $pos->getTargetProgress();
                    $color  = $pnl >= 0 ? 'var(--green)' : 'var(--red)';
                @endphp
                <tr>
                    <td>
                        <div style="font-weight:700; color:#fff; font-size:14px;">{{ $pos->symbol }}</div>
                        @if($pos->name)
                        <div class="text-muted" style="font-size:11px;">{{ Str::limit($pos->name, 22) }}</div>
                        @endif
                    </td>
                    <td>
                        <span style="font-size:11px; padding:2px 8px; border-radius:4px; background:var(--bg-hover); color:var(--text-secondary); text-transform:uppercase; font-weight:600;">
                            {{ $pos->asset_type }}
                        </span>
                    </td>
                    <td style="text-align:right; font-weight:600;">{{ number_format((float)$pos->shares, 2) }}</td>
                    <td style="text-align:right;">${{ number_format((float)$pos->avg_cost, 2) }}</td>
                    <td style="text-align:right;">
                        @if($pos->current_price)
                            <span style="color:{{ (float)$pos->current_price >= (float)$pos->avg_cost ? 'var(--green)' : 'var(--red)' }}; font-weight:600;">
                                ${{ number_format((float)$pos->current_price, 2) }}
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td style="text-align:right; font-weight:600;">${{ number_format($pos->getCurrentValue(), 2) }}</td>
                    <td style="text-align:right; font-weight:700; color:{{ $color }};">
                        {{ $pnl >= 0 ? '+' : '' }}${{ number_format($pnl, 2) }}
                    </td>
                    <td style="text-align:right; font-weight:700; color:{{ $color }};">
                        {{ $retPct >= 0 ? '+' : '' }}{{ number_format($retPct, 2) }}%
                    </td>
                    <td style="text-align:right;">
                        @if($pos->target_price)
                            <span style="color:#5b8af5;">${{ number_format((float)$pos->target_price, 2) }}</span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td style="width:100px; padding:12px 16px;">
                        @if($prog !== null)
                        <div style="background:var(--border); border-radius:4px; height:6px; overflow:hidden; margin-bottom:2px;">
                            <div style="height:6px; border-radius:4px; width:{{ $prog }}%; background:{{ $prog >= 100 ? 'var(--green)' : '#5b8af5' }};"></div>
                        </div>
                        <div class="text-muted" style="font-size:10px; text-align:center;">{{ number_format($prog, 0) }}%</div>
                        @else
                        <span class="text-muted" style="font-size:11px;">—</span>
                        @endif
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                        <a href="{{ route('portfolio.edit', $pos) }}" style="color:#5b8af5; font-size:12px; text-decoration:none; margin-right:12px;">Editar</a>
                        <form method="POST" action="{{ route('portfolio.destroy', $pos) }}" style="display:inline;" onsubmit="return confirm('¿Eliminar {{ $pos->symbol }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="color:var(--red); font-size:12px; background:none; border:none; cursor:pointer;">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @if($pos->thesis)
                <tr style="background:rgba(41,98,255,0.03);">
                    <td colspan="11" style="padding:8px 16px; font-size:12px; color:var(--text-secondary); border-bottom:1px solid var(--border);">
                        💡 <em>{{ $pos->thesis }}</em>
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<div class="card" style="padding:48px; text-align:center; margin-bottom:16px;">
    <div style="font-size:40px; margin-bottom:12px;">📈</div>
    <div style="font-size:16px; color:#fff; margin-bottom:8px;">No tienes posiciones abiertas</div>
    <div class="text-muted" style="font-size:13px; margin-bottom:20px;">Agrega tus primeras inversiones de largo plazo</div>
    <a href="{{ route('portfolio.create') }}" class="btn-primary" style="text-decoration:none;">+ Nueva Posición</a>
</div>
@endif

{{-- ═══════ POSICIONES CERRADAS ═══════ --}}
@if($closed->count() > 0)
<div class="card" style="overflow:hidden;">
    <div style="padding:16px 20px; border-bottom:1px solid var(--border);">
        <h2>Posiciones Cerradas <span class="text-muted" style="font-size:13px; margin-left:8px;">{{ $closed->count() }}</span></h2>
    </div>
    <table>
        <thead>
            <tr>
                <th>Símbolo</th>
                <th>Tipo</th>
                <th style="text-align:right;">Acciones</th>
                <th style="text-align:right;">Costo Prom</th>
                <th style="text-align:right;">Precio Salida</th>
                <th style="text-align:right;">G/P $</th>
                <th style="text-align:right;">G/P %</th>
                <th style="text-align:right;">Dividendos</th>
                <th style="text-align:right;">Fecha Salida</th>
            </tr>
        </thead>
        <tbody>
            @foreach($closed as $pos)
            @php
                $pnl    = $pos->getUnrealizedPnl();
                $retPct = $pos->getReturnPercent();
                $color  = $pnl >= 0 ? 'var(--green)' : 'var(--red)';
            @endphp
            <tr style="opacity:0.7;">
                <td>
                    <div style="font-weight:700; color:#fff;">{{ $pos->symbol }}</div>
                    @if($pos->name)<div class="text-muted" style="font-size:11px;">{{ Str::limit($pos->name, 22) }}</div>@endif
                </td>
                <td><span style="font-size:11px; padding:2px 8px; border-radius:4px; background:var(--bg-hover); color:var(--text-secondary); text-transform:uppercase;">{{ $pos->asset_type }}</span></td>
                <td style="text-align:right;">{{ number_format((float)$pos->shares, 2) }}</td>
                <td style="text-align:right;">${{ number_format((float)$pos->avg_cost, 2) }}</td>
                <td style="text-align:right;">${{ $pos->exit_price ? number_format((float)$pos->exit_price, 2) : '—' }}</td>
                <td style="text-align:right; font-weight:700; color:{{ $color }};">{{ $pnl >= 0 ? '+' : '' }}${{ number_format($pnl, 2) }}</td>
                <td style="text-align:right; font-weight:700; color:{{ $color }};">{{ $retPct >= 0 ? '+' : '' }}{{ number_format($retPct, 2) }}%</td>
                <td style="text-align:right; color:var(--green);">+${{ number_format((float)$pos->dividends_received, 2) }}</td>
                <td style="text-align:right; color:var(--text-secondary);">{{ $pos->exit_date?->format('d/m/Y') ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@if($open->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const COLORS = ['#2962ff','#26a69a','#f59e0b','#ef5350','#a855f7','#06b6d4','#ec4899','#84cc16','#f97316','#64748b'];
const TEXT = '#787b86';

const allocation = @json($allocation);
const sectors    = @json($bySector);

function makeDoughnut(id, data, labels) {
    new Chart(document.getElementById(id), {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: COLORS.slice(0, data.length).map(c => c + '33'),
                borderColor:     COLORS.slice(0, data.length),
                borderWidth: 2,
            }]
        },
        options: {
            responsive: false,
            cutout: '62%',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.label}: ${ctx.parsed.toFixed(1)}%`
                    }
                }
            }
        }
    });
}

makeDoughnut('allocationChart', allocation.map(a => a.pct), allocation.map(a => a.symbol));
makeDoughnut('sectorChart',     sectors.map(s => s.pct),    sectors.map(s => s.sector));
</script>
@endif
@endsection
