@extends('layouts.app')
@section('title', 'Import CSV - TradeLog')

@section('content')
<div style="max-width:640px;">
    <div style="margin-bottom:24px;">
        <h1>Import Trades</h1>
        <div class="text-muted" style="font-size:13px; margin-top:4px;">Sube tu bitácora de Excel/Google Sheets exportada como CSV</div>
    </div>

    <!-- Format Info -->
    <div class="card" style="padding:16px 20px; margin-bottom:16px; border-left:3px solid var(--blue);">
        <div style="font-size:13px; font-weight:600; color:#fff; margin-bottom:8px;">Formato esperado de tu CSV</div>
        <div style="font-size:12px; color:var(--text-secondary); line-height:1.6;">
            Columnas en orden: <code style="background:var(--bg-primary); padding:1px 5px; border-radius:3px; color:var(--green);">#, Fecha, Stock, Call/Put, Precio Compra, Contratos, Precio Venta, G/P, %, Comisión, Estrategia, Notas</code>
        </div>
        <div style="font-size:12px; color:var(--text-muted); margin-top:8px;">
            Las filas sin fecha o sin símbolo son ignoradas automáticamente.
        </div>
    </div>

    <!-- Upload Form -->
    <div class="card" style="padding:24px;">
        <form method="POST" action="{{ route('import.store') }}" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom:20px;">
                <label>Archivo CSV</label>
                <div style="border:2px dashed var(--border); border-radius:8px; padding:40px; text-align:center; transition:border-color .15s;"
                     id="dropzone"
                     ondragover="event.preventDefault(); this.style.borderColor='var(--blue)'"
                     ondragleave="this.style.borderColor='var(--border)'"
                     ondrop="handleDrop(event)">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="var(--text-muted)" style="margin-bottom:12px;"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                    <div style="font-size:14px; color:var(--text-secondary); margin-bottom:8px;">Arrastra tu CSV aquí o</div>
                    <label for="csv_file" style="cursor:pointer; color:var(--blue); font-size:13px; font-weight:600; text-transform:none; letter-spacing:0;">
                        Selecciona archivo
                    </label>
                    <input type="file" id="csv_file" name="csv_file" accept=".csv,.txt" style="display:none;" onchange="showFileName(this)">
                    <div id="file-name" style="font-size:12px; color:var(--green); margin-top:8px;"></div>
                </div>
            </div>

            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn-primary" style="flex:1; padding:12px;">Importar Trades</button>
                <a href="{{ route('trades.index') }}" class="btn-ghost" style="text-decoration:none; padding:12px 20px;">Cancelar</a>
            </div>
        </form>
    </div>

    <!-- Instructions -->
    <div class="card" style="padding:16px 20px; margin-top:16px;">
        <div style="font-size:13px; font-weight:600; color:#fff; margin-bottom:12px;">Como exportar desde Google Sheets</div>
        <ol style="font-size:12px; color:var(--text-secondary); line-height:2; padding-left:16px; margin:0;">
            <li>Abre tu bitácora en Google Sheets</li>
            <li>Click en <strong style="color:var(--text-primary);">File → Download → CSV</strong></li>
            <li>Sube el archivo descargado aquí</li>
        </ol>
    </div>
</div>

<script>
function showFileName(input) {
    if (input.files[0]) {
        document.getElementById('file-name').textContent = input.files[0].name;
        document.getElementById('dropzone').style.borderColor = 'var(--green)';
    }
}
function handleDrop(event) {
    event.preventDefault();
    const file = event.dataTransfer.files[0];
    if (file) {
        const input = document.getElementById('csv_file');
        const dt = new DataTransfer();
        dt.items.add(file);
        input.files = dt.files;
        showFileName(input);
    }
    document.getElementById('dropzone').style.borderColor = 'var(--border)';
}
</script>
@endsection
