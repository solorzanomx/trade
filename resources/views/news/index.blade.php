@extends('layouts.app')
@section('title', 'Noticias - TradeLog')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
    <div>
        <h1>Noticias del Mercado</h1>
        <div class="text-muted" style="font-size:13px; margin-top:2px;">Resúmenes generados por IA — Perplexity + Claude</div>
    </div>
</div>

{{-- Generar nueva noticia --}}
<div class="card" style="padding:20px; margin-bottom:20px;">
    <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:16px;">
        Generar Resumen con IA
    </div>
    @if($errors->has('generate'))
        <div class="alert-error" style="margin-bottom:12px;">{{ $errors->first('generate') }}</div>
    @endif
    <form method="POST" action="{{ route('news.generate') }}" style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end;">
        @csrf
        <div style="flex:1; min-width:120px;">
            <label>Símbolo</label>
            <input type="text" name="symbol" class="form-input" placeholder="QQQ, AAPL, SPY..."
                   value="{{ old('symbol') }}" style="text-transform:uppercase;" required
                   list="symbolSuggestions">
            <datalist id="symbolSuggestions">
                @foreach($tradedSymbols as $sym)
                <option value="{{ $sym }}">
                @endforeach
            </datalist>
        </div>
        <div style="flex:1; min-width:140px;">
            <label>Fecha</label>
            <input type="date" name="date" class="form-input"
                   value="{{ old('date', now()->subDay()->format('Y-m-d')) }}" required>
        </div>
        <div>
            <button type="submit" class="btn-primary" style="padding:8px 24px;">
                ✨ Generar
            </button>
        </div>
    </form>
    @if(!config('services.perplexity.api_key') && !config('services.anthropic.api_key'))
    <div style="margin-top:12px; padding:10px 14px; background:rgba(249,168,37,0.08); border:1px solid rgba(249,168,37,0.3); border-radius:6px; font-size:12px; color:#f9a825;">
        ⚠️ Sin API keys. Agrega <code>PERPLEXITY_API_KEY</code> o <code>ANTHROPIC_API_KEY</code> al .env del servidor.
    </div>
    @endif
</div>

{{-- Filtros --}}
<div class="card" style="padding:14px 20px; margin-bottom:16px;">
    <form method="GET" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
        <select name="asset_id" class="form-input" style="width:auto; min-width:140px;">
            <option value="">Todos los activos</option>
            @foreach($assets as $asset)
            <option value="{{ $asset->id }}" {{ request('asset_id') == $asset->id ? 'selected' : '' }}>
                {{ $asset->symbol }}
            </option>
            @endforeach
        </select>
        <input type="date" name="date_from" class="form-input" style="width:auto;" value="{{ request('date_from') }}">
        <span class="text-muted">→</span>
        <input type="date" name="date_to" class="form-input" style="width:auto;" value="{{ request('date_to') }}">
        <button type="submit" class="btn-ghost" style="padding:8px 16px;">Filtrar</button>
        @if(request()->hasAny(['asset_id','date_from','date_to']))
        <a href="{{ route('news.index') }}" class="text-muted" style="font-size:13px; text-decoration:none;">✕ Limpiar</a>
        @endif
        <span class="text-muted" style="font-size:12px; margin-left:auto;">{{ $summaries->total() }} resúmenes</span>
    </form>
</div>

{{-- Lista --}}
@forelse($summaries as $summary)
<div class="card" style="padding:20px; margin-bottom:10px;">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px; flex-wrap:wrap; gap:8px;">
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <span style="font-size:17px; font-weight:800; color:#fff;">{{ $summary->asset->symbol }}</span>
            <span class="text-muted" style="font-size:12px;">{{ $summary->date->format('d M Y') }}</span>
            <span style="font-size:11px; padding:2px 8px; border-radius:4px; font-weight:600;
                {{ $summary->source === 'perplexity'
                    ? 'background:rgba(41,98,255,0.15); color:#5b8af5;'
                    : ($summary->source === 'claude'
                        ? 'background:rgba(168,85,247,0.15); color:#a855f7;'
                        : 'background:rgba(42,46,57,0.8); color:var(--text-muted);') }}">
                {{ ucfirst($summary->source) }}
            </span>
            <span style="font-size:11px; padding:2px 10px; border-radius:12px; font-weight:600;
                {{ $summary->sentiment === 'positive'
                    ? 'background:rgba(38,166,154,0.15); color:var(--green);'
                    : ($summary->sentiment === 'negative'
                        ? 'background:rgba(239,83,80,0.15); color:var(--red);'
                        : 'background:rgba(42,46,57,0.8); color:var(--text-muted);') }}">
                {{ $summary->sentiment === 'positive' ? '▲ Positivo' : ($summary->sentiment === 'negative' ? '▼ Negativo' : '● Neutral') }}
            </span>
        </div>
        <form method="POST" action="{{ route('news.destroy', $summary->id) }}"
              onsubmit="return confirm('¿Eliminar?')">
            @csrf @method('DELETE')
            <button type="submit" style="background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:18px; line-height:1; padding:0;"
                    title="Eliminar">×</button>
        </form>
    </div>

    <p style="font-size:13px; color:var(--text-primary); line-height:1.65; margin-bottom:{{ $summary->key_points && count($summary->key_points) ? '12px' : '0' }};">
        {{ $summary->summary }}
    </p>

    @if($summary->key_points && count($summary->key_points) > 0)
    <div style="padding:10px 14px; background:var(--bg-primary); border-radius:6px; border:1px solid var(--border);">
        <div class="text-muted" style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:6px;">Puntos Clave</div>
        @foreach($summary->key_points as $point)
        <div style="font-size:12px; color:var(--text-primary); margin-bottom:3px; display:flex; gap:8px;">
            <span style="color:#5b8af5; flex-shrink:0;">›</span>
            <span>{{ $point }}</span>
        </div>
        @endforeach
    </div>
    @endif
</div>
@empty
<div class="card" style="padding:48px; text-align:center;">
    <div style="font-size:40px; margin-bottom:12px;">📰</div>
    <div style="color:#fff; font-size:16px; margin-bottom:8px;">No hay resúmenes todavía</div>
    <div class="text-muted" style="font-size:13px;">Genera el primero con el formulario de arriba</div>
</div>
@endforelse

@if($summaries->hasPages())
<div style="margin-top:16px;">{{ $summaries->links() }}</div>
@endif
@endsection
