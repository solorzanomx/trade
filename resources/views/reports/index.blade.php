@extends('layouts.app')
@section('title', 'Reporte Diario - TradeLog')

@section('content')

@if(!$template)
<div class="card" style="padding:48px; text-align:center;">
    <div style="font-size:48px; margin-bottom:16px;">📊</div>
    <div style="font-size:20px; font-weight:700; color:#fff; margin-bottom:8px;">Configura tu Reporte Diario QQQ</div>
    <div class="text-muted" style="font-size:14px; margin-bottom:24px;">
        Se genera automáticamente a las 9:35 AM CDMX · Lunes a Viernes
    </div>
    <a href="{{ route('reports.setup') }}" class="btn-primary" style="text-decoration:none; padding:12px 32px; font-size:15px;">
        Crear Reporte QQQ
    </a>
</div>
@else

<div style="display:grid; grid-template-columns:260px 1fr; gap:16px; align-items:start;">

    {{-- ═══ SIDEBAR ═══ --}}
    <div style="position:sticky; top:24px;">

        {{-- Info plantilla --}}
        <div class="card" style="padding:16px; margin-bottom:10px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                <span style="font-size:14px; font-weight:800; color:#fff;">{{ $template->symbol }}</span>
                <span style="font-size:11px; padding:2px 8px; border-radius:4px;
                    {{ $template->is_active ? 'background:rgba(38,166,154,0.15); color:var(--green);' : 'background:rgba(42,46,57,0.8); color:var(--text-muted);' }}">
                    {{ $template->is_active ? '● Activo' : '○ Pausado' }}
                </span>
            </div>
            <div class="text-muted" style="font-size:12px; margin-bottom:14px;">
                ⏰ {{ $template->schedule_time }} CDMX · Lun–Vie
            </div>
            <div style="display:flex; flex-direction:column; gap:6px;">
                <form method="POST" action="{{ route('reports.generate', $template) }}">
                    @csrf
                    <input type="hidden" name="date" value="{{ now()->toDateString() }}">
                    <button type="submit" class="btn-primary" style="width:100%; font-size:12px; padding:8px;">
                        ✨ Generar Hoy
                    </button>
                </form>
                <a href="{{ route('reports.edit', $template) }}" class="btn-ghost"
                   style="text-decoration:none; text-align:center; font-size:12px; padding:7px;">
                    ✏️ Editar Prompt
                </a>
            </div>
        </div>

        {{-- Historial --}}
        <div class="card" style="overflow:hidden;">
            <div style="padding:10px 14px; border-bottom:1px solid var(--border); font-size:10px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.1em;">
                Historial
            </div>
            <div style="max-height:calc(100vh - 320px); overflow-y:auto;">
                @forelse($reports as $r)
                <a href="{{ route('reports.index', ['report' => $r->id]) }}"
                   style="display:flex; justify-content:space-between; align-items:center; padding:9px 14px;
                          border-bottom:1px solid rgba(42,46,57,0.4); text-decoration:none;
                          background:{{ isset($report) && $report->id === $r->id ? 'var(--bg-hover)' : 'transparent' }};">
                    <div>
                        <div style="font-size:13px; color:{{ $r->status === 'error' ? 'var(--red)' : '#fff' }}; font-weight:600;">
                            {{ $r->report_date->format('d M') }}
                        </div>
                        <div class="text-muted" style="font-size:10px; text-transform:capitalize;">
                            {{ $r->report_date->locale('es')->isoFormat('ddd') }}
                        </div>
                    </div>
                    <span style="font-size:11px; {{ $r->status === 'generated' ? 'color:var(--green);' : 'color:var(--red);' }}">
                        {{ $r->status === 'generated' ? '✓' : '✗' }}
                    </span>
                </a>
                @empty
                <div style="padding:20px; text-align:center; color:var(--text-muted); font-size:12px;">Sin reportes</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ═══ REPORTE PRINCIPAL ═══ --}}
    <div>
        @if($report && $report->status === 'generated')

        {{-- Header del reporte --}}
        <div class="card" style="padding:16px 24px; margin-bottom:12px; border-color:rgba(91,138,245,0.3);">
            <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div>
                    <div style="font-size:18px; font-weight:800; color:#fff;">
                        {{ $template->symbol }} &mdash;
                        {{ $report->report_date->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY') }}
                    </div>
                    <div class="text-muted" style="font-size:12px; margin-top:3px;">
                        Generado {{ $report->created_at->setTimezone('America/Mexico_City')->format('H:i') }} CDMX
                        · {{ ucfirst($report->source) }}
                        · {{ $report->report_date->isToday() ? '🟢 Hoy' : ($report->report_date->isYesterday() ? '🟡 Ayer' : '🔵 ' . $report->report_date->diffForHumans()) }}
                    </div>
                </div>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <form method="POST" action="{{ route('reports.generate', $template) }}" style="display:inline;">
                        @csrf
                        <input type="hidden" name="date" value="{{ $report->report_date->toDateString() }}">
                        <button type="submit" class="btn-ghost" style="font-size:12px; padding:6px 12px;">🔄 Regenerar</button>
                    </form>
                    <button onclick="window.print()" class="btn-ghost" style="font-size:12px; padding:6px 12px;">🖨️ Imprimir</button>
                </div>
            </div>
        </div>

        {{-- Contenido del reporte --}}
        <div class="card" style="overflow:hidden;">
            <div id="reportContent" style="padding:28px 32px;">
                {!! \Illuminate\Support\Str::of($report->content)->markdown(['html_input' => 'allow']) !!}
            </div>
        </div>

        @elseif($report && $report->status === 'error')
        <div class="card" style="padding:40px; text-align:center; border-color:rgba(239,83,80,0.3);">
            <div style="font-size:36px; margin-bottom:12px;">❌</div>
            <div style="color:var(--red); font-weight:700; font-size:16px; margin-bottom:8px;">Error al generar</div>
            <div class="text-muted" style="font-size:13px; margin-bottom:20px; max-width:400px; margin-left:auto; margin-right:auto;">
                {{ $report->error_message }}
            </div>
            <form method="POST" action="{{ route('reports.generate', $template) }}" style="display:inline;">
                @csrf
                <input type="hidden" name="date" value="{{ $report->report_date->toDateString() }}">
                <button type="submit" class="btn-primary" style="padding:10px 24px;">Intentar de nuevo</button>
            </form>
        </div>

        @else
        <div class="card" style="padding:60px; text-align:center;">
            <div style="font-size:56px; margin-bottom:16px;">📋</div>
            <div style="color:#fff; font-size:18px; font-weight:700; margin-bottom:8px;">Sin reporte para hoy</div>
            <div class="text-muted" style="font-size:14px; margin-bottom:6px;">
                Se genera automáticamente a las <strong style="color:#fff;">{{ $template->schedule_time }} CDMX</strong>
            </div>
            <div class="text-muted" style="font-size:13px; margin-bottom:28px;">
                O genera uno ahora manualmente
            </div>
            <form method="POST" action="{{ route('reports.generate', $template) }}" style="display:inline;">
                @csrf
                <input type="hidden" name="date" value="{{ now()->toDateString() }}">
                <button type="submit" class="btn-primary" style="padding:14px 40px; font-size:15px; font-weight:700;">
                    ✨ Generar Reporte de Hoy
                </button>
            </form>
        </div>
        @endif
    </div>
</div>
@endif

<style>
/* ═══ ESTILOS DEL REPORTE MARKDOWN ═══ */
#reportContent { font-size: 13.5px; color: var(--text-primary); line-height: 1.7; }

/* Headings */
#reportContent h1 { font-size: 22px; font-weight: 900; color: #fff; margin: 0 0 20px; padding-bottom: 12px; border-bottom: 2px solid var(--border); letter-spacing: -.3px; }
#reportContent h2 { font-size: 16px; font-weight: 800; color: #fff; margin: 32px 0 14px; padding: 8px 14px; background: var(--bg-primary); border-left: 3px solid #5b8af5; border-radius: 0 6px 6px 0; }
#reportContent h3 { font-size: 13px; font-weight: 700; color: #5b8af5; margin: 20px 0 8px; text-transform: uppercase; letter-spacing: .06em; }
#reportContent h4 { font-size: 13px; font-weight: 700; color: var(--text-primary); margin: 14px 0 6px; }

/* Párrafos */
#reportContent p  { margin-bottom: 10px; }

/* Listas */
#reportContent ul, #reportContent ol { padding-left: 22px; margin-bottom: 12px; }
#reportContent li { margin-bottom: 5px; line-height: 1.65; }

/* Bold/Italic */
#reportContent strong { color: #fff; font-weight: 700; }
#reportContent em { color: #f9a825; font-style: italic; }

/* HR */
#reportContent hr { border: none; border-top: 1px solid var(--border); margin: 24px 0; }

/* Code blocks (resumen final) */
#reportContent pre { background: var(--bg-primary); border: 1px solid var(--border); border-radius: 8px; padding: 16px 20px; margin: 16px 0; overflow-x: auto; }
#reportContent pre code { color: var(--green); font-size: 12.5px; font-family: 'JetBrains Mono', 'Fira Code', monospace; line-height: 1.8; background: none; padding: 0; border-radius: 0; }
#reportContent code { background: rgba(91,138,245,0.12); color: #5b8af5; padding: 2px 6px; border-radius: 4px; font-size: 12px; font-family: 'JetBrains Mono', monospace; }

/* Tablas */
#reportContent table { width: 100%; border-collapse: collapse; margin: 14px 0 20px; font-size: 12.5px; border: 1px solid var(--border); border-radius: 8px; overflow: hidden; }
#reportContent thead { background: rgba(42,46,57,0.6); }
#reportContent th { color: var(--text-muted); font-size: 11px; text-transform: uppercase; letter-spacing: .06em; padding: 10px 14px; text-align: left; border-bottom: 1px solid var(--border); font-weight: 700; white-space: nowrap; }
#reportContent td { padding: 9px 14px; border-bottom: 1px solid rgba(42,46,57,0.5); color: var(--text-primary); vertical-align: top; }
#reportContent tr:last-child td { border-bottom: none; }
#reportContent tr:hover td { background: rgba(42,46,57,0.3); }

/* Blockquotes */
#reportContent blockquote { border-left: 3px solid #f9a825; padding: 10px 16px; margin: 14px 0; background: rgba(249,168,37,0.06); border-radius: 0 6px 6px 0; }
#reportContent blockquote p { margin: 0; color: #f9a825; font-size: 12.5px; }

/* Colores semánticos en texto */
#reportContent td:contains("🟢"), #reportContent td:contains("↑") { color: var(--green); }
#reportContent td:contains("🔴"), #reportContent td:contains("↓") { color: var(--red); }

/* Print */
@media print {
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    #reportContent { color: #000 !important; }
    #reportContent h2 { background: #f5f5f5 !important; border-left-color: #2962ff !important; color: #000 !important; }
    #reportContent th, #reportContent td { color: #000 !important; border-color: #ddd !important; }
    #reportContent pre { background: #f5f5f5 !important; border-color: #ddd !important; }
    #reportContent pre code { color: #006600 !important; }
}
</style>
@endsection
