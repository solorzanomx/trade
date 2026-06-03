<div>
    {{-- ═══ IDLE / UPLOAD ═══ --}}
    @if($status === 'idle' || $status === 'uploading')

    <form wire:submit="runImport">
        {{-- Drop zone --}}
        <div style="border:2px dashed {{ $xmlFile ? '#26a69a' : 'var(--border)' }};
                    border-radius:8px; padding:40px; text-align:center; cursor:pointer;
                    margin-bottom:20px; transition:all .2s;
                    background:{{ $xmlFile ? 'rgba(38,166,154,0.05)' : 'transparent' }};"
             onclick="document.getElementById('ibkrFile').click()"
             ondragover="event.preventDefault(); this.style.borderColor='#2962ff'"
             ondragleave="this.style.borderColor='{{ $xmlFile ? '#26a69a' : 'var(--border)' }}'"
             ondrop="handleIBKRDrop(event)">

            <div style="font-size:36px; margin-bottom:12px;">
                {{ $xmlFile ? '✅' : '📄' }}
            </div>

            @if($xmlFile)
                <div style="color:var(--green); font-weight:700; margin-bottom:4px;">
                    {{ $xmlFile->getClientOriginalName() }}
                </div>
                <div class="text-muted" style="font-size:12px;">
                    {{ number_format($xmlFile->getSize() / 1024, 1) }} KB · listo para importar
                </div>
            @else
                <div style="color:#fff; font-weight:600; margin-bottom:6px;">
                    Arrastra tu Flex Query XML aquí
                </div>
                <div class="text-muted" style="font-size:13px;">o haz clic para seleccionar</div>
            @endif
        </div>

        <input type="file" id="ibkrFile" accept=".xml,.txt" style="display:none;"
               wire:model="xmlFile">

        @if($errors->has('xmlFile'))
        <div class="alert-error" style="margin-bottom:12px;">{{ $errors->first('xmlFile') }}</div>
        @endif

        {{-- Barra de progreso de upload --}}
        @if($status === 'uploading')
        <div style="background:var(--border); border-radius:4px; height:4px; margin-bottom:16px; overflow:hidden;">
            <div wire:loading wire:target="xmlFile"
                 style="height:4px; background:var(--green); border-radius:4px; width:100%; animation:pulse 1s infinite;"></div>
        </div>
        @endif

        <div style="display:flex; gap:12px; align-items:center;">
            <button type="submit" class="btn-primary"
                    style="padding:10px 28px; font-size:14px; {{ !$xmlFile ? 'opacity:0.5; cursor:not-allowed;' : '' }}"
                    {{ !$xmlFile ? 'disabled' : '' }}>
                <span wire:loading.remove wire:target="runImport">Importar Trades</span>
                <span wire:loading wire:target="runImport">Importando...</span>
            </button>

            @if($xmlFile)
            <button type="button" wire:click="resetForm" class="btn-ghost" style="font-size:13px;">
                Cancelar
            </button>
            @endif
        </div>
    </form>

    @endif

    {{-- ═══ IMPORTING ═══ --}}
    @if($status === 'importing')
    <div style="padding:40px; text-align:center;">
        <div style="font-size:36px; margin-bottom:16px; animation:spin 1s linear infinite; display:inline-block;">⚙️</div>
        <div style="color:#fff; font-weight:700; font-size:16px; margin-bottom:8px;">Procesando tu historial de IBKR</div>
        <div class="text-muted" style="font-size:13px; margin-bottom:20px;">{{ $message }}</div>
        <div style="background:var(--border); border-radius:6px; height:8px; max-width:400px; margin:0 auto; overflow:hidden;">
            <div style="height:8px; border-radius:6px; background:var(--green); width:{{ $progress }}%; transition:width .5s ease;"></div>
        </div>
        <div style="font-size:12px; color:var(--text-muted); margin-top:8px;">{{ $progress }}%</div>
    </div>
    @endif

    {{-- ═══ DONE ═══ --}}
    @if($status === 'done')
    <div>
        {{-- Summary cards --}}
        <div style="display:flex; gap:16px; flex-wrap:wrap; margin-bottom:20px;">
            <div style="flex:1; min-width:120px; padding:16px; background:rgba(38,166,154,0.08); border:1px solid rgba(38,166,154,0.3); border-radius:8px; text-align:center;">
                <div style="font-size:32px; font-weight:800; color:var(--green);">{{ $imported }}</div>
                <div class="text-muted" style="font-size:12px; margin-top:4px;">Nuevas aperturas</div>
            </div>
            <div style="flex:1; min-width:120px; padding:16px; background:rgba(91,138,245,0.08); border:1px solid rgba(91,138,245,0.3); border-radius:8px; text-align:center;">
                <div style="font-size:32px; font-weight:800; color:#5b8af5;">{{ $closed }}</div>
                <div class="text-muted" style="font-size:12px; margin-top:4px;">Trades cerrados</div>
            </div>
            <div style="flex:1; min-width:120px; padding:16px; background:rgba(42,46,57,0.5); border:1px solid var(--border); border-radius:8px; text-align:center;">
                <div style="font-size:32px; font-weight:800; color:var(--text-muted);">{{ $duplicates }}</div>
                <div class="text-muted" style="font-size:12px; margin-top:4px;">Duplicados omitidos</div>
            </div>
            @if(count($errors) > 0)
            <div style="flex:1; min-width:120px; padding:16px; background:rgba(239,83,80,0.08); border:1px solid rgba(239,83,80,0.3); border-radius:8px; text-align:center;">
                <div style="font-size:32px; font-weight:800; color:var(--red);">{{ count($errors) }}</div>
                <div class="text-muted" style="font-size:12px; margin-top:4px;">Errores</div>
            </div>
            @endif
        </div>

        {{-- Barra de progreso completa --}}
        <div style="background:var(--border); border-radius:6px; height:6px; margin-bottom:20px; overflow:hidden;">
            <div style="height:6px; border-radius:6px; background:var(--green); width:100%;"></div>
        </div>

        {{-- Errores --}}
        @if(count($errors) > 0)
        <div style="background:rgba(239,83,80,0.08); border:1px solid rgba(239,83,80,0.2); border-radius:6px; padding:12px 16px; margin-bottom:16px;">
            <div style="font-size:11px; font-weight:700; color:var(--red); text-transform:uppercase; margin-bottom:8px;">Errores</div>
            @foreach($errors as $err)
            <div style="font-size:12px; color:var(--red); margin-bottom:4px;">• {{ $err }}</div>
            @endforeach
        </div>
        @endif

        {{-- Tabla de trades --}}
        @if(count($trades) > 0)
        <div style="max-height:360px; overflow-y:auto; border-radius:6px; border:1px solid var(--border); margin-bottom:16px;">
            <table>
                <thead style="position:sticky; top:0; background:var(--bg-card);">
                    <tr>
                        <th>Acción</th>
                        <th>Símbolo</th>
                        <th>Tipo</th>
                        <th style="text-align:right;">Precio</th>
                        <th style="text-align:right;">Qty</th>
                        <th style="text-align:right;">Fecha</th>
                        <th style="text-align:right;">P&L</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($trades as $t)
                    <tr>
                        <td>
                            <span style="font-size:11px; padding:2px 8px; border-radius:4px; font-weight:600;
                                {{ str_contains($t['action'], 'close')
                                    ? 'background:rgba(91,138,245,0.15); color:#5b8af5;'
                                    : 'background:rgba(38,166,154,0.15); color:var(--green);' }}">
                                {{ $t['action'] }}
                            </span>
                        </td>
                        <td style="font-weight:700; color:#fff;">{{ $t['symbol'] }}</td>
                        <td style="text-transform:uppercase; font-size:11px; color:var(--text-muted);">{{ $t['type'] }}</td>
                        <td style="text-align:right;">${{ number_format($t['price'], 2) }}</td>
                        <td style="text-align:right;">{{ number_format($t['qty'], 0) }}</td>
                        <td style="text-align:right; color:var(--text-muted);">{{ $t['date'] }}</td>
                        <td style="text-align:right; font-weight:700;">
                            @if($t['pnl'] !== null)
                                <span style="color:{{ $t['pnl'] >= 0 ? 'var(--green)' : 'var(--red)' }};">
                                    {{ $t['pnl'] >= 0 ? '+' : '' }}${{ number_format($t['pnl'], 2) }}
                                </span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div style="display:flex; gap:12px;">
            <a href="{{ route('trades.index') }}" class="btn-primary" style="text-decoration:none;">Ver operaciones</a>
            <a href="{{ route('metrics.index') }}" style="text-decoration:none; padding:10px 16px; font-size:13px; color:#5b8af5;">Ver métricas →</a>
            <button wire:click="resetForm" class="btn-ghost" style="margin-left:auto; font-size:13px;">Nueva importación</button>
        </div>
    </div>
    @endif

    {{-- ═══ ERROR ═══ --}}
    @if($status === 'error')
    <div style="padding:32px; text-align:center;">
        <div style="font-size:36px; margin-bottom:12px;">❌</div>
        <div style="color:var(--red); font-weight:700; margin-bottom:8px;">Error en la importación</div>
        <div class="text-muted" style="font-size:13px; margin-bottom:20px;">{{ $message }}</div>
        <button wire:click="resetForm" class="btn-ghost">Intentar de nuevo</button>
    </div>
    @endif
</div>

<style>
@keyframes spin {
    from { transform: rotate(0deg); }
    to   { transform: rotate(360deg); }
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.5; }
}
</style>

<script>
function handleIBKRDrop(e) {
    e.preventDefault();
    const file = e.dataTransfer.files[0];
    if (!file) return;
    const input = document.getElementById('ibkrFile');
    const dt = new DataTransfer();
    dt.items.add(file);
    input.files = dt.files;
    input.dispatchEvent(new Event('change'));
}
</script>
