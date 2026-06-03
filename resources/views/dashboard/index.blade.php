@extends('layouts.app')
@section('title', 'Dashboard - TradeLog')

@section('content')

{{-- HEADER --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
    <div>
        <h1>Dashboard</h1>
        <div class="text-muted" style="font-size:13px; margin-top:2px;">
            {{ now()->setTimezone('America/Mexico_City')->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
            &nbsp;·&nbsp; {{ now()->setTimezone('America/Mexico_City')->format('H:i') }} CDMX
        </div>
    </div>
    <a href="{{ route('trades.create') }}" class="btn-primary" style="text-decoration:none;">+ Nueva Operación</a>
</div>

{{-- ═══════════════════════════════════════════
     FILA 1: BIAS DEL DÍA (si hay reporte)
═══════════════════════════════════════════ --}}
@if($reportBias)
@php
    $biasColors = [
        'green'  => ['bg' => 'rgba(38,166,154,0.08)', 'border' => 'rgba(38,166,154,0.4)', 'text' => '#26a69a'],
        'red'    => ['bg' => 'rgba(239,83,80,0.08)',  'border' => 'rgba(239,83,80,0.4)',  'text' => '#ef5350'],
        'yellow' => ['bg' => 'rgba(249,168,37,0.08)', 'border' => 'rgba(249,168,37,0.4)', 'text' => '#f9a825'],
    ];
    $bc = $biasColors[$reportBias['color']];
@endphp
<div class="card" style="padding:16px 24px; margin-bottom:12px; border-color:{{ $bc['border'] }}; background:{{ $bc['bg'] }};">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:16px;">
            <div style="font-size:40px; line-height:1;">{{ $reportBias['icon'] }}</div>
            <div>
                <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:2px;">
                    Bias QQQ — Hoy
                </div>
                <div style="font-size:26px; font-weight:900; color:{{ $bc['text'] }}; letter-spacing:-.5px;">
                    {{ $reportBias['label'] }}
                    @if($reportBias['conviction'])
                    <span style="font-size:13px; font-weight:600; color:var(--text-muted); margin-left:8px;">
                        Convicción: {{ $reportBias['conviction'] }}
                    </span>
                    @endif
                </div>
                @if($reportBias['reason'])
                <div style="font-size:13px; color:var(--text-secondary); margin-top:4px;">
                    {{ $reportBias['reason'] }}
                </div>
                @endif
            </div>
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
            @if($todayReport)
            <span class="text-muted" style="font-size:12px;">
                {{ $todayReport->report_date->isToday() ? 'Reporte de hoy' : 'Último: ' . $todayReport->report_date->format('d/m') }}
            </span>
            @endif
            <a href="{{ route('reports.index') }}" style="font-size:12px; color:#5b8af5; text-decoration:none; padding:6px 14px; border:1px solid rgba(91,138,245,0.3); border-radius:6px;">
                Ver reporte completo →
            </a>
        </div>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════
     FILA 2: STATS CARDS
═══════════════════════════════════════════ --}}
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:12px;">
    <div class="card" style="padding:18px 20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:6px;">P&L Total</div>
        <div style="font-size:26px; font-weight:800; {{ $totalPnL >= 0 ? 'color:var(--green)' : 'color:var(--red)' }};">
            {{ $totalPnL >= 0 ? '+' : '' }}${{ number_format($totalPnL, 2) }}
        </div>
        <div class="text-muted" style="font-size:11px; margin-top:3px;">Acumulado</div>
    </div>
    <div class="card" style="padding:18px 20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:6px;">Win Rate</div>
        <div style="font-size:26px; font-weight:800; color:#5b8af5;">{{ number_format($winRate, 1) }}%</div>
        <div class="text-muted" style="font-size:11px; margin-top:3px;">Operaciones cerradas</div>
    </div>
    <div class="card" style="padding:18px 20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:6px;">P&L de Hoy</div>
        @if($todayMetric)
        <div style="font-size:26px; font-weight:800; {{ $todayMetric->daily_pnl >= 0 ? 'color:var(--green)' : 'color:var(--red)' }};">
            {{ $todayMetric->daily_pnl >= 0 ? '+' : '' }}${{ number_format($todayMetric->daily_pnl, 2) }}
        </div>
        <div class="text-muted" style="font-size:11px; margin-top:3px;">{{ $todayMetric->trades_count }} operaciones</div>
        @else
        <div style="font-size:26px; font-weight:800; color:var(--text-muted);">—</div>
        <div class="text-muted" style="font-size:11px; margin-top:3px;">Sin operaciones hoy</div>
        @endif
    </div>
    <div class="card" style="padding:18px 20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:6px;">Racha Actual</div>
        @if($currentStreak > 0)
        <div style="font-size:26px; font-weight:800; {{ $streakType === 'win' ? 'color:var(--green)' : 'color:var(--red)' }};">
            {{ $currentStreak }}{{ $streakType === 'win' ? 'G' : 'P' }}
        </div>
        <div class="text-muted" style="font-size:11px; margin-top:3px;">Racha de {{ $streakType === 'win' ? 'ganancias' : 'pérdidas' }}</div>
        @else
        <div style="font-size:26px; font-weight:800; color:var(--text-muted);">—</div>
        <div class="text-muted" style="font-size:11px; margin-top:3px;">Sin racha activa</div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════
     FILA 3: AGENDA + NIVELES
═══════════════════════════════════════════ --}}
@if($reportAgenda || $reportLevels || $reportSummary)
<div style="display:grid; grid-template-columns:1fr {{ $reportLevels ? '240px' : '' }}; gap:12px; margin-bottom:12px;">

    {{-- TIMELINE DE CATALIZADORES --}}
    @if($reportAgenda)
    <div class="card" style="overflow:hidden;">
        <div style="padding:14px 20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
            <div>
                <span style="font-size:13px; font-weight:700; color:#fff;">📅 Agenda del Día</span>
                <span class="text-muted" style="font-size:12px; margin-left:8px;">Horario Ciudad de México</span>
            </div>
            <a href="{{ route('reports.index') }}" style="font-size:12px; color:#5b8af5; text-decoration:none;">Reporte completo →</a>
        </div>
        <div style="padding:8px 0;">
            @php
                $now = now()->setTimezone('America/Mexico_City');
                $nowStr = $now->format('H:i');
            @endphp
            @foreach($reportAgenda as $i => $event)
            @php
                $isPast = strcmp($event['time_ct'], $nowStr) < 0;
                $isNext = !$isPast && ($i === 0 || strcmp($reportAgenda[$i-1]['time_ct'], $nowStr) < 0);
                $impactColor = $event['impact'] === 'high' ? '#ef5350' : ($event['impact'] === 'medium' ? '#f9a825' : '#4c525e');
                $impactBg    = $event['impact'] === 'high' ? 'rgba(239,83,80,0.12)' : ($event['impact'] === 'medium' ? 'rgba(249,168,37,0.08)' : 'transparent');
            @endphp
            <div style="display:flex; gap:0; padding:0 20px; {{ $isNext ? 'background:rgba(41,98,255,0.06);' : '' }}">
                {{-- Línea de tiempo --}}
                <div style="width:60px; flex-shrink:0; padding:10px 0; display:flex; flex-direction:column; align-items:center;">
                    <div style="font-size:12px; font-weight:700; color:{{ $isPast ? 'var(--text-muted)' : ($isNext ? '#5b8af5' : '#fff') }};">
                        {{ $event['time_ct'] }}
                    </div>
                    @if(!$loop->last)
                    <div style="width:1px; flex:1; min-height:16px; background:{{ $isNext ? '#5b8af5' : 'var(--border)' }}; margin-top:4px;"></div>
                    @endif
                </div>
                {{-- Dot --}}
                <div style="width:24px; flex-shrink:0; display:flex; flex-direction:column; align-items:center; padding-top:12px;">
                    <div style="width:10px; height:10px; border-radius:50%; flex-shrink:0;
                        background:{{ $isPast ? 'var(--border)' : ($isNext ? '#5b8af5' : $impactColor) }};
                        {{ $isNext ? 'box-shadow:0 0 0 3px rgba(91,138,245,0.2);' : '' }}">
                    </div>
                    @if(!$loop->last)
                    <div style="width:1px; flex:1; min-height:16px; background:{{ $isNext ? '#5b8af5' : 'var(--border)' }};"></div>
                    @endif
                </div>
                {{-- Contenido --}}
                <div style="flex:1; padding:8px 0 8px 12px; {{ $loop->last ? '' : 'border-bottom:1px solid rgba(42,46,57,0.3);' }}">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px;">
                        <div style="font-size:13px; font-weight:{{ $isNext ? '700' : '500' }};
                                    color:{{ $isPast ? 'var(--text-muted)' : '#fff' }}; line-height:1.4;">
                            {{ $event['event'] }}
                            @if($isNext)
                            <span style="font-size:10px; padding:1px 6px; border-radius:3px; background:rgba(91,138,245,0.2); color:#5b8af5; margin-left:6px; font-weight:700;">
                                PRÓXIMO
                            </span>
                            @endif
                        </div>
                        @if($event['impact'] !== 'low')
                        <span style="font-size:10px; padding:2px 7px; border-radius:4px; flex-shrink:0; font-weight:700;
                                     background:{{ $impactBg }}; color:{{ $impactColor }}; border:1px solid {{ $impactColor }}33;">
                            {{ $event['impact'] === 'high' ? 'ALTO' : 'MEDIO' }}
                        </span>
                        @endif
                    </div>
                    @if($event['detail'])
                    <div style="font-size:11px; color:var(--text-muted); margin-top:2px; line-height:1.4;">
                        {{ Str::limit($event['detail'], 80) }}
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- NIVELES CLAVE --}}
    @if($reportLevels)
    <div class="card" style="overflow:hidden; align-self:start;">
        <div style="padding:14px 16px; border-bottom:1px solid var(--border);">
            <span style="font-size:13px; font-weight:700; color:#fff;">📊 Niveles QQQ</span>
        </div>
        <div style="padding:8px 0;">
            @foreach($reportLevels as $level)
            <div style="display:flex; justify-content:space-between; align-items:center; padding:8px 16px; border-bottom:1px solid rgba(42,46,57,0.3);">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:10px; font-weight:800; padding:2px 6px; border-radius:3px;
                        {{ $level['type'] === 'R' ? 'background:rgba(239,83,80,0.15); color:#ef5350;' : 'background:rgba(38,166,154,0.15); color:#26a69a;' }}">
                        {{ $level['type'] === 'R' ? 'RES' : 'SOP' }}
                    </span>
                    <span class="text-muted" style="font-size:11px;">{{ Str::limit($level['label'], 16) }}</span>
                </div>
                <span style="font-size:14px; font-weight:700; color:#fff; font-family:monospace;">
                    ${{ number_format((float)$level['price'], 2) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endif

{{-- RESUMEN COPIABLE --}}
@if($reportSummary)
<div class="card" style="padding:14px 20px; margin-bottom:12px; border-color:rgba(38,166,154,0.2); cursor:pointer;" onclick="copyResumen()" title="Clic para copiar">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
        <span style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.08em;">📋 Resumen del día — clic para copiar</span>
        <span id="copyBtn" style="font-size:11px; color:#5b8af5;">Copiar</span>
    </div>
    <pre id="summaryText" style="font-size:12px; color:var(--green); font-family:'JetBrains Mono',monospace; margin:0; white-space:pre-wrap; line-height:1.7;">{{ $reportSummary }}</pre>
</div>
@endif

{{-- ═══════════════════════════════════════════
     FILA 4: TRADES + SIDEBAR
═══════════════════════════════════════════ --}}
<div style="display:grid; grid-template-columns:1fr 340px; gap:16px;">

    {{-- TRADES RECIENTES --}}
    <div class="card" style="overflow:hidden;">
        <div style="padding:14px 20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
            <h2>Operaciones Recientes</h2>
            <a href="{{ route('trades.index') }}" style="font-size:12px; color:#5b8af5; text-decoration:none;">Ver todas →</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Símbolo</th>
                    <th>Tipo</th>
                    <th style="text-align:right;">Compra</th>
                    <th style="text-align:right;">Venta</th>
                    <th style="text-align:right;">G/P Neto</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentTrades as $trade)
                <tr style="cursor:pointer;" onclick="window.location='{{ route('trades.show', $trade) }}'">
                    <td><span style="font-weight:700; color:#fff;">{{ $trade->symbol }}</span></td>
                    <td>
                        <span style="font-size:10px; font-weight:700; padding:2px 7px; border-radius:4px; text-transform:uppercase;
                            {{ $trade->trade_type === 'call' ? 'background:rgba(38,166,154,0.15); color:var(--green);'
                            : ($trade->trade_type === 'put' ? 'background:rgba(239,83,80,0.15); color:var(--red);'
                            : 'background:rgba(91,138,245,0.15); color:#5b8af5;') }}">
                            {{ $trade->trade_type }}
                        </span>
                    </td>
                    <td style="text-align:right; font-size:13px;">${{ number_format($trade->entry_price, 2) }}</td>
                    <td style="text-align:right; font-size:13px;">
                        {{ $trade->exit_price ? '$'.number_format($trade->exit_price,2) : '<span style="color:#f9a825; font-size:11px;">Abierta</span>' }}
                    </td>
                    <td style="text-align:right; font-weight:700; {{ ($trade->net_p_l ?? $trade->p_l ?? 0) >= 0 ? 'color:var(--green)' : 'color:var(--red)' }}">
                        @if($trade->net_p_l !== null)
                            {{ $trade->net_p_l >= 0 ? '+' : '' }}${{ number_format($trade->net_p_l, 2) }}
                        @elseif($trade->p_l !== null)
                            {{ $trade->p_l >= 0 ? '+' : '' }}${{ number_format($trade->p_l, 2) }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td class="text-muted" style="font-size:12px;">{{ $trade->entry_date->format('d/m') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center; color:var(--text-muted); padding:32px 16px;">
                        Sin operaciones.
                        <a href="{{ route('ibkr.index') }}" style="color:#5b8af5;">Importa desde IBKR</a>
                        o <a href="{{ route('trades.create') }}" style="color:#5b8af5;">agrega una</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- COLUMNA DERECHA --}}
    <div style="display:flex; flex-direction:column; gap:10px;">

        {{-- Acciones rápidas --}}
        <div class="card" style="padding:16px;">
            <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.08em; margin-bottom:12px;">Acciones Rápidas</div>
            <div style="display:flex; flex-direction:column; gap:7px;">
                <a href="{{ route('trades.create') }}" class="btn-primary" style="text-decoration:none; text-align:center; font-size:13px; padding:8px;">+ Nueva Operación</a>
                <a href="{{ route('ibkr.index') }}" class="btn-ghost" style="text-decoration:none; text-align:center; font-size:13px; padding:7px;">📊 Importar IBKR</a>
                @if($template)
                <form method="POST" action="{{ route('reports.generate', $template) }}">
                    @csrf
                    <input type="hidden" name="date" value="{{ now()->toDateString() }}">
                    <button type="submit" class="btn-ghost" style="width:100%; font-size:13px; padding:7px;">✨ Generar Reporte QQQ</button>
                </form>
                @endif
            </div>
        </div>

        {{-- Noticias --}}
        @if($todayNews->isNotEmpty())
        <div class="card" style="overflow:hidden; flex:1;">
            <div style="padding:12px 16px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:13px; font-weight:700; color:#fff;">📰 Noticias</span>
                <a href="{{ route('news.index') }}" style="font-size:12px; color:#5b8af5; text-decoration:none;">Ver más →</a>
            </div>
            <div>
                @foreach($todayNews as $news)
                <a href="{{ route('news.index', ['asset_id' => $news->asset_id]) }}"
                   style="display:block; padding:12px 16px; border-bottom:1px solid rgba(42,46,57,0.4); text-decoration:none;" class="card-hover">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                        <span style="font-weight:700; color:#fff; font-size:13px;">{{ $news->asset->symbol }}</span>
                        <span style="font-size:10px; padding:1px 7px; border-radius:4px; font-weight:600;
                            {{ $news->sentiment === 'positive' ? 'background:rgba(38,166,154,0.15); color:var(--green);'
                            : ($news->sentiment === 'negative' ? 'background:rgba(239,83,80,0.15); color:var(--red);'
                            : 'background:rgba(42,46,57,0.8); color:var(--text-muted);') }}">
                            {{ ucfirst($news->sentiment) }}
                        </span>
                    </div>
                    <p style="font-size:12px; color:var(--text-secondary); margin:0; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                        {{ $news->summary }}
                    </p>
                </a>
                @endforeach
            </div>
        </div>
        @else
        <div class="card" style="padding:20px; text-align:center;">
            <div style="font-size:24px; margin-bottom:8px;">📰</div>
            <div class="text-muted" style="font-size:12px; margin-bottom:10px;">Sin noticias de hoy</div>
            <a href="{{ route('news.index') }}" style="font-size:12px; color:#5b8af5; text-decoration:none;">Generar noticias →</a>
        </div>
        @endif

        {{-- Sin reporte — CTA --}}
        @if(!$todayReport && $template)
        <div class="card" style="padding:16px; border-color:rgba(249,168,37,0.3); background:rgba(249,168,37,0.04);">
            <div style="font-size:12px; color:#f9a825; font-weight:700; margin-bottom:6px;">⚠️ Sin reporte QQQ hoy</div>
            <div class="text-muted" style="font-size:12px; margin-bottom:10px;">Se genera automáticamente a las {{ $template->schedule_time }} CDMX</div>
            <a href="{{ route('reports.index') }}" style="font-size:12px; color:#5b8af5; text-decoration:none;">Ver reportes →</a>
        </div>
        @elseif(!$template)
        <div class="card" style="padding:16px; border-color:rgba(91,138,245,0.3); background:rgba(91,138,245,0.04);">
            <div style="font-size:12px; color:#5b8af5; font-weight:700; margin-bottom:6px;">📊 Reporte QQQ</div>
            <div class="text-muted" style="font-size:12px; margin-bottom:10px;">Configura tu reporte diario automático</div>
            <a href="{{ route('reports.setup') }}" class="btn-primary" style="text-decoration:none; text-align:center; font-size:12px; padding:6px 14px; display:block;">Configurar →</a>
        </div>
        @endif

    </div>
</div>

<script>
function copyResumen() {
    const text = document.getElementById('summaryText').innerText;
    navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById('copyBtn');
        btn.textContent = '✓ Copiado';
        btn.style.color = '#26a69a';
        setTimeout(() => { btn.textContent = 'Copiar'; btn.style.color = '#5b8af5'; }, 2000);
    });
}
</script>
@endsection
