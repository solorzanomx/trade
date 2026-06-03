@extends('layouts.app')
@section('title', 'Diario ' . $date->format('d/m/Y'))

@section('content')
<div style="max-width:860px;">

{{-- NAV DE FECHA --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
    <div style="display:flex; align-items:center; gap:12px;">
        <a href="{{ route('journal.day', $prevDay->toDateString()) }}" style="color:var(--text-muted); text-decoration:none; font-size:20px; padding:4px 8px;">‹</a>
        <div>
            <div style="font-size:20px; font-weight:800; color:#fff;">
                {{ $date->locale('es')->isoFormat('dddd D [de] MMMM') }}
                @if($date->isToday()) <span style="font-size:12px; color:#26a69a; font-weight:600; margin-left:6px;">HOY</span> @endif
            </div>
            <div style="display:flex; gap:8px; margin-top:4px; flex-wrap:wrap;">
                <a href="{{ route('journal.index') }}" style="font-size:12px; color:var(--text-muted); text-decoration:none;">← Todos los días</a>
                @if($date->lt(now()))
                <span class="text-muted">·</span>
                <a href="{{ route('journal.day', $nextDay->toDateString()) }}" style="font-size:12px; color:var(--text-muted); text-decoration:none;">Siguiente →</a>
                @endif
            </div>
        </div>
        <a href="{{ route('journal.day', $prevDay->toDateString()) }}" style="color:var(--text-muted); text-decoration:none; font-size:20px; padding:4px 8px;">›</a>
    </div>

    {{-- P&L del día --}}
    @if($dayPnl != 0)
    <div style="padding:10px 20px; border-radius:8px; border:1px solid {{ $dayPnl >= 0 ? 'rgba(38,166,154,0.3)' : 'rgba(239,83,80,0.3)' }};
                background:{{ $dayPnl >= 0 ? 'rgba(38,166,154,0.06)' : 'rgba(239,83,80,0.06)' }}; text-align:center;">
        <div class="text-muted" style="font-size:10px; text-transform:uppercase; letter-spacing:.08em; margin-bottom:2px;">P&L del día</div>
        <div style="font-size:22px; font-weight:800; color:{{ $dayPnl >= 0 ? 'var(--green)' : 'var(--red)' }};">
            {{ $dayPnl >= 0 ? '+' : '' }}${{ number_format($dayPnl, 2) }}
        </div>
    </div>
    @endif
</div>

<form method="POST" action="{{ route('journal.save', $date->toDateString()) }}">
@csrf

{{-- TABS --}}
<div style="display:flex; gap:0; margin-bottom:16px; border-bottom:1px solid var(--border);">
    <button type="button" onclick="showTab('pre')" id="tabPre"
            style="padding:10px 20px; font-size:13px; font-weight:600; border:none; cursor:pointer; border-bottom:2px solid #5b8af5; color:#fff; background:transparent;">
        🌅 Pre-Market
    </button>
    <button type="button" onclick="showTab('post')" id="tabPost"
            style="padding:10px 20px; font-size:13px; font-weight:600; border:none; cursor:pointer; border-bottom:2px solid transparent; color:var(--text-muted); background:transparent;">
        🌆 Post-Market
    </button>
    <button type="button" onclick="showTab('trades')" id="tabTrades"
            style="padding:10px 20px; font-size:13px; font-weight:600; border:none; cursor:pointer; border-bottom:2px solid transparent; color:var(--text-muted); background:transparent;">
        📊 Operaciones ({{ $dayTrades->count() }})
    </button>
</div>

{{-- ═══ PRE-MARKET ═══ --}}
<div id="panePre">
    <div class="card" style="padding:24px; margin-bottom:12px;">
        <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border);">
            🧭 Bias y Plan del Día
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:16px;">
            <div>
                <label>Bias del día</label>
                <select name="pre_bias" class="form-input">
                    <option value="">— Seleccionar —</option>
                    <option value="bullish"  {{ old('pre_bias', $entry?->pre_bias) === 'bullish'  ? 'selected' : '' }}>🟢 Alcista (Bullish)</option>
                    <option value="neutral"  {{ old('pre_bias', $entry?->pre_bias) === 'neutral'  ? 'selected' : '' }}>🟡 Neutral</option>
                    <option value="bearish"  {{ old('pre_bias', $entry?->pre_bias) === 'bearish'  ? 'selected' : '' }}>🔴 Bajista (Bearish)</option>
                </select>
            </div>
            <div>
                <label>Estado de ánimo (1-10)</label>
                <input type="range" name="mood_start" min="1" max="10"
                       value="{{ old('mood_start', $entry?->mood_start ?? 7) }}"
                       oninput="document.getElementById('moodStartVal').textContent = this.value"
                       style="width:100%; accent-color:#5b8af5; margin-top:8px;">
                <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--text-muted); margin-top:4px;">
                    <span>😞 1</span>
                    <span id="moodStartVal" style="font-size:14px; font-weight:700; color:#fff;">{{ $entry?->mood_start ?? 7 }}</span>
                    <span>🔥 10</span>
                </div>
            </div>
            <div>
                <label>Condiciones del mercado</label>
                <input type="text" name="market_conditions" class="form-input"
                       value="{{ old('market_conditions', $entry?->market_conditions) }}"
                       placeholder="Trending, Range-bound, Alta volatilidad...">
            </div>
        </div>

        <div style="margin-bottom:16px;">
            <label>Plan del día <span class="text-muted" style="font-weight:400; font-size:11px;">¿Qué setups estoy buscando? ¿Qué eventos hay hoy?</span></label>
            <textarea name="pre_plan" class="form-input" rows="4" style="resize:vertical;"
                      placeholder="Hoy busco calls en QQQ si supera los $485. Hay datos de inflación a las 7:30 CT...">{{ old('pre_plan', $entry?->pre_plan) }}</textarea>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div>
                <label>Watchlist <span class="text-muted" style="font-weight:400; font-size:11px;">separados por coma</span></label>
                <input type="text" name="watchlist_raw" class="form-input"
                       value="{{ old('watchlist_raw', $entry ? implode(', ', $entry->watchlist ?? []) : '') }}"
                       placeholder="QQQ, SPY, NVDA, AAPL..." style="text-transform:uppercase;">
            </div>
            <div>
                <label>Metas del día <span class="text-muted" style="font-weight:400; font-size:11px;">1-3 metas específicas</span></label>
                <textarea name="pre_goals" class="form-input" rows="3" style="resize:vertical;"
                          placeholder="1. No perder más de $200&#10;2. Máximo 3 operaciones&#10;3. Salir si QQQ pierde $480">{{ old('pre_goals', $entry?->pre_goals) }}</textarea>
            </div>
        </div>
    </div>

    @if($plan && $plan->pre_trade_checklist)
    <div class="card" style="padding:20px; margin-bottom:12px;">
        <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:14px;">
            ✅ Checklist Pre-Trade (de tu plan de trading)
        </div>
        @foreach($plan->pre_trade_checklist as $i => $item)
        <label style="display:flex; align-items:center; gap:10px; margin-bottom:10px; cursor:pointer; text-transform:none; letter-spacing:0; font-size:13px; color:var(--text-primary); font-weight:400;">
            <input type="checkbox" style="width:16px; height:16px; accent-color:var(--green); flex-shrink:0;">
            {{ $item }}
        </label>
        @endforeach
        <div style="margin-top:12px; font-size:12px; color:var(--text-muted);">
            ⚠️ Este checklist es solo de referencia visual — no se guarda en la entrada.
            <a href="{{ route('plan.index') }}" style="color:#5b8af5;">Editar checklist →</a>
        </div>
    </div>
    @endif

    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:4px;">
        <button type="submit" class="btn-primary" style="padding:10px 28px;">💾 Guardar Pre-Market</button>
        <button type="button" onclick="showTab('post')" class="btn-ghost" style="font-size:13px;">
            Ir a Post-Market →
        </button>
    </div>
</div>

{{-- ═══ POST-MARKET ═══ --}}
<div id="panePost" style="display:none;">
    <div class="card" style="padding:24px; margin-bottom:12px;">
        <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border);">
            📊 Revisión del Día
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-bottom:20px;">
            <div>
                <label>Calificación del día</label>
                <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:6px; margin-top:6px;" id="gradeGrid">
                    @foreach(['A+','A','B','C','D','F'] as $g)
                    @php
                        $gradeColors = ['A+'=>'#26a69a','A'=>'#26a69a','B'=>'#5b8af5','C'=>'#f9a825','D'=>'#ef5350','F'=>'#ef5350'];
                        $selected = old('grade', $entry?->grade) === $g;
                    @endphp
                    <label style="cursor:pointer;">
                        <input type="radio" name="grade" value="{{ $g }}" {{ $selected ? 'checked' : '' }} style="display:none;" class="grade-radio">
                        <div class="grade-btn" data-grade="{{ $g }}"
                             style="text-align:center; padding:8px 4px; border-radius:6px; border:2px solid {{ $selected ? $gradeColors[$g] : 'var(--border)' }};
                                    color:{{ $selected ? $gradeColors[$g] : 'var(--text-muted)' }}; font-weight:800; font-size:14px;
                                    background:{{ $selected ? $gradeColors[$g].'22' : 'transparent' }}; transition:all .15s; cursor:pointer;">
                            {{ $g }}
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            <div>
                <label>¿Seguiste tu plan?</label>
                <div style="display:flex; gap:8px; margin-top:8px;">
                    <label style="flex:1; cursor:pointer; text-transform:none; letter-spacing:0; margin:0;">
                        <input type="radio" name="followed_plan" value="1" {{ old('followed_plan', $entry?->followed_plan) == 1 ? 'checked' : '' }} style="display:none;" class="plan-radio">
                        <div style="text-align:center; padding:10px; border-radius:6px; border:2px solid var(--border); font-size:13px; font-weight:600; cursor:pointer;"
                             data-plan="1">✓ Sí</div>
                    </label>
                    <label style="flex:1; cursor:pointer; text-transform:none; letter-spacing:0; margin:0;">
                        <input type="radio" name="followed_plan" value="0" {{ old('followed_plan', $entry?->followed_plan) === false ? 'checked' : '' }} style="display:none;" class="plan-radio">
                        <div style="text-align:center; padding:10px; border-radius:6px; border:2px solid var(--border); font-size:13px; font-weight:600; cursor:pointer;"
                             data-plan="0">✗ No</div>
                    </label>
                </div>
            </div>
            <div>
                <label>Estado de ánimo al cierre (1-10)</label>
                <input type="range" name="mood_end" min="1" max="10"
                       value="{{ old('mood_end', $entry?->mood_end ?? 7) }}"
                       oninput="document.getElementById('moodEndVal').textContent = this.value"
                       style="width:100%; accent-color:#5b8af5; margin-top:8px;">
                <div style="display:flex; justify-content:space-between; font-size:11px; color:var(--text-muted); margin-top:4px;">
                    <span>😞 1</span>
                    <span id="moodEndVal" style="font-size:14px; font-weight:700; color:#fff;">{{ $entry?->mood_end ?? 7 }}</span>
                    <span>🔥 10</span>
                </div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;">
            <div>
                <label style="color:var(--green);">✅ ¿Qué salió bien?</label>
                <textarea name="what_went_well" class="form-input" rows="4" style="resize:vertical; border-color:rgba(38,166,154,0.3);"
                          placeholder="Entré en el momento correcto. Respeté mi stop loss. No sobreoperer...">{{ old('what_went_well', $entry?->what_went_well) }}</textarea>
            </div>
            <div>
                <label style="color:var(--red);">❌ ¿Qué mejorar?</label>
                <textarea name="what_to_improve" class="form-input" rows="4" style="resize:vertical; border-color:rgba(239,83,80,0.3);"
                          placeholder="Salí muy rápido antes de que llegara al target. Entré en FOMO a las 12pm...">{{ old('what_to_improve', $entry?->what_to_improve) }}</textarea>
            </div>
        </div>

        <div style="margin-bottom:16px;">
            <label style="color:#5b8af5;">💡 Lección del día</label>
            <textarea name="lesson_learned" class="form-input" rows="3" style="resize:vertical; border-color:rgba(91,138,245,0.3);"
                      placeholder="La lección más importante de hoy para no olvidar...">{{ old('lesson_learned', $entry?->lesson_learned) }}</textarea>
        </div>

        <div style="margin-bottom:16px;">
            <label>📝 Revisión general del día</label>
            <textarea name="post_review" class="form-input" rows="4" style="resize:vertical;"
                      placeholder="Descripción general de cómo fue el día, el mercado, qué pasó...">{{ old('post_review', $entry?->post_review) }}</textarea>
        </div>

        <div>
            <label>🎯 Metas para mañana</label>
            <textarea name="goals_tomorrow" class="form-input" rows="3" style="resize:vertical;"
                      placeholder="1. Enfocarse en la apertura&#10;2. No tradear después de las 12pm&#10;3. Revisar el reporte QQQ antes de abrir">{{ old('goals_tomorrow', $entry?->goals_tomorrow) }}</textarea>
        </div>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:4px;">
        <button type="button" onclick="showTab('pre')" class="btn-ghost" style="font-size:13px;">
            ← Volver a Pre-Market
        </button>
        <button type="submit" class="btn-primary" style="padding:10px 28px;">💾 Guardar Todo</button>
    </div>
</div>

{{-- ═══ OPERACIONES DEL DÍA ═══ --}}
<div id="paneTrades" style="display:none;">
    @if($dayTrades->isEmpty())
    <div class="card" style="padding:40px; text-align:center;">
        <div class="text-muted" style="font-size:14px;">No hay operaciones registradas para este día.</div>
    </div>
    @else
    <div class="card" style="overflow:hidden;">
        <table>
            <thead>
                <tr>
                    <th>Símbolo</th>
                    <th>Tipo</th>
                    <th style="text-align:right;">Entrada</th>
                    <th style="text-align:right;">Salida</th>
                    <th style="text-align:right;">Qty</th>
                    <th style="text-align:right;">P&L</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dayTrades as $trade)
                <tr onclick="window.location='{{ route('trades.show', $trade) }}'" style="cursor:pointer;">
                    <td><strong style="color:#fff;">{{ $trade->symbol }}</strong></td>
                    <td>
                        <span style="font-size:10px; text-transform:uppercase; padding:2px 6px; border-radius:3px;
                            {{ $trade->trade_type === 'call' ? 'background:rgba(38,166,154,0.15); color:var(--green);'
                            : ($trade->trade_type === 'put' ? 'background:rgba(239,83,80,0.15); color:var(--red);'
                            : 'background:rgba(91,138,245,0.15); color:#5b8af5;') }}">
                            {{ $trade->trade_type }}
                        </span>
                    </td>
                    <td style="text-align:right;">${{ number_format($trade->entry_price, 2) }}</td>
                    <td style="text-align:right;">{{ $trade->exit_price ? '$'.number_format($trade->exit_price,2) : '—' }}</td>
                    <td style="text-align:right;">{{ $trade->quantity }}</td>
                    <td style="text-align:right; font-weight:700; {{ ($trade->p_l ?? 0) >= 0 ? 'color:var(--green)' : 'color:var(--red)' }}">
                        {{ $trade->p_l !== null ? (($trade->p_l >= 0 ? '+' : '') . '$' . number_format($trade->p_l, 2)) : '—' }}
                    </td>
                    <td>
                        <span style="font-size:11px; padding:2px 8px; border-radius:4px;
                            {{ $trade->status === 'open' ? 'background:rgba(249,168,37,0.15); color:#f9a825;' : 'background:rgba(42,46,57,0.8); color:var(--text-muted);' }}">
                            {{ $trade->status === 'open' ? 'Abierta' : 'Cerrada' }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
    <div style="margin-top:12px;">
        <button type="submit" class="btn-ghost" style="font-size:13px; padding:8px 20px;">💾 Guardar cambios</button>
    </div>
</div>

</form>
</div>

<script>
function showTab(tab) {
    ['pre','post','trades'].forEach(t => {
        document.getElementById('pane' + t.charAt(0).toUpperCase() + t.slice(1)).style.display = 'none';
        const btn = document.getElementById('tab' + t.charAt(0).toUpperCase() + t.slice(1));
        btn.style.borderBottomColor = 'transparent';
        btn.style.color = 'var(--text-muted)';
    });
    document.getElementById('pane' + tab.charAt(0).toUpperCase() + tab.slice(1)).style.display = 'block';
    const activeBtn = document.getElementById('tab' + tab.charAt(0).toUpperCase() + tab.slice(1));
    activeBtn.style.borderBottomColor = '#5b8af5';
    activeBtn.style.color = '#fff';
}

// Grade selector
document.querySelectorAll('.grade-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const gradeColors = {'A+':'#26a69a','A':'#26a69a','B':'#5b8af5','C':'#f9a825','D':'#ef5350','F':'#ef5350'};
        const grade = btn.dataset.grade;
        document.querySelectorAll('.grade-btn').forEach(b => {
            b.style.borderColor = 'var(--border)';
            b.style.color = 'var(--text-muted)';
            b.style.background = 'transparent';
        });
        btn.style.borderColor = gradeColors[grade];
        btn.style.color = gradeColors[grade];
        btn.style.background = gradeColors[grade] + '22';
        btn.previousElementSibling.checked = true;
    });
});

// Plan radios
document.querySelectorAll('.plan-radio').forEach(radio => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('[data-plan]').forEach(el => {
            el.style.borderColor = 'var(--border)';
            el.style.color = 'var(--text-muted)';
            el.style.background = 'transparent';
        });
        const div = radio.nextElementSibling;
        const isPlan = radio.value === '1';
        div.style.borderColor = isPlan ? 'var(--green)' : 'var(--red)';
        div.style.color = isPlan ? 'var(--green)' : 'var(--red)';
        div.style.background = isPlan ? 'rgba(38,166,154,0.08)' : 'rgba(239,83,80,0.08)';
    });
});

// Inicializar estado de plan radios
document.querySelectorAll('.plan-radio:checked').forEach(radio => radio.dispatchEvent(new Event('change')));

// Abrir tab post si ya hay entrada
@if($entry?->grade || $entry?->what_went_well)
showTab('post');
@endif
</script>
@endsection
