@extends('layouts.app')
@section('title', $position ? 'Editar Posición - TradeLog' : 'Nueva Posición - TradeLog')

@section('content')
<div style="max-width:720px;">
    <div style="display:flex; align-items:center; gap:12px; margin-bottom:24px;">
        <a href="{{ route('portfolio.index') }}" style="color:var(--text-muted); text-decoration:none; font-size:13px;">← Portafolio</a>
        <span class="text-muted">/</span>
        <h1>{{ $position ? 'Editar ' . $position->symbol : 'Nueva Posición' }}</h1>
    </div>

    <form method="POST" action="{{ $position ? route('portfolio.update', $position) : route('portfolio.store') }}">
        @csrf
        @if($position) @method('PUT') @endif

        <div class="card" style="padding:24px; margin-bottom:16px;">
            <div style="font-size:13px; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:.06em; margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid var(--border);">
                Identificación
            </div>
            <div style="display:grid; grid-template-columns:1fr 2fr 1fr; gap:16px; margin-bottom:16px;">
                <div>
                    <label>Ticker / Símbolo *</label>
                    <input type="text" name="symbol" class="form-input" value="{{ old('symbol', $position?->symbol) }}"
                           placeholder="AAPL" style="text-transform:uppercase; font-weight:700; font-size:15px;" required>
                </div>
                <div>
                    <label>Nombre de la empresa</label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $position?->name) }}" placeholder="Apple Inc.">
                </div>
                <div>
                    <label>Tipo de activo *</label>
                    <select name="asset_type" class="form-input" required>
                        @foreach(['stock' => 'Acción', 'etf' => 'ETF', 'crypto' => 'Crypto', 'bond' => 'Bono', 'reit' => 'REIT'] as $val => $label)
                        <option value="{{ $val }}" {{ old('asset_type', $position?->asset_type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div>
                    <label>Sector</label>
                    <input type="text" name="sector" class="form-input" value="{{ old('sector', $position?->sector) }}"
                           placeholder="Tecnología, Salud, Energía...">
                </div>
                <div>
                    <label>Fecha de entrada *</label>
                    <input type="date" name="entry_date" class="form-input" value="{{ old('entry_date', $position?->entry_date?->format('Y-m-d')) }}" required>
                </div>
            </div>
        </div>

        <div class="card" style="padding:24px; margin-bottom:16px;">
            <div style="font-size:13px; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:.06em; margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid var(--border);">
                Posición y Precio
            </div>
            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:16px;">
                <div>
                    <label>Acciones / Unidades *</label>
                    <input type="number" name="shares" class="form-input" value="{{ old('shares', $position?->shares) }}"
                           step="0.000001" min="0" placeholder="100" required>
                </div>
                <div>
                    <label>Costo promedio *</label>
                    <div style="position:relative;">
                        <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--text-muted);">$</span>
                        <input type="number" name="avg_cost" class="form-input" value="{{ old('avg_cost', $position?->avg_cost) }}"
                               step="0.0001" min="0" placeholder="150.00" style="padding-left:22px;" required>
                    </div>
                </div>
                <div>
                    <label>Precio actual</label>
                    <div style="position:relative;">
                        <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--text-muted);">$</span>
                        <input type="number" name="current_price" class="form-input" value="{{ old('current_price', $position?->current_price) }}"
                               step="0.0001" min="0" placeholder="175.00" style="padding-left:22px;">
                    </div>
                </div>
            </div>
            <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px;">
                <div>
                    <label>Precio objetivo</label>
                    <div style="position:relative;">
                        <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--text-muted);">$</span>
                        <input type="number" name="target_price" class="form-input" value="{{ old('target_price', $position?->target_price) }}"
                               step="0.0001" min="0" placeholder="200.00" style="padding-left:22px;">
                    </div>
                </div>
                <div>
                    <label>Stop Loss</label>
                    <div style="position:relative;">
                        <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--text-muted);">$</span>
                        <input type="number" name="stop_loss" class="form-input" value="{{ old('stop_loss', $position?->stop_loss) }}"
                               step="0.0001" min="0" placeholder="130.00" style="padding-left:22px;">
                    </div>
                </div>
                <div>
                    <label>Dividendos recibidos</label>
                    <div style="position:relative;">
                        <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--text-muted);">$</span>
                        <input type="number" name="dividends_received" class="form-input" value="{{ old('dividends_received', $position?->dividends_received ?? 0) }}"
                               step="0.01" min="0" placeholder="0.00" style="padding-left:22px;">
                    </div>
                </div>
            </div>
        </div>

        @if($position)
        <div class="card" style="padding:24px; margin-bottom:16px;">
            <div style="font-size:13px; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:.06em; margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid var(--border);">
                Estado de la Posición
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px;">
                <div>
                    <label>Estado</label>
                    <select name="status" class="form-input" id="statusSelect">
                        <option value="open" {{ old('status', $position->status) === 'open' ? 'selected' : '' }}>Abierta</option>
                        <option value="closed" {{ old('status', $position->status) === 'closed' ? 'selected' : '' }}>Cerrada</option>
                    </select>
                </div>
                <div id="exitPriceWrap" style="{{ old('status', $position->status) !== 'closed' ? 'opacity:0.4; pointer-events:none;' : '' }}">
                    <label>Precio de salida</label>
                    <div style="position:relative;">
                        <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--text-muted);">$</span>
                        <input type="number" name="exit_price" class="form-input" value="{{ old('exit_price', $position?->exit_price) }}"
                               step="0.0001" min="0" style="padding-left:22px;">
                    </div>
                </div>
                <div id="exitDateWrap" style="{{ old('status', $position->status) !== 'closed' ? 'opacity:0.4; pointer-events:none;' : '' }}">
                    <label>Fecha de salida</label>
                    <input type="date" name="exit_date" class="form-input" value="{{ old('exit_date', $position?->exit_date?->format('Y-m-d')) }}">
                </div>
            </div>
            <script>
            document.getElementById('statusSelect').addEventListener('change', function() {
                const isClosed = this.value === 'closed';
                ['exitPriceWrap','exitDateWrap'].forEach(id => {
                    const el = document.getElementById(id);
                    el.style.opacity = isClosed ? '1' : '0.4';
                    el.style.pointerEvents = isClosed ? 'auto' : 'none';
                });
            });
            </script>
        </div>
        @endif

        <div class="card" style="padding:24px; margin-bottom:24px;">
            <div style="font-size:13px; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:.06em; margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid var(--border);">
                Tesis e Ideas
            </div>
            <div style="margin-bottom:16px;">
                <label>Tesis de inversión</label>
                <textarea name="thesis" class="form-input" rows="3" placeholder="¿Por qué compraste esta posición? ¿Cuál es tu tesis de largo plazo?..." style="resize:vertical;">{{ old('thesis', $position?->thesis) }}</textarea>
            </div>
            <div>
                <label>Notas adicionales</label>
                <textarea name="notes" class="form-input" rows="2" placeholder="Catalizadores, fechas clave, earnings..." style="resize:vertical;">{{ old('notes', $position?->notes) }}</textarea>
            </div>
        </div>

        {{-- Preview P&L en tiempo real --}}
        <div id="preview" class="card" style="padding:16px 20px; margin-bottom:24px; display:none; border-color:rgba(41,98,255,0.3);">
            <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:var(--text-muted); margin-bottom:8px;">Preview</div>
            <div style="display:flex; gap:32px; font-size:13px;">
                <div>Costo base: <span id="prevCost" style="color:#fff; font-weight:700;"></span></div>
                <div>Valor actual: <span id="prevValue" style="color:#fff; font-weight:700;"></span></div>
                <div>G/P: <span id="prevPnl" style="font-weight:700;"></span></div>
                <div>Retorno: <span id="prevRet" style="font-weight:700;"></span></div>
            </div>
        </div>

        <div style="display:flex; gap:12px;">
            <button type="submit" class="btn-primary" style="padding:10px 28px; font-size:14px;">
                {{ $position ? 'Guardar cambios' : 'Agregar al Portafolio' }}
            </button>
            <a href="{{ route('portfolio.index') }}" class="btn-ghost" style="text-decoration:none; padding:10px 20px; font-size:14px;">Cancelar</a>
        </div>
    </form>
</div>

<script>
const sharesEl  = document.querySelector('[name=shares]');
const costEl    = document.querySelector('[name=avg_cost]');
const priceEl   = document.querySelector('[name=current_price]');
const preview   = document.getElementById('preview');

function fmt(n) { return '$' + Math.abs(n).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2}); }

function updatePreview() {
    const shares = parseFloat(sharesEl.value) || 0;
    const cost   = parseFloat(costEl.value) || 0;
    const price  = parseFloat(priceEl.value) || 0;
    if (!shares || !cost) { preview.style.display = 'none'; return; }
    const basis  = shares * cost;
    const value  = price ? shares * price : basis;
    const pnl    = value - basis;
    const ret    = basis > 0 ? (pnl / basis) * 100 : 0;
    const pnlColor = pnl >= 0 ? '#26a69a' : '#ef5350';
    document.getElementById('prevCost').textContent  = fmt(basis);
    document.getElementById('prevValue').textContent = fmt(value);
    document.getElementById('prevPnl').textContent   = (pnl >= 0 ? '+' : '-') + fmt(pnl);
    document.getElementById('prevPnl').style.color   = pnlColor;
    document.getElementById('prevRet').textContent   = (ret >= 0 ? '+' : '') + ret.toFixed(2) + '%';
    document.getElementById('prevRet').style.color   = pnlColor;
    preview.style.display = 'block';
}

[sharesEl, costEl, priceEl].forEach(el => el.addEventListener('input', updatePreview));
updatePreview();
</script>
@endsection
