@extends('layouts.app')
@section('title', 'Nueva Operación - TradeLog')

@section('content')
<div style="max-width:760px;">
    <div style="display:flex; align-items:center; gap:12px; margin-bottom:24px;">
        <a href="{{ route('trades.index') }}" style="color:var(--text-muted); text-decoration:none; font-size:13px;">← Operaciones</a>
        <span class="text-muted">/</span>
        <h1>Nueva Operación</h1>
    </div>

    <form action="{{ route('trades.store') }}" method="POST">
        @csrf

        {{-- Identificación --}}
        <div class="card" style="padding:24px; margin-bottom:12px;">
            <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border);">
                Identificación
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
                <div>
                    <label>Símbolo *</label>
                    <input type="text" name="symbol" class="form-input" value="{{ old('symbol') }}"
                           placeholder="QQQ" style="text-transform:uppercase; font-weight:700; font-size:16px;" required>
                </div>
                <div>
                    <label>Tipo *</label>
                    <select name="trade_type" class="form-input" required>
                        <option value="call" {{ old('trade_type') === 'call' ? 'selected' : '' }}>Call (opción)</option>
                        <option value="put"  {{ old('trade_type') === 'put'  ? 'selected' : '' }}>Put (opción)</option>
                        <option value="stock"{{ old('trade_type') === 'stock'? 'selected' : '' }}>Stock / ETF</option>
                    </select>
                </div>
                <div>
                    <label>Dirección *</label>
                    <select name="position_direction" class="form-input" required>
                        <option value="long"  {{ old('position_direction') === 'long'  ? 'selected' : '' }}>Long (compra)</option>
                        <option value="short" {{ old('position_direction') === 'short' ? 'selected' : '' }}>Short (venta)</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Entry --}}
        <div class="card" style="padding:24px; margin-bottom:12px;">
            <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border);">
                Entrada
            </div>
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:16px;">
                <div>
                    <label>Precio entrada *</label>
                    <div style="position:relative;">
                        <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--text-muted);">$</span>
                        <input type="number" name="entry_price" class="form-input" value="{{ old('entry_price') }}"
                               step="0.0001" min="0.01" placeholder="5.20" style="padding-left:22px;" required>
                    </div>
                </div>
                <div>
                    <label>Contratos / Qty *</label>
                    <input type="number" name="quantity" class="form-input" value="{{ old('quantity') }}"
                           min="1" placeholder="10" required>
                </div>
                <div>
                    <label>Fecha entrada *</label>
                    <input type="date" name="entry_date" class="form-input" value="{{ old('entry_date', now()->format('Y-m-d')) }}" required>
                </div>
                <div>
                    <label>Hora entrada</label>
                    <input type="time" name="entry_time" class="form-input" value="{{ old('entry_time') }}" step="60">
                </div>
            </div>
            <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:16px;">
                <div>
                    <label>Capital usado *</label>
                    <div style="position:relative;">
                        <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--text-muted);">$</span>
                        <input type="number" name="capital_used" class="form-input" value="{{ old('capital_used') }}"
                               step="0.01" min="0.01" placeholder="520.00" style="padding-left:22px;" required>
                    </div>
                </div>
                <div>
                    <label>Stop Loss</label>
                    <div style="position:relative;">
                        <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--text-muted);">$</span>
                        <input type="number" name="stop_loss" class="form-input" value="{{ old('stop_loss') }}"
                               step="0.0001" min="0" placeholder="3.80" style="padding-left:22px;">
                    </div>
                </div>
                <div>
                    <label>Take Profit</label>
                    <div style="position:relative;">
                        <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--text-muted);">$</span>
                        <input type="number" name="take_profit" class="form-input" value="{{ old('take_profit') }}"
                               step="0.0001" min="0" placeholder="8.00" style="padding-left:22px;">
                    </div>
                </div>
                <div>
                    <label>Comisión</label>
                    <div style="position:relative;">
                        <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--text-muted);">$</span>
                        <input type="number" name="commission" class="form-input" value="{{ old('commission', '0') }}"
                               step="0.01" min="0" placeholder="1.30" style="padding-left:22px;">
                    </div>
                </div>
            </div>
        </div>

        {{-- Estrategia --}}
        <div class="card" style="padding:24px; margin-bottom:12px;">
            <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border);">
                Estrategia y Setup
            </div>
            <div style="display:grid; grid-template-columns:2fr 1fr; gap:16px; margin-bottom:16px;">
                <div>
                    <label>Estrategia</label>
                    <input type="text" name="strategy" class="form-input" value="{{ old('strategy') }}"
                           placeholder="Breakout, Mean Reversion, 0DTE Scalp, Earnings Play...">
                </div>
                <div>
                    <label>Calidad del Setup</label>
                    <select name="setup_quality" class="form-input">
                        <option value="">— Seleccionar —</option>
                        <option value="A+" {{ old('setup_quality') === 'A+' ? 'selected' : '' }}>A+ (Ideal)</option>
                        <option value="A"  {{ old('setup_quality') === 'A'  ? 'selected' : '' }}>A  (Bueno)</option>
                        <option value="B"  {{ old('setup_quality') === 'B'  ? 'selected' : '' }}>B  (Aceptable)</option>
                        <option value="C"  {{ old('setup_quality') === 'C'  ? 'selected' : '' }}>C  (Forzado)</option>
                    </select>
                </div>
            </div>
            <div>
                <label>Razón de entrada</label>
                <textarea name="entry_reason" class="form-input" rows="3"
                          placeholder="¿Qué viste en el chart? ¿Por qué entras en este momento?..." style="resize:vertical;">{{ old('entry_reason') }}</textarea>
            </div>
        </div>

        {{-- Preview R:R --}}
        <div id="rrPreview" class="card" style="padding:14px 20px; margin-bottom:20px; display:none; border-color:rgba(41,98,255,0.3);">
            <div style="display:flex; gap:32px; font-size:13px; align-items:center;">
                <div class="text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase;">R:R Planeado</div>
                <div>Riesgo: <span id="rrisk" style="color:var(--red); font-weight:700;"></span></div>
                <div>Reward: <span id="rreward" style="color:var(--green); font-weight:700;"></span></div>
                <div>Ratio: <span id="rratio" style="font-weight:800; font-size:15px;"></span></div>
            </div>
        </div>

        <div style="display:flex; gap:12px;">
            <button type="submit" class="btn-primary" style="padding:10px 28px; font-size:14px;">Registrar Operación</button>
            <a href="{{ route('trades.index') }}" class="btn-ghost" style="text-decoration:none; padding:10px 20px; font-size:14px;">Cancelar</a>
        </div>
    </form>
</div>

<script>
const ep  = document.querySelector('[name=entry_price]');
const sl  = document.querySelector('[name=stop_loss]');
const tp  = document.querySelector('[name=take_profit]');
const prev= document.getElementById('rrPreview');

function fmt(n) { return '$' + Math.abs(n).toFixed(2); }

function updateRR() {
    const entry = parseFloat(ep.value);
    const stop  = parseFloat(sl.value);
    const target= parseFloat(tp.value);
    if (!entry || !stop || !target) { prev.style.display = 'none'; return; }
    const risk   = Math.abs(entry - stop);
    const reward = Math.abs(target - entry);
    if (risk === 0) { prev.style.display = 'none'; return; }
    const ratio  = reward / risk;
    document.getElementById('rrisk').textContent   = fmt(risk);
    document.getElementById('rreward').textContent = fmt(reward);
    document.getElementById('rratio').textContent  = ratio.toFixed(2) + ':1';
    document.getElementById('rratio').style.color  = ratio >= 2 ? '#26a69a' : ratio >= 1 ? '#f59e0b' : '#ef5350';
    prev.style.display = 'block';
}

[ep, sl, tp].forEach(el => el.addEventListener('input', updateRR));
updateRR();
</script>
@endsection
