@extends('layouts.app')
@section('title', $trade->symbol . ' - TradeLog')

@section('content')
<div style="max-width:760px;">

    {{-- Header --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <div style="display:flex; align-items:center; gap:12px;">
            <a href="{{ route('trades.index') }}" style="color:var(--text-muted); text-decoration:none; font-size:13px;">← Operaciones</a>
            <span class="text-muted">/</span>
            <div>
                <span style="font-size:22px; font-weight:800; color:#fff;">{{ $trade->symbol }}</span>
                <span style="margin-left:10px; font-size:13px; padding:3px 10px; border-radius:4px; font-weight:600; text-transform:uppercase;
                    {{ $trade->trade_type === 'call' ? 'background:rgba(38,166,154,0.15); color:var(--green);'
                    : ($trade->trade_type === 'put' ? 'background:rgba(239,83,80,0.15); color:var(--red);'
                    : 'background:rgba(91,138,245,0.15); color:#5b8af5;') }}">
                    {{ $trade->trade_type }}
                </span>
                <span style="margin-left:6px; font-size:13px; padding:3px 10px; border-radius:4px; font-weight:600;
                    {{ $trade->status === 'open'
                        ? 'background:rgba(249,168,37,0.15); color:#f9a825;'
                        : 'background:rgba(42,46,57,0.8); color:var(--text-secondary);' }}">
                    {{ $trade->status === 'open' ? 'Abierta' : 'Cerrada' }}
                </span>
            </div>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('trades.edit', $trade) }}" class="btn-ghost" style="text-decoration:none; font-size:13px;">Editar</a>
            <form method="POST" action="{{ route('trades.destroy', $trade) }}"
                  onsubmit="return confirm('¿Eliminar {{ $trade->symbol }}?')">
                @csrf @method('DELETE')
                <button type="submit" style="background:none; border:1px solid rgba(239,83,80,0.4); color:var(--red); padding:8px 14px; border-radius:6px; font-size:13px; cursor:pointer;">Eliminar</button>
            </form>
        </div>
    </div>

    {{-- P&L Banner (solo si cerrada) --}}
    @if($trade->status === 'closed' && $trade->p_l !== null)
    <div class="card" style="padding:20px 24px; margin-bottom:12px;
        border-color:{{ $trade->p_l >= 0 ? 'rgba(38,166,154,0.4)' : 'rgba(239,83,80,0.4)' }};
        background:{{ $trade->p_l >= 0 ? 'rgba(38,166,154,0.06)' : 'rgba(239,83,80,0.06)' }};">
        <div style="display:flex; gap:40px; align-items:center; flex-wrap:wrap;">
            <div>
                <div class="text-muted" style="font-size:11px; text-transform:uppercase; letter-spacing:.08em; margin-bottom:4px;">P&L Bruto</div>
                <div style="font-size:32px; font-weight:800; color:{{ $trade->p_l >= 0 ? 'var(--green)' : 'var(--red)' }};">
                    {{ $trade->p_l >= 0 ? '+' : '' }}${{ number_format($trade->p_l, 2) }}
                </div>
            </div>
            <div>
                <div class="text-muted" style="font-size:11px; text-transform:uppercase; letter-spacing:.08em; margin-bottom:4px;">P&L Neto</div>
                <div style="font-size:24px; font-weight:700; color:{{ ($trade->net_p_l ?? $trade->p_l) >= 0 ? 'var(--green)' : 'var(--red)' }};">
                    {{ ($trade->net_p_l ?? $trade->p_l) >= 0 ? '+' : '' }}${{ number_format($trade->net_p_l ?? $trade->p_l, 2) }}
                </div>
            </div>
            @if($trade->p_l_percent !== null)
            <div>
                <div class="text-muted" style="font-size:11px; text-transform:uppercase; letter-spacing:.08em; margin-bottom:4px;">Retorno</div>
                <div style="font-size:24px; font-weight:700; color:{{ $trade->p_l_percent >= 0 ? 'var(--green)' : 'var(--red)' }};">
                    {{ $trade->p_l_percent >= 0 ? '+' : '' }}{{ number_format($trade->p_l_percent, 2) }}%
                </div>
            </div>
            @endif
            @if($trade->commission)
            <div>
                <div class="text-muted" style="font-size:11px; text-transform:uppercase; letter-spacing:.08em; margin-bottom:4px;">Comisión</div>
                <div style="font-size:20px; font-weight:600; color:var(--red);">-${{ number_format($trade->commission, 2) }}</div>
            </div>
            @endif
        </div>
    </div>
    @endif

    {{-- Entrada y Salida --}}
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
        <div class="card" style="padding:20px;">
            <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:14px;">Entrada</div>
            <div style="display:flex; flex-direction:column; gap:10px; font-size:13px;">
                <div style="display:flex; justify-content:space-between;">
                    <span class="text-muted">Precio</span>
                    <span style="color:#fff; font-weight:700; font-size:16px;">${{ number_format($trade->entry_price, 2) }}</span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span class="text-muted">Contratos</span>
                    <span style="color:#fff; font-weight:600;">{{ $trade->quantity }}</span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span class="text-muted">Capital</span>
                    <span style="color:#fff;">${{ number_format($trade->capital_used, 2) }}</span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span class="text-muted">Fecha</span>
                    <span style="color:#fff;">{{ $trade->entry_date->format('d/m/Y') }}{{ $trade->entry_time ? ' · ' . $trade->entry_time : '' }}</span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span class="text-muted">Dirección</span>
                    <span style="color:#fff; text-transform:capitalize; font-weight:600;">{{ $trade->position_direction }}</span>
                </div>
                @if($trade->stop_loss)
                <div style="display:flex; justify-content:space-between; padding-top:8px; border-top:1px solid var(--border);">
                    <span class="text-muted">Stop Loss</span>
                    <span style="color:var(--red); font-weight:600;">${{ number_format($trade->stop_loss, 2) }}</span>
                </div>
                @endif
                @if($trade->take_profit)
                <div style="display:flex; justify-content:space-between;">
                    <span class="text-muted">Take Profit</span>
                    <span style="color:var(--green); font-weight:600;">${{ number_format($trade->take_profit, 2) }}</span>
                </div>
                @endif
                @if($trade->stop_loss && $trade->take_profit)
                <div style="display:flex; justify-content:space-between;">
                    <span class="text-muted">R:R Planeado</span>
                    @php $rr = $trade->getRiskRewardRatio(); @endphp
                    <span style="color:{{ $rr && $rr >= 2 ? 'var(--green)' : ($rr && $rr >= 1 ? '#f59e0b' : 'var(--red)') }}; font-weight:700;">
                        {{ $rr ? number_format($rr, 2) . ':1' : '—' }}
                    </span>
                </div>
                @endif
            </div>
        </div>

        <div class="card" style="padding:20px;">
            <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:14px;">
                Salida @if($trade->status === 'open')<span style="font-size:10px; color:#f9a825; margin-left:6px;">(abierta)</span>@endif
            </div>
            @if($trade->exit_price)
            <div style="display:flex; flex-direction:column; gap:10px; font-size:13px;">
                <div style="display:flex; justify-content:space-between;">
                    <span class="text-muted">Precio</span>
                    <span style="color:#fff; font-weight:700; font-size:16px;">${{ number_format($trade->exit_price, 2) }}</span>
                </div>
                <div style="display:flex; justify-content:space-between;">
                    <span class="text-muted">Fecha</span>
                    <span style="color:#fff;">{{ $trade->exit_date?->format('d/m/Y') }}{{ $trade->exit_time ? ' · ' . $trade->exit_time : '' }}</span>
                </div>
                @if($trade->entry_date && $trade->exit_date)
                <div style="display:flex; justify-content:space-between;">
                    <span class="text-muted">Duración</span>
                    <span style="color:#fff;">{{ $trade->entry_date->diffInDays($trade->exit_date) }} días</span>
                </div>
                @endif
            </div>
            @else
            <div style="padding:24px 0; text-align:center;">
                <div class="text-muted" style="font-size:13px; margin-bottom:12px;">Operación sin cerrar</div>
                <a href="{{ route('trades.edit', $trade) }}" class="btn-green" style="text-decoration:none; font-size:13px; padding:8px 20px;">Cerrar operación</a>
            </div>
            @endif
        </div>
    </div>

    {{-- Estrategia y Psicología --}}
    @if($trade->strategy || $trade->setup_quality || $trade->emotional_state || $trade->followed_plan !== null || $trade->mistakes_made)
    <div class="card" style="padding:20px; margin-bottom:12px;">
        <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:14px;">Estrategia y Psicología</div>
        <div style="display:flex; gap:24px; flex-wrap:wrap; font-size:13px; margin-bottom:{{ $trade->mistakes_made ? '16px' : '0' }};">
            @if($trade->strategy)
            <div><span class="text-muted">Estrategia:</span> <strong style="color:#fff;">{{ $trade->strategy }}</strong></div>
            @endif
            @if($trade->setup_quality)
            <div><span class="text-muted">Setup:</span>
                <strong style="color:{{ in_array($trade->setup_quality, ['A+','A']) ? 'var(--green)' : ($trade->setup_quality === 'B' ? '#f59e0b' : 'var(--red)') }};">
                    {{ $trade->setup_quality }}
                </strong>
            </div>
            @endif
            @if($trade->emotional_state)
            <div><span class="text-muted">Estado:</span> <strong style="color:#fff; text-transform:capitalize;">{{ $trade->emotional_state }}</strong></div>
            @endif
            @if($trade->followed_plan !== null)
            <div>
                <span class="text-muted">Siguió el plan:</span>
                <strong style="color:{{ $trade->followed_plan ? 'var(--green)' : 'var(--red)' }};">
                    {{ $trade->followed_plan ? '✓ Sí' : '✗ No' }}
                </strong>
            </div>
            @endif
        </div>
        @if($trade->mistakes_made)
        <div style="padding:12px 14px; background:rgba(239,83,80,0.06); border-radius:6px; border:1px solid rgba(239,83,80,0.2); font-size:13px; color:var(--text-primary);">
            <span class="text-muted" style="font-size:11px; font-weight:700; text-transform:uppercase; display:block; margin-bottom:4px;">Errores</span>
            {{ $trade->mistakes_made }}
        </div>
        @endif
    </div>
    @endif

    {{-- Razones --}}
    @if($trade->entry_reason || $trade->exit_reason)
    <div class="card" style="padding:20px; margin-bottom:12px;">
        <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:14px;">Notas</div>
        @if($trade->entry_reason)
        <div style="margin-bottom:12px;">
            <div class="text-muted" style="font-size:11px; font-weight:600; margin-bottom:4px;">Razón de entrada</div>
            <div style="font-size:13px; color:var(--text-primary); line-height:1.5;">{{ $trade->entry_reason }}</div>
        </div>
        @endif
        @if($trade->exit_reason)
        <div>
            <div class="text-muted" style="font-size:11px; font-weight:600; margin-bottom:4px;">Razón de salida</div>
            <div style="font-size:13px; color:var(--text-primary); line-height:1.5;">{{ $trade->exit_reason }}</div>
        </div>
        @endif
    </div>
    @endif

</div>
@endsection
