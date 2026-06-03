@extends('layouts.app')
@section('title', 'Importar IBKR - TradeLog')

@section('content')
<div style="max-width:860px;">
    <div style="margin-bottom:24px;">
        <h1>Importar desde Interactive Brokers</h1>
        <div class="text-muted" style="font-size:13px; margin-top:4px;">
            Sube tu Flex Query XML de IBKR — agrupa partial fills, cruza aperturas con cierres y calcula P&L real
        </div>
    </div>

    {{-- Resultados --}}
    @if(session('ibkr_results'))
    @php $r = session('ibkr_results'); @endphp
    <div class="card" style="padding:24px; margin-bottom:20px; border-color:{{ $r['imported'] + $r['closed'] > 0 ? 'rgba(38,166,154,0.4)' : 'rgba(239,83,80,0.3)' }};">
        <div style="font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:16px; color:var(--text-muted);">
            Resultado de la Importación
        </div>
        <div style="display:flex; gap:32px; flex-wrap:wrap; margin-bottom:20px;">
            <div style="text-align:center;">
                <div style="font-size:36px; font-weight:800; color:var(--green);">{{ $r['imported'] }}</div>
                <div class="text-muted" style="font-size:12px; margin-top:4px;">Nuevas aperturas</div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:36px; font-weight:800; color:#5b8af5;">{{ $r['closed'] }}</div>
                <div class="text-muted" style="font-size:12px; margin-top:4px;">Trades cerrados</div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:36px; font-weight:800; color:var(--text-muted);">{{ $r['duplicates'] }}</div>
                <div class="text-muted" style="font-size:12px; margin-top:4px;">Duplicados omitidos</div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:36px; font-weight:800; color:{{ $r['skipped'] > 0 ? 'var(--red)' : 'var(--text-muted)' }};">{{ $r['skipped'] }}</div>
                <div class="text-muted" style="font-size:12px; margin-top:4px;">Omitidos</div>
            </div>
            @if(count($r['errors']) > 0)
            <div style="text-align:center;">
                <div style="font-size:36px; font-weight:800; color:var(--red);">{{ count($r['errors']) }}</div>
                <div class="text-muted" style="font-size:12px; margin-top:4px;">Errores</div>
            </div>
            @endif
        </div>

        @if(count($r['errors']) > 0)
        <div style="background:rgba(239,83,80,0.08); border-radius:6px; padding:12px 16px; margin-bottom:16px;">
            <div style="font-size:11px; font-weight:700; color:var(--red); text-transform:uppercase; margin-bottom:8px;">Errores</div>
            @foreach($r['errors'] as $err)
            <div style="font-size:12px; color:var(--red); margin-bottom:4px;">• {{ $err }}</div>
            @endforeach
        </div>
        @endif

        @if(count($r['trades']) > 0)
        <div style="max-height:400px; overflow-y:auto; border-radius:6px; border:1px solid var(--border);">
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
                    @foreach($r['trades'] as $t)
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
                        <td style="text-align:right;">${{ number_format($t['price'], 4) }}</td>
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

        <div style="margin-top:16px; display:flex; gap:12px;">
            <a href="{{ route('trades.index') }}" class="btn-primary" style="text-decoration:none;">Ver mis operaciones</a>
            <a href="{{ route('metrics.index') }}" class="btn-ghost" style="text-decoration:none;">Ver métricas</a>
        </div>
        @endif
    </div>
    @endif

    {{-- Upload form --}}
    <div class="card" style="padding:28px; margin-bottom:20px;">
        <div style="font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:20px; color:var(--text-muted); padding-bottom:12px; border-bottom:1px solid var(--border);">
            Subir Flex Query XML
        </div>

        <form action="{{ route('ibkr.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div id="dropZone" style="border:2px dashed var(--border); border-radius:8px; padding:40px; text-align:center; cursor:pointer; margin-bottom:20px; transition:all .2s;"
                 onclick="document.getElementById('xmlFile').click()"
                 ondragover="event.preventDefault(); this.style.borderColor='#2962ff'; this.style.background='rgba(41,98,255,0.05)'"
                 ondragleave="this.style.borderColor='var(--border)'; this.style.background=''"
                 ondrop="handleDrop(event)">
                <div style="font-size:32px; margin-bottom:12px;">📄</div>
                <div style="color:#fff; font-weight:600; margin-bottom:6px;">Arrastra tu XML aquí</div>
                <div class="text-muted" style="font-size:13px;">o haz clic para seleccionar el archivo</div>
                <div id="fileName" class="text-muted" style="font-size:12px; margin-top:8px;"></div>
            </div>
            <input type="file" id="xmlFile" name="xml_file" accept=".xml,.txt" style="display:none;"
                   onchange="document.getElementById('fileName').textContent = this.files[0]?.name || ''">

            @error('xml_file')
            <div class="alert-error" style="margin-bottom:12px;">{{ $message }}</div>
            @enderror

            <button type="submit" class="btn-primary" style="padding:10px 28px; font-size:14px;">
                Importar Trades
            </button>
        </form>
    </div>

    {{-- Instrucciones --}}
    <div class="card" style="padding:24px;">
        <div style="font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; margin-bottom:16px; color:var(--text-muted);">
            ¿Cómo obtener el XML de IBKR?
        </div>
        <ol style="font-size:13px; color:var(--text-primary); line-height:1.8; padding-left:20px;">
            <li>Entra a <strong style="color:#fff;">Client Portal</strong> de Interactive Brokers</li>
            <li>Ve a <strong style="color:#fff;">Reports → Flex Queries</strong></li>
            <li>Crea una <strong style="color:#fff;">Activity Flex Query</strong></li>
            <li>En la sección <strong style="color:#fff;">Trades</strong>, activa todos los campos</li>
            <li>Formato: <strong style="color:#fff;">XML</strong> · Delivery: <strong style="color:#fff;">HTTP</strong></li>
            <li>Descarga el XML y súbelo aquí</li>
        </ol>
        <div style="margin-top:16px; padding:12px 16px; background:rgba(41,98,255,0.06); border-radius:6px; border:1px solid rgba(41,98,255,0.2); font-size:12px; color:var(--text-secondary);">
            💡 <strong style="color:#fff;">Lógica de importación:</strong>
            Agrupa partial fills por Order ID · Cruza aperturas con cierres por símbolo · Usa el P&L FIFO de IBKR · Omite duplicados automáticamente · Ignora conversiones de divisas (USD/MXN)
        </div>
    </div>
</div>

<script>
function handleDrop(e) {
    e.preventDefault();
    const file = e.dataTransfer.files[0];
    if (file) {
        const input = document.getElementById('xmlFile');
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('dropZone').style.borderColor = '#26a69a';
        document.getElementById('dropZone').style.background = 'rgba(38,166,154,0.05)';
    }
}
</script>
@endsection
