@extends('layouts.app')
@section('title', 'Cuenta - TradeLog')

@section('content')

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <h1 style="margin:0;">Gestión de Capital</h1>
        <div class="text-muted" style="font-size:13px; margin-top:4px;">Depósitos, retiros y saldo real de tu cuenta IBKR</div>
    </div>
</div>

@if(session('success'))
    <div style="background:rgba(46,213,115,.12); border:1px solid var(--green); color:var(--green); padding:10px 14px; border-radius:8px; margin-bottom:16px; font-size:13px;">
        {{ session('success') }}
    </div>
@endif

{{-- ── Resumen ─────────────────────────────────────────────────────────────── --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px; margin-bottom:24px;">

    {{-- Saldo Actual --}}
    <div class="card" style="padding:20px; border:1px solid {{ $currentBalance !== null ? 'var(--blue)' : 'var(--border)' }};">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">Saldo Actual (IBKR)</div>
        @if($currentBalance !== null)
            <div style="font-size:28px; font-weight:800; color:var(--blue);">${{ number_format($currentBalance, 2) }}</div>
            <div class="text-muted" style="font-size:11px; margin-top:4px;">Último ajuste manual</div>
        @else
            <div style="font-size:20px; font-weight:700; color:var(--text-muted);">Sin registrar</div>
            <div class="text-muted" style="font-size:11px; margin-top:4px;">Agrega un "Ajuste de saldo" abajo</div>
        @endif
    </div>

    {{-- Depósitos --}}
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">Total Depositado</div>
        <div style="font-size:28px; font-weight:800; color:var(--green);">${{ number_format($totalDeposits, 2) }}</div>
        <div class="text-muted" style="font-size:11px; margin-top:4px;">Capital aportado históricamente</div>
    </div>

    {{-- Retiros --}}
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">Total Retirado</div>
        <div style="font-size:28px; font-weight:800; color:var(--red);">-${{ number_format($totalWithdrawals, 2) }}</div>
        <div class="text-muted" style="font-size:11px; margin-top:4px;">Retiros realizados</div>
    </div>

    {{-- Capital Neto --}}
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">Capital Neto Aportado</div>
        <div style="font-size:28px; font-weight:800; color:{{ $netCapital >= 0 ? 'var(--text-primary)' : 'var(--red)' }};">${{ number_format($netCapital, 2) }}</div>
        <div class="text-muted" style="font-size:11px; margin-top:4px;">Depósitos − Retiros</div>
    </div>

    {{-- P&L de Trading --}}
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">P&L Neto de Trading</div>
        <div style="font-size:28px; font-weight:800; color:{{ $tradingNetPnl >= 0 ? 'var(--green)' : 'var(--red)' }};">
            {{ $tradingNetPnl >= 0 ? '+' : '' }}${{ number_format($tradingNetPnl, 2) }}
        </div>
        <div class="text-muted" style="font-size:11px; margin-top:4px;">Solo trades cerrados</div>
    </div>

    {{-- ROI --}}
    <div class="card" style="padding:20px;">
        <div class="text-muted" style="font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; margin-bottom:8px;">ROI sobre Capital</div>
        @if($roi !== null)
            <div style="font-size:28px; font-weight:800; color:{{ $roi >= 0 ? 'var(--green)' : 'var(--red)' }};">
                {{ $roi >= 0 ? '+' : '' }}{{ $roi }}%
            </div>
            <div class="text-muted" style="font-size:11px; margin-top:4px;">P&L ÷ Capital aportado</div>
        @else
            <div style="font-size:20px; font-weight:700; color:var(--text-muted);">--</div>
            <div class="text-muted" style="font-size:11px; margin-top:4px;">Registra depósitos primero</div>
        @endif
    </div>

</div>

{{-- ── Explicación ─────────────────────────────────────────────────────────── --}}
<div class="card" style="padding:16px 20px; margin-bottom:24px; background:rgba(59,130,246,.06); border:1px solid rgba(59,130,246,.2);">
    <div style="font-size:13px; color:var(--blue); font-weight:700; margin-bottom:6px;">💡 ¿Por qué registrar depósitos y retiros?</div>
    <div style="font-size:12px; color:var(--text-secondary); line-height:1.6;">
        Los <strong>retiros</strong> no son pérdidas de trading — son capital que sacaste de la cuenta.
        Si solo miras el P&L de trades, los retiros aparecen como "dinero que falta" y distorsionan tu rendimiento real.
        Registrando cada movimiento puedes ver: <strong>Saldo IBKR = Capital neto + P&L trading</strong>.
        También puedes usar <em>Ajuste de saldo</em> para sincronizar el saldo real de tu cuenta hoy ($554).
    </div>
</div>

{{-- ── Formulario ──────────────────────────────────────────────────────────── --}}
<div class="card" style="padding:20px; margin-bottom:24px;">
    <h3 style="margin:0 0 16px; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted);">Registrar Movimiento</h3>
    <form method="POST" action="{{ route('account.store') }}" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
        @csrf
        <div style="display:flex; flex-direction:column; gap:4px;">
            <label style="font-size:11px; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Tipo</label>
            <select name="type" class="form-input" style="width:160px;" required onchange="updateLabel(this.value)">
                <option value="deposit">💰 Depósito</option>
                <option value="withdrawal">💸 Retiro</option>
                <option value="adjustment">📊 Ajuste de saldo actual</option>
            </select>
        </div>
        <div style="display:flex; flex-direction:column; gap:4px;">
            <label id="amountLabel" style="font-size:11px; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Monto (USD)</label>
            <input type="number" name="amount" step="0.01" min="0.01" class="form-input" style="width:150px;" placeholder="0.00" required>
        </div>
        <div style="display:flex; flex-direction:column; gap:4px;">
            <label style="font-size:11px; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Fecha</label>
            <input type="date" name="date" class="form-input" style="width:150px;" value="{{ date('Y-m-d') }}" required>
        </div>
        <div style="display:flex; flex-direction:column; gap:4px; flex:1; min-width:200px;">
            <label style="font-size:11px; color:var(--text-muted); font-weight:700; text-transform:uppercase;">Notas (opcional)</label>
            <input type="text" name="notes" class="form-input" placeholder="Ej: Depósito inicial, Retiro para gastos...">
        </div>
        <button type="submit" class="btn-primary">Guardar</button>
    </form>
    <div id="adjustmentHint" style="display:none; margin-top:10px; font-size:12px; color:var(--text-muted); background:rgba(255,255,255,.04); padding:8px 12px; border-radius:6px;">
        📊 <strong>Ajuste de saldo:</strong> Ingresa el saldo actual exacto de tu cuenta IBKR. Esto no afecta el P&L de trading — solo guarda una foto del saldo real en esa fecha. Ideal para sincronizar cuando hay discrepancias.
    </div>
</div>

{{-- ── Historial ────────────────────────────────────────────────────────────── --}}
<div class="card" style="overflow:hidden;">
    <div style="padding:16px 20px; border-bottom:1px solid var(--border);">
        <h3 style="margin:0; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted);">Historial de Movimientos</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th style="text-align:right;">Monto</th>
                <th>Notas</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $tx)
                <tr>
                    <td class="text-muted" style="font-size:12px;">{{ $tx->date->format('M d, Y') }}</td>
                    <td>
                        @if($tx->type === 'deposit')
                            <span class="badge-green" style="font-size:10px; padding:2px 8px; border-radius:4px; font-weight:700;">DEPÓSITO</span>
                        @elseif($tx->type === 'withdrawal')
                            <span class="badge-red" style="font-size:10px; padding:2px 8px; border-radius:4px; font-weight:700;">RETIRO</span>
                        @else
                            <span class="badge-blue" style="font-size:10px; padding:2px 8px; border-radius:4px; font-weight:700;">AJUSTE</span>
                        @endif
                    </td>
                    <td style="text-align:right; font-weight:700;
                        color:{{ $tx->type === 'deposit' ? 'var(--green)' : ($tx->type === 'withdrawal' ? 'var(--red)' : 'var(--blue)') }};">
                        {{ $tx->type === 'withdrawal' ? '-' : '' }}${{ number_format($tx->amount, 2) }}
                    </td>
                    <td style="font-size:12px; color:var(--text-secondary);">{{ $tx->notes ?: '--' }}</td>
                    <td style="text-align:right;">
                        <form method="POST" action="{{ route('account.destroy', $tx) }}" onsubmit="return confirm('¿Eliminar este movimiento?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="background:none; border:none; color:var(--text-muted); cursor:pointer; font-size:12px; padding:2px 6px;" title="Eliminar">✕</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center; color:var(--text-muted); padding:40px; font-size:13px;">
                        Sin movimientos registrados. Comienza registrando tus depósitos históricos.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @if($transactions->hasPages())
        <div style="padding:12px 16px; border-top:1px solid var(--border);">
            {{ $transactions->links() }}
        </div>
    @endif
</div>

<script>
function updateLabel(type) {
    const hint = document.getElementById('adjustmentHint');
    const label = document.getElementById('amountLabel');
    if (type === 'adjustment') {
        hint.style.display = 'block';
        label.textContent = 'Saldo actual (USD)';
    } else {
        hint.style.display = 'none';
        label.textContent = 'Monto (USD)';
    }
}
</script>
@endsection
