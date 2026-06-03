@extends('layouts.app')
@section('title', 'Plan de Trading')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
    <div>
        <h1>📋 Plan de Trading</h1>
        <div class="text-muted" style="font-size:13px; margin-top:2px;">Tus reglas, límites de riesgo y checklist pre-trade</div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 320px; gap:16px; align-items:start;">

{{-- FORMULARIO --}}
<div>
<form method="POST" action="{{ route('plan.save') }}">
@csrf

{{-- LÍMITES DE RIESGO --}}
<div class="card" style="padding:24px; margin-bottom:12px;">
    <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border);">
        🛡️ Límites de Riesgo
    </div>
    <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:16px;">
        <div>
            <label>Pérdida máxima diaria</label>
            <div style="position:relative;">
                <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--red);">$</span>
                <input type="number" name="max_daily_loss" class="form-input" style="padding-left:22px; border-color:rgba(239,83,80,0.3);"
                       value="{{ old('max_daily_loss', $plan->max_daily_loss ?? 500) }}" step="0.01" min="0" required>
            </div>
            <div class="text-muted" style="font-size:11px; margin-top:4px;">El sistema te avisará cuando estés al 80%</div>
        </div>
        <div>
            <label>Pérdida máxima semanal</label>
            <div style="position:relative;">
                <span style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--red);">$</span>
                <input type="number" name="max_weekly_loss" class="form-input" style="padding-left:22px; border-color:rgba(239,83,80,0.3);"
                       value="{{ old('max_weekly_loss', $plan->max_weekly_loss ?? 1500) }}" step="0.01" min="0" required>
            </div>
        </div>
        <div>
            <label>Tamaño máximo de posición (%)</label>
            <div style="position:relative;">
                <input type="number" name="max_position_size_pct" class="form-input" style="padding-right:22px;"
                       value="{{ old('max_position_size_pct', $plan->max_position_size_pct ?? 2) }}" step="0.1" min="0" max="100" required>
                <span style="position:absolute; right:10px; top:50%; transform:translateY(-50%); color:var(--text-muted);">%</span>
            </div>
            <div class="text-muted" style="font-size:11px; margin-top:4px;">% del capital por operación</div>
        </div>
        <div>
            <label>Máximo de operaciones por día</label>
            <input type="number" name="max_trades_per_day" class="form-input"
                   value="{{ old('max_trades_per_day', $plan->max_trades_per_day ?? 3) }}" min="1" max="50" required>
        </div>
    </div>
</div>

{{-- HORARIO Y CONDICIONES --}}
<div class="card" style="padding:24px; margin-bottom:12px;">
    <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border);">
        ⏰ Cuándo Tradear
    </div>
    <div style="margin-bottom:16px;">
        <label>Horario de trading permitido</label>
        <textarea name="trading_schedule" class="form-input" rows="3" style="resize:vertical;"
                  placeholder="Ej: Solo entre 8:30-10:30 CT y 14:00-15:00 CT (apertura y power hour)&#10;Nunca entrar en los últimos 5 minutos del día">{{ old('trading_schedule', $plan->trading_schedule) }}</textarea>
    </div>
    <div>
        <label>Condiciones de mercado permitidas</label>
        <textarea name="market_conditions_allowed" class="form-input" rows="3" style="resize:vertical;"
                  placeholder="Ej: Solo en días con VIX entre 15-25&#10;No tradear días de datos macro (CPI, NFP) hasta 30 min después de la noticia&#10;Evitar días laterales sin tendencia clara">{{ old('market_conditions_allowed', $plan->market_conditions_allowed) }}</textarea>
    </div>
</div>

{{-- CHECKLIST PRE-TRADE --}}
<div class="card" style="padding:24px; margin-bottom:12px;">
    <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:8px;">
        ✅ Checklist Pre-Trade
    </div>
    <div class="text-muted" style="font-size:12px; margin-bottom:16px;">
        Una línea por ítem — este checklist aparecerá en tu diario diario antes de abrir operaciones
    </div>
    <textarea name="checklist_raw" class="form-input" rows="8" style="resize:vertical; font-family:monospace; font-size:13px;"
              placeholder="¿Revisé el reporte QQQ de hoy?&#10;¿Identifiqué los soportes y resistencias clave?&#10;¿Hay eventos macro que puedan mover el mercado hoy?&#10;¿Tengo definido mi stop loss antes de entrar?&#10;¿Estoy en buen estado emocional para tradear?&#10;¿Mi posición no supera el 2% del capital?&#10;¿Ya alcancé mi límite de pérdidas del día?">{{ old('checklist_raw', $plan->pre_trade_checklist ? implode("\n", $plan->pre_trade_checklist) : '') }}</textarea>
</div>

{{-- REGLAS --}}
<div class="card" style="padding:24px; margin-bottom:20px;">
    <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:8px;">
        📜 Reglas de Trading
    </div>
    <div class="text-muted" style="font-size:12px; margin-bottom:16px;">
        Tus principios personales — escríbelos en markdown si quieres (soporta **negrita**, listas, etc.)
    </div>
    <textarea name="rules" class="form-input" rows="12" style="resize:vertical; font-family:monospace; font-size:13px; line-height:1.7;"
              placeholder="## Reglas de Entrada&#10;- Solo entro si el setup es A o A+&#10;- Espero confirmación (no entro al primer impulso)&#10;&#10;## Reglas de Salida&#10;- Si el precio llega al stop, salgo sin dudar&#10;- Si gano 2R, muevo el stop a breakeven&#10;&#10;## Reglas Psicológicas&#10;- Después de 2 pérdidas seguidas, dejo de tradear&#10;- No tradeo en FOMO ni en revenge trading">{{ old('rules', $plan->rules) }}</textarea>
</div>

<button type="submit" class="btn-primary" style="padding:12px 32px; font-size:14px;">💾 Guardar Plan de Trading</button>
</form>
</div>

{{-- SIDEBAR: INSIGHTS --}}
<div style="position:sticky; top:24px;">

    {{-- Insights de IA --}}
    <div class="card" style="overflow:hidden; margin-bottom:12px;">
        <div style="padding:14px 16px; border-bottom:1px solid var(--border);">
            <div style="font-size:13px; font-weight:700; color:#fff;">🧠 Insights de tu historial</div>
            <div class="text-muted" style="font-size:11px; margin-top:2px;">Basado en tus operaciones reales</div>
        </div>
        <div style="padding:8px 0;">
            @forelse($insights as $insight)
            <div style="padding:10px 16px; border-bottom:1px solid rgba(42,46,57,0.4);">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:8px;">
                    <div>
                        <div style="font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:.06em; margin-bottom:2px;">
                            {{ $insight['icon'] }} {{ $insight['label'] ?? '' }}
                        </div>
                        <div style="font-size:12px; color:var(--text-primary); line-height:1.4;">
                            {{ $insight['text'] }}
                        </div>
                    </div>
                    @if(isset($insight['value']))
                    <div style="font-size:16px; font-weight:800; color:{{ $insight['color'] ?? '#fff' }}; flex-shrink:0;">
                        {{ $insight['value'] }}
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div style="padding:20px 16px; text-align:center; color:var(--text-muted); font-size:12px;">
                Necesitas más operaciones para generar insights
            </div>
            @endforelse
        </div>
    </div>

    {{-- Accesos rápidos --}}
    <div class="card" style="padding:16px;">
        <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:12px;">Accesos Rápidos</div>
        <div style="display:flex; flex-direction:column; gap:6px;">
            <a href="{{ route('journal.day', now()->toDateString()) }}" class="btn-ghost" style="text-decoration:none; text-align:center; font-size:13px; padding:7px;">📓 Diario de Hoy</a>
            <a href="{{ route('metrics.index') }}" class="btn-ghost" style="text-decoration:none; text-align:center; font-size:13px; padding:7px;">📊 Ver Métricas</a>
            <a href="{{ route('reports.index') }}" class="btn-ghost" style="text-decoration:none; text-align:center; font-size:13px; padding:7px;">📋 Reporte QQQ</a>
        </div>
    </div>
</div>

</div>
@endsection
