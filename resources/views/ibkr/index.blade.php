@extends('layouts.app')
@section('title', 'IBKR Import - TradeLog')

@section('content')
<div style="max-width:860px;">
    <div style="margin-bottom:24px;">
        <h1>Importar desde Interactive Brokers</h1>
        <div class="text-muted" style="font-size:13px; margin-top:4px;">
            Agrupa partial fills · Cruza aperturas/cierres · P&L FIFO real · Sin duplicados
        </div>
    </div>

    {{-- Componente Livewire --}}
    <div class="card" style="padding:28px; margin-bottom:20px;">
        <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:var(--text-muted); margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid var(--border);">
            Subir Flex Query XML
        </div>
        @livewire('i-b-k-r-importer')
    </div>

    {{-- Instrucciones --}}
    <div class="card" style="padding:24px;">
        <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.1em; color:var(--text-muted); margin-bottom:16px;">
            ¿Cómo obtener el XML de IBKR?
        </div>
        <ol style="font-size:13px; color:var(--text-primary); line-height:2; padding-left:20px; margin:0;">
            <li>Entra a <strong style="color:#fff;">Client Portal</strong> de Interactive Brokers</li>
            <li>Ve a <strong style="color:#fff;">Reports → Flex Queries</strong></li>
            <li>Crea una <strong style="color:#fff;">Activity Flex Query</strong> con sección <strong style="color:#fff;">Trades</strong></li>
            <li>Formato: <strong style="color:#fff;">XML</strong> · Date range: el periodo que quieras</li>
            <li>Descarga el XML y súbelo aquí</li>
        </ol>
        <div style="margin-top:16px; padding:12px 16px; background:rgba(41,98,255,0.06); border-radius:6px; border:1px solid rgba(41,98,255,0.2); font-size:12px; color:var(--text-secondary);">
            💡 Puedes subir el mismo XML múltiples veces — los duplicados se detectan automáticamente por <code style="color:#5b8af5;">Order ID</code> y nunca se importan dos veces.
        </div>
    </div>
</div>

@livewireScripts
@endsection
