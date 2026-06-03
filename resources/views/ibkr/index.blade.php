@extends('layouts.app')
@section('title', 'IBKR Sync - TradeLog')

@section('content')

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <h1 style="margin:0;">Sincronización IBKR</h1>
        <div class="text-muted" style="font-size:13px; margin-top:4px;">Importa y sincroniza tus trades desde Interactive Brokers</div>
    </div>
</div>

{{-- ── Resultados de sincronización ───────────────────────────────────────── --}}
@if(session('ibkr_results'))
    @php $r = session('ibkr_results'); @endphp
    <div style="background:rgba(46,213,115,.08); border:1px solid var(--green); border-radius:10px; padding:20px; margin-bottom:20px;">
        <div style="font-size:14px; font-weight:700; color:var(--green); margin-bottom:12px;">✓ Sincronización completada</div>
        <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:12px;">
            <div style="text-align:center;">
                <div style="font-size:28px; font-weight:800; color:var(--green);">{{ $r['imported'] }}</div>
                <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Nuevas</div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:28px; font-weight:800; color:var(--blue);">{{ $r['closed'] }}</div>
                <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Cerradas</div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:28px; font-weight:800; color:var(--text-muted);">{{ $r['duplicates'] }}</div>
                <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Duplicados</div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:28px; font-weight:800; color:{{ count($r['errors']) > 0 ? 'var(--red)' : 'var(--text-muted)' }};">{{ count($r['errors']) }}</div>
                <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; font-weight:700;">Errores</div>
            </div>
        </div>

        @if(!empty($r['errors']))
            <div style="background:rgba(239,83,80,.1); border-radius:6px; padding:10px 14px; margin-bottom:12px;">
                <div style="font-size:12px; font-weight:700; color:var(--red); margin-bottom:6px;">Errores:</div>
                @foreach($r['errors'] as $err)
                    <div style="font-size:11px; color:var(--red); font-family:monospace; margin-bottom:2px;">{{ $err }}</div>
                @endforeach
            </div>
        @endif

        @if(!empty($r['trades']))
            <details style="cursor:pointer;">
                <summary style="font-size:12px; color:var(--text-secondary); font-weight:600; user-select:none; padding:6px 0;">
                    Ver detalle de operaciones ({{ count($r['trades']) }})
                </summary>
                <div style="margin-top:10px; max-height:300px; overflow-y:auto;">
                    <table style="width:100%; font-size:11px;">
                        <thead>
                            <tr style="color:var(--text-muted);">
                                <th style="text-align:left; padding:4px 8px;">Acción</th>
                                <th style="text-align:left; padding:4px 8px;">Símbolo</th>
                                <th style="text-align:left; padding:4px 8px;">Tipo</th>
                                <th style="text-align:right; padding:4px 8px;">Precio</th>
                                <th style="text-align:right; padding:4px 8px;">Qty</th>
                                <th style="text-align:right; padding:4px 8px;">P&L</th>
                                <th style="text-align:left; padding:4px 8px;">Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($r['trades'] as $t)
                            <tr style="border-top:1px solid var(--border);">
                                <td style="padding:4px 8px;">
                                    <span style="font-size:10px; padding:1px 6px; border-radius:3px; font-weight:700;
                                        background:{{ str_contains($t['action'],'open') ? 'rgba(46,213,115,.15)' : (str_contains($t['action'],'close') ? 'rgba(59,130,246,.15)' : 'rgba(245,158,11,.15)') }};
                                        color:{{ str_contains($t['action'],'open') ? 'var(--green)' : (str_contains($t['action'],'close') ? 'var(--blue)' : '#f59e0b') }};">
                                        {{ strtoupper($t['action']) }}
                                    </span>
                                </td>
                                <td style="padding:4px 8px; font-weight:700;">{{ $t['symbol'] }}</td>
                                <td style="padding:4px 8px; color:var(--text-muted);">{{ $t['type'] }}</td>
                                <td style="padding:4px 8px; text-align:right;">${{ number_format($t['price'], 2) }}</td>
                                <td style="padding:4px 8px; text-align:right;">{{ $t['qty'] }}</td>
                                <td style="padding:4px 8px; text-align:right; font-weight:700;
                                    color:{{ $t['pnl'] === null ? 'var(--text-muted)' : ($t['pnl'] >= 0 ? 'var(--green)' : 'var(--red)') }};">
                                    {{ $t['pnl'] !== null ? ($t['pnl'] >= 0 ? '+' : '').'$'.number_format($t['pnl'],2) : '--' }}
                                </td>
                                <td style="padding:4px 8px; color:var(--text-muted);">{{ $t['date'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </details>
        @endif
    </div>
@endif

@if($errors->any())
    <div style="background:rgba(239,83,80,.1); border:1px solid var(--red); border-radius:10px; padding:16px 20px; margin-bottom:20px; font-size:13px; color:var(--red);">
        {{ $errors->first() }}
    </div>
@endif

{{-- ── Stats actuales ──────────────────────────────────────────────────────── --}}
<div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:20px;">
    <div class="card" style="padding:18px 20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px;">Total Trades</div>
        <div style="font-size:28px; font-weight:800;">{{ $totalTrades }}</div>
        <div class="text-muted" style="font-size:11px; margin-top:2px;">En el sistema</div>
    </div>
    <div class="card" style="padding:18px 20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px;">Cerradas</div>
        <div style="font-size:28px; font-weight:800; color:var(--blue);">{{ $closedTrades }}</div>
    </div>
    <div class="card" style="padding:18px 20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px;">Abiertas</div>
        <div style="font-size:28px; font-weight:800; color:{{ $openTrades > 0 ? '#f59e0b' : 'var(--text-muted)' }};">{{ $openTrades }}</div>
        @if($openTrades > 0)
            <div class="text-muted" style="font-size:11px; margin-top:2px;">Pendientes de cierre</div>
        @endif
    </div>
    <div class="card" style="padding:18px 20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px;">Último Trade</div>
        <div style="font-size:16px; font-weight:800;">{{ $lastTrade ? $lastTrade->entry_date->format('M d, Y') : '--' }}</div>
        @if($lastTrade)
            <div class="text-muted" style="font-size:11px; margin-top:2px;">{{ Str::before($lastTrade->symbol, ' ') ?: $lastTrade->symbol }}</div>
        @endif
    </div>
</div>

{{-- ── Dos columnas: API sync + Upload manual ──────────────────────────────── --}}
<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">

    {{-- Sincronización API --}}
    <div class="card" style="padding:24px;">
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
            @if($hasConfig)
                <div style="width:10px; height:10px; border-radius:50%; background:var(--green); box-shadow:0 0 8px var(--green); flex-shrink:0;"></div>
            @else
                <div style="width:10px; height:10px; border-radius:50%; background:#f59e0b; flex-shrink:0;"></div>
            @endif
            <h3 style="margin:0; font-size:14px; font-weight:700;">Sincronización API Automática</h3>
        </div>

        @if($hasConfig)
            <div style="font-size:13px; color:var(--text-secondary); margin-bottom:16px; line-height:1.6;">
                Conectado a IBKR Flex Query. El sistema sincroniza cada <strong>10 minutos</strong> en días hábiles de <strong>8:30 AM – 5:00 PM CDMX</strong>.
            </div>
            <div style="background:var(--bg-secondary); border-radius:8px; padding:12px 14px; font-size:11px; color:var(--text-muted); font-family:monospace; margin-bottom:16px;">
                Token: ****{{ substr(config('services.ibkr.flex_token'), -6) }}<br>
                Query ID: {{ config('services.ibkr.flex_query_id') }}
            </div>
            <form method="POST" action="{{ route('ibkr.sync') }}" id="syncForm">
                @csrf
                <button type="button" id="syncBtn" class="btn-primary" style="width:100%; padding:12px; font-size:14px; font-weight:700;"
                    onclick="startSync()">
                    🔄 Sincronizar Ahora
                </button>
            </form>
            <div id="syncStatus" style="display:none; font-size:12px; color:var(--text-muted); text-align:center; margin-top:10px; line-height:1.6;">
                ⏳ Contactando IBKR... puede tardar 15–30 segundos.<br>
                <span style="font-size:11px;">No cierres esta ventana.</span>
            </div>
            <div style="font-size:11px; color:var(--text-muted); text-align:center; margin-top:8px;">
                Trae los últimos 30 días · Nunca duplica
            </div>
        @else
            <div style="font-size:13px; color:#f59e0b; line-height:1.6;">
                ⚠️ Configura <code style="background:var(--bg-secondary); padding:1px 4px; border-radius:3px;">IBKR_FLEX_TOKEN</code> e <code style="background:var(--bg-secondary); padding:1px 4px; border-radius:3px;">IBKR_FLEX_QUERY_ID</code> en el <code>.env</code> del servidor para habilitar la sincronización automática.
            </div>
        @endif
    </div>

    {{-- Upload manual --}}
    <div class="card" style="padding:24px;">
        <h3 style="margin:0 0 16px; font-size:14px; font-weight:700;">📁 Importar XML Manual</h3>
        <div style="font-size:13px; color:var(--text-secondary); margin-bottom:16px; line-height:1.6;">
            Descarga el Flex Query XML desde IBKR Portal y súbelo aquí. Ideal para importar historial completo de un año o un período específico.
        </div>
        <form method="POST" action="{{ route('ibkr.import') }}" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom:12px;">
                <label style="font-size:11px; color:var(--text-muted); font-weight:700; text-transform:uppercase; display:block; margin-bottom:6px;">Archivo XML de IBKR</label>
                <input type="file" name="xml_file" accept=".xml" class="form-input" style="width:100%;" required>
            </div>
            <button type="submit" class="btn-ghost" style="width:100%; padding:10px; font-weight:600;">
                Subir e Importar
            </button>
        </form>
        <div style="margin-top:12px; font-size:11px; color:var(--text-muted); line-height:1.5;">
            💡 Puedes subir el mismo XML varias veces — los duplicados se detectan por <code style="background:var(--bg-secondary); padding:1px 4px; border-radius:3px;">ibOrderID</code> y se ignoran automáticamente.
        </div>
    </div>
</div>

{{-- ── Cómo funciona ───────────────────────────────────────────────────────── --}}
<div class="card" style="padding:20px;">
    <h3 style="margin:0 0 14px; font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted);">¿Cómo procesa los trades?</h3>
    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; font-size:12px; color:var(--text-secondary); line-height:1.7;">
        <div>
            <div style="font-size:13px; font-weight:700; color:var(--text-primary); margin-bottom:4px;">① Partial fills</div>
            Agrupa todas las ejecuciones del mismo <code style="background:var(--bg-secondary); padding:1px 4px; border-radius:3px;">ibOrderID</code> y calcula precio promedio ponderado.
        </div>
        <div>
            <div style="font-size:13px; font-weight:700; color:var(--text-primary); margin-bottom:4px;">② Aperturas y cierres</div>
            Cruza automáticamente las órdenes de apertura (O) con las de cierre (C) por símbolo, usando el P&L FIFO real de IBKR.
        </div>
        <div>
            <div style="font-size:13px; font-weight:700; color:var(--text-primary); margin-bottom:4px;">③ Opciones expiradas</div>
            Lee la sección <code style="background:var(--bg-secondary); padding:1px 4px; border-radius:3px;">OptionEAE</code> de IBKR para cerrar correctamente opciones que vencieron sin valor con el P&L real.
        </div>
    </div>
</div>

<script>
function startSync() {
    document.getElementById('syncBtn').disabled = true;
    document.getElementById('syncBtn').textContent = '⏳ Sincronizando...';
    document.getElementById('syncStatus').style.display = 'block';
    document.getElementById('syncForm').submit();
}
</script>

@endsection
