@extends('layouts.app')
@section('title', 'Reporte Diario - TradeLog')

@section('content')

@if(!$template)
{{-- Sin plantilla todavía --}}
<div class="card" style="padding:48px; text-align:center;">
    <div style="font-size:48px; margin-bottom:16px;">📊</div>
    <div style="font-size:20px; font-weight:700; color:#fff; margin-bottom:8px;">Configura tu Reporte Diario</div>
    <div class="text-muted" style="font-size:14px; margin-bottom:24px;">
        Se ejecuta automáticamente a las 9:35 AM CDMX de lunes a viernes
    </div>
    <a href="{{ route('reports.setup') }}" class="btn-primary" style="text-decoration:none; padding:12px 32px; font-size:15px;">
        Crear Reporte QQQ
    </a>
</div>
@else

<div style="display:grid; grid-template-columns:280px 1fr; gap:16px; align-items:start;">

    {{-- SIDEBAR IZQUIERDO --}}
    <div>
        {{-- Info de la plantilla --}}
        <div class="card" style="padding:16px; margin-bottom:12px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <div style="font-size:13px; font-weight:700; color:#fff;">{{ $template->name }}</div>
                <span style="font-size:11px; padding:2px 8px; border-radius:4px;
                    {{ $template->is_active ? 'background:rgba(38,166,154,0.15); color:var(--green);' : 'background:rgba(42,46,57,0.8); color:var(--text-muted);' }}">
                    {{ $template->is_active ? 'Activo' : 'Pausado' }}
                </span>
            </div>
            <div class="text-muted" style="font-size:12px; margin-bottom:4px;">
                ⏰ {{ $template->schedule_time }} CDMX · Lun–Vie
            </div>
            <div class="text-muted" style="font-size:12px; margin-bottom:16px;">
                📈 Símbolo: <strong style="color:#fff;">{{ $template->symbol }}</strong>
            </div>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <a href="{{ route('reports.edit', $template) }}" class="btn-ghost"
                   style="text-decoration:none; text-align:center; font-size:12px; padding:6px 12px;">
                    ✏️ Editar Prompt
                </a>
                <form method="POST" action="{{ route('reports.generate', $template) }}">
                    @csrf
                    <input type="hidden" name="date" value="{{ now()->toDateString() }}">
                    <button type="submit" class="btn-primary"
                            style="width:100%; font-size:12px; padding:7px 12px;">
                        ✨ Generar Ahora
                    </button>
                </form>
            </div>
        </div>

        {{-- Historial --}}
        <div class="card" style="overflow:hidden;">
            <div style="padding:12px 16px; border-bottom:1px solid var(--border); font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.08em;">
                Historial
            </div>
            <div style="max-height:500px; overflow-y:auto;">
                @forelse($reports as $r)
                <a href="{{ route('reports.index', ['report' => $r->id]) }}"
                   style="display:flex; justify-content:space-between; align-items:center; padding:10px 16px;
                          border-bottom:1px solid rgba(42,46,57,0.5); text-decoration:none;
                          {{ isset($report) && $report->id === $r->id ? 'background:var(--bg-hover);' : '' }}">
                    <div>
                        <div style="font-size:13px; color:{{ $r->status === 'error' ? 'var(--red)' : '#fff' }}; font-weight:500;">
                            {{ $r->report_date->format('d M') }}
                        </div>
                        <div class="text-muted" style="font-size:11px;">{{ $r->report_date->locale('es')->isoFormat('dddd') }}</div>
                    </div>
                    <span style="font-size:10px; padding:2px 6px; border-radius:3px;
                        {{ $r->status === 'generated' ? 'color:var(--green); background:rgba(38,166,154,0.1);'
                        : 'color:var(--red); background:rgba(239,83,80,0.1);' }}">
                        {{ $r->status === 'generated' ? '✓' : '✗' }}
                    </span>
                </a>
                @empty
                <div style="padding:16px; text-align:center; color:var(--text-muted); font-size:13px;">
                    Sin reportes todavía
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- REPORTE PRINCIPAL --}}
    <div>
        @if($report && $report->status === 'generated')
        <div class="card" style="overflow:hidden;">
            <div style="padding:16px 24px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                <div>
                    <div style="font-size:16px; font-weight:700; color:#fff;">
                        {{ $template->symbol }} — {{ $report->report_date->locale('es')->isoFormat('dddd D [de] MMMM') }}
                    </div>
                    <div class="text-muted" style="font-size:12px; margin-top:2px;">
                        Generado a las {{ $report->created_at->setTimezone('America/Mexico_City')->format('H:i') }} CDMX
                        · {{ ucfirst($report->source) }}
                    </div>
                </div>
                <form method="POST" action="{{ route('reports.generate', $template) }}">
                    @csrf
                    <input type="hidden" name="date" value="{{ $report->report_date->toDateString() }}">
                    <button type="submit" class="btn-ghost" style="font-size:12px; padding:6px 12px;">
                        🔄 Regenerar
                    </button>
                </form>
            </div>
            <div style="padding:24px; max-width:none;" id="reportContent">
                {!! \Illuminate\Support\Str::of($report->content)->markdown() !!}
            </div>
        </div>

        @elseif($report && $report->status === 'error')
        <div class="card" style="padding:32px; text-align:center; border-color:rgba(239,83,80,0.3);">
            <div style="font-size:32px; margin-bottom:12px;">❌</div>
            <div style="color:var(--red); font-weight:700; margin-bottom:8px;">Error al generar el reporte</div>
            <div class="text-muted" style="font-size:13px; margin-bottom:20px;">{{ $report->error_message }}</div>
            <form method="POST" action="{{ route('reports.generate', $template) }}">
                @csrf
                <input type="hidden" name="date" value="{{ $report->report_date->toDateString() }}">
                <button type="submit" class="btn-primary">Intentar de nuevo</button>
            </form>
        </div>

        @else
        <div class="card" style="padding:48px; text-align:center;">
            <div style="font-size:48px; margin-bottom:16px;">📋</div>
            <div style="color:#fff; font-size:16px; font-weight:700; margin-bottom:8px;">Sin reporte para hoy</div>
            <div class="text-muted" style="font-size:13px; margin-bottom:24px;">
                Se genera automáticamente a las <strong style="color:#fff;">{{ $template->schedule_time }} CDMX</strong>
                de lunes a viernes.<br>O genera uno manualmente ahora.
            </div>
            <form method="POST" action="{{ route('reports.generate', $template) }}" style="display:inline;">
                @csrf
                <input type="hidden" name="date" value="{{ now()->toDateString() }}">
                <button type="submit" class="btn-primary" style="padding:12px 32px; font-size:15px;">
                    ✨ Generar Reporte de Hoy
                </button>
            </form>
        </div>
        @endif
    </div>

</div>
@endif

<style>
/* Estilos markdown del reporte */
#reportContent h1 { font-size: 18px; font-weight: 800; color: #fff; margin: 24px 0 12px; padding-bottom: 8px; border-bottom: 1px solid var(--border); }
#reportContent h2 { font-size: 15px; font-weight: 700; color: #fff; margin: 20px 0 10px; }
#reportContent h3 { font-size: 13px; font-weight: 700; color: #5b8af5; margin: 16px 0 8px; text-transform: uppercase; letter-spacing: .05em; }
#reportContent p  { font-size: 13px; color: var(--text-primary); line-height: 1.7; margin-bottom: 12px; }
#reportContent ul, #reportContent ol { font-size: 13px; color: var(--text-primary); line-height: 1.8; padding-left: 20px; margin-bottom: 12px; }
#reportContent li { margin-bottom: 4px; }
#reportContent strong { color: #fff; font-weight: 700; }
#reportContent em { color: #f9a825; font-style: normal; }
#reportContent hr { border: none; border-top: 1px solid var(--border); margin: 20px 0; }
#reportContent table { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 12px; }
#reportContent th { background: var(--bg-primary); color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: .06em; padding: 8px 12px; text-align: left; border-bottom: 1px solid var(--border); }
#reportContent td { padding: 8px 12px; border-bottom: 1px solid rgba(42,46,57,0.5); color: var(--text-primary); }
#reportContent tr:hover td { background: var(--bg-hover); }
#reportContent blockquote { border-left: 3px solid #5b8af5; padding: 8px 16px; margin: 12px 0; background: rgba(91,138,245,0.06); border-radius: 0 6px 6px 0; }
#reportContent code { background: var(--bg-primary); color: #5b8af5; padding: 2px 6px; border-radius: 4px; font-size: 12px; }
</style>
@endsection
