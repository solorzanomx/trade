@extends('layouts.app')
@section('title', 'Cerrar / Editar ' . $trade->symbol . ' - TradeLog')

@section('content')
<div style="max-width:760px;">
    <div style="display:flex; align-items:center; gap:12px; margin-bottom:24px;">
        <a href="{{ route('trades.index') }}" style="color:var(--text-muted); text-decoration:none; font-size:13px;">← Operaciones</a>
        <span class="text-muted">/</span>
        <h1>{{ $trade->symbol }}
            <span style="font-size:14px; font-weight:500; padding:3px 10px; border-radius:4px; margin-left:8px;
                {{ $trade->status === 'open'
                    ? 'background:rgba(249,168,37,0.15); color:#f9a825;'
                    : 'background:rgba(91,138,245,0.15); color:#5b8af5;' }}">
                {{ $trade->status === 'open' ? 'Abierta' : 'Cerrada' }}
            </span>
        </h1>
    </div>

    {{-- Resumen de entrada (solo lectura) --}}
    <div class="card" style="padding:16px 20px; margin-bottom:12px; border-color:rgba(42,46,57,0.8);">
        <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:12px;">Datos de Entrada (solo lectura)</div>
        <div style="display:flex; gap:32px; flex-wrap:wrap; font-size:13px;">
            <div><span class="text-muted">Entrada:</span> <strong style="color:#fff;">${{ number_format($trade->entry_price, 2) }}</strong></div>
            <div><span class="text-muted">Contratos:</span> <strong style="color:#fff;">{{ $trade->quantity }}</strong></div>
            <div><span class="text-muted">Fecha:</span> <strong style="color:#fff;">{{ $trade->entry_date->format('d/m/Y') }}</strong></div>
            <div><span class="text-muted">Tipo:</span> <strong style="color:#fff; text-transform:uppercase;">{{ $trade->trade_type }}</strong></div>
            @if($trade->stop_loss)<div><span class="text-muted">Stop Loss:</span> <strong style="color:var(--red);">${{ number_format($trade->stop_loss, 2) }}</strong></div>@endif
            @if($trade->take_profit)<div><span class="text-muted">Target:</span> <strong style="color:var(--green);">${{ number_format($trade->take_profit, 2) }}</strong></div>@endif
            @if($trade->strategy)<div><span class="text-muted">Estrategia:</span> <strong style="color:#fff;">{{ $trade->strategy }}</strong></div>@endif
        </div>
    </div>

    <form action="{{ route('trades.update', $trade) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Cierre --}}
        <div class="card" style="padding:24px; margin-bottom:12px;">
            <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border);">
                Cierre de Operación
            </div>
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px;">
                <div>
                    <label>Precio salida</label>
                    <div style="position:relative;">
                        <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--text-muted);">$</span>
                        <input type="number" name="exit_price" id="exitPrice" class="form-input"
                               value="{{ old('exit_price', $trade->exit_price) }}"
                               step="0.0001" min="0" placeholder="8.50" style="padding-left:22px;">
                    </div>
                </div>
                <div>
                    <label>Fecha salida</label>
                    <input type="date" name="exit_date" class="form-input"
                           value="{{ old('exit_date', $trade->exit_date?->format('Y-m-d')) }}">
                </div>
                <div>
                    <label>Hora salida</label>
                    <input type="time" name="exit_time" class="form-input"
                           value="{{ old('exit_time', $trade->exit_time) }}" step="60">
                </div>
                <div>
                    <label>Comisión salida</label>
                    <div style="position:relative;">
                        <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--text-muted);">$</span>
                        <input type="number" name="commission" class="form-input"
                               value="{{ old('commission', $trade->commission ?? '0') }}"
                               step="0.01" min="0" placeholder="1.30" style="padding-left:22px;">
                    </div>
                </div>
            </div>

            {{-- P&L preview --}}
            <div id="pnlPreview" style="display:none; margin-top:16px; padding:14px 16px; background:var(--bg-primary); border-radius:8px; border:1px solid var(--border);">
                <div style="display:flex; gap:28px; font-size:13px; align-items:center; flex-wrap:wrap;">
                    <div class="text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase;">Preview P&L</div>
                    <div>Bruto: <span id="pGross" style="font-weight:700;"></span></div>
                    <div>Neto: <span id="pNet" style="font-weight:700; font-size:16px;"></span></div>
                    <div>%: <span id="pPct" style="font-weight:700;"></span></div>
                </div>
            </div>
        </div>

        {{-- Estrategia --}}
        <div class="card" style="padding:24px; margin-bottom:12px;">
            <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border);">
                Estrategia y Revisión
            </div>
            <div style="display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:16px;">
                <div>
                    <label>Estrategia</label>
                    <input type="text" name="strategy" class="form-input"
                           value="{{ old('strategy', $trade->strategy) }}"
                           placeholder="Breakout, Mean Reversion, 0DTE Scalp...">
                </div>
                <div>
                    <label>Calidad del Setup</label>
                    <select name="setup_quality" class="form-input">
                        <option value="">— Seleccionar —</option>
                        @foreach(['A+' => 'A+ (Ideal)', 'A' => 'A (Bueno)', 'B' => 'B (Aceptable)', 'C' => 'C (Forzado)'] as $val => $label)
                        <option value="{{ $val }}" {{ old('setup_quality', $trade->setup_quality) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="margin-bottom:16px;">
                <label>Razón de salida</label>
                <textarea name="exit_reason" class="form-input" rows="2"
                          placeholder="¿Por qué saliste? ¿Tocó stop, target, o saliste manualmente?..." style="resize:vertical;">{{ old('exit_reason', $trade->exit_reason) }}</textarea>
            </div>
        </div>

        {{-- Psicología --}}
        <div class="card" style="padding:24px; margin-bottom:12px;">
            <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border);">
                Psicología del Trade
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
                <div>
                    <label>Estado emocional</label>
                    <select name="emotional_state" class="form-input">
                        <option value="">— Seleccionar —</option>
                        @foreach([
                            'tranquilo'    => '😌 Tranquilo',
                            'confiado'     => '💪 Confiado',
                            'neutral'      => '😐 Neutral',
                            'ansioso'      => '😰 Ansioso',
                            'impaciente'   => '⚡ Impaciente',
                            'miedoso'      => '😨 Miedoso',
                            'fomo'         => '🚀 FOMO',
                            'sobreconfiado'=> '😤 Sobreconfiado',
                        ] as $val => $label)
                        <option value="{{ $val }}" {{ old('emotional_state', $trade->emotional_state) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:flex; align-items:center; padding-top:18px;">
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer; text-transform:none; font-size:13px; margin-bottom:0; letter-spacing:0;">
                        <input type="hidden" name="followed_plan" value="0">
                        <input type="checkbox" name="followed_plan" value="1"
                               {{ old('followed_plan', $trade->followed_plan) ? 'checked' : '' }}
                               style="width:16px; height:16px; cursor:pointer; accent-color:var(--green);">
                        <span style="color:var(--text-primary);">Seguí mi plan de trading</span>
                    </label>
                </div>
            </div>
            <div>
                <label>Errores cometidos</label>
                <textarea name="mistakes_made" class="form-input" rows="3"
                          placeholder="¿Entraste tarde? ¿Moviste el stop? ¿Saliste antes de tiempo? ¿Trading de venganza?..." style="resize:vertical;">{{ old('mistakes_made', $trade->mistakes_made) }}</textarea>
            </div>
        </div>

        <div style="display:flex; gap:12px;">
            <button type="submit" class="btn-primary" style="padding:10px 28px; font-size:14px;">Guardar</button>
            <a href="{{ route('trades.index') }}" class="btn-ghost" style="text-decoration:none; padding:10px 20px; font-size:14px;">Cancelar</a>
            <form method="POST" action="{{ route('trades.destroy', $trade) }}" style="margin-left:auto;"
                  onsubmit="return confirm('¿Eliminar esta operación?')">
                @csrf @method('DELETE')
                <button type="submit" style="background:none; border:1px solid rgba(239,83,80,0.4); color:var(--red); padding:10px 16px; border-radius:6px; font-size:13px; cursor:pointer;">Eliminar</button>
            </form>
        </div>
    </form>
</div>

<script>
const exitPriceEl = document.getElementById('exitPrice');
const entryPrice  = {{ (float)$trade->entry_price }};
const qty         = {{ (int)$trade->quantity }};

function updatePnl() {
    const exit = parseFloat(exitPriceEl.value);
    const comm = parseFloat(document.querySelector('[name=commission]').value) || 0;
    if (!exit) { document.getElementById('pnlPreview').style.display = 'none'; return; }
    const gross = (exit - entryPrice) * qty;
    const net   = gross - comm;
    const pct   = entryPrice > 0 ? ((exit - entryPrice) / entryPrice) * 100 : 0;
    const color = net >= 0 ? '#26a69a' : '#ef5350';
    const sign  = v => (v >= 0 ? '+' : '') + '$' + Math.abs(v).toFixed(2);
    document.getElementById('pGross').textContent = sign(gross);
    document.getElementById('pGross').style.color = color;
    document.getElementById('pNet').textContent   = sign(net);
    document.getElementById('pNet').style.color   = color;
    document.getElementById('pPct').textContent   = (pct >= 0 ? '+' : '') + pct.toFixed(2) + '%';
    document.getElementById('pPct').style.color   = color;
    document.getElementById('pnlPreview').style.display = 'block';
}

exitPriceEl.addEventListener('input', updatePnl);
document.querySelector('[name=commission]').addEventListener('input', updatePnl);
updatePnl();
</script>
@endsection
