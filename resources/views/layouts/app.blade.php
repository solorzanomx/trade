<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TradeLog')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        :root {
            --bg-primary: #0b0e11;
            --bg-card: #131722;
            --bg-hover: #1c2030;
            --border: #2a2e39;
            --text-primary: #d1d4dc;
            --text-secondary: #787b86;
            --text-muted: #4c525e;
            --green: #26a69a;
            --green-dim: rgba(38,166,154,0.15);
            --red: #ef5350;
            --red-dim: rgba(239,83,80,0.15);
            --blue: #2962ff;
            --blue-dim: rgba(41,98,255,0.15);
            --yellow: #f9a825;
        }
        body { background: var(--bg-primary); color: var(--text-primary); font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .card { background: var(--bg-card); border: 1px solid var(--border); border-radius: 8px; }
        .card-hover:hover { background: var(--bg-hover); }
        .text-green { color: var(--green); }
        .text-red { color: var(--red); }
        .text-muted { color: var(--text-secondary); }
        .badge-green { background: var(--green-dim); color: var(--green); border: 1px solid rgba(38,166,154,0.3); }
        .badge-red { background: var(--red-dim); color: var(--red); border: 1px solid rgba(239,83,80,0.3); }
        .badge-blue { background: var(--blue-dim); color: #5b8af5; border: 1px solid rgba(41,98,255,0.3); }
        .badge-yellow { background: rgba(249,168,37,0.15); color: var(--yellow); border: 1px solid rgba(249,168,37,0.3); }
        .btn-primary { background: var(--blue); color: #fff; border: none; border-radius: 6px; padding: 8px 16px; font-size: 13px; font-weight: 600; cursor: pointer; transition: opacity .15s; }
        .btn-primary:hover { opacity: .85; }
        .btn-ghost { background: transparent; color: var(--text-secondary); border: 1px solid var(--border); border-radius: 6px; padding: 8px 16px; font-size: 13px; cursor: pointer; transition: all .15s; }
        .btn-ghost:hover { color: var(--text-primary); border-color: #4a4f5e; }
        .btn-green { background: var(--green); color: #fff; border: none; border-radius: 6px; padding: 8px 16px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .form-input { background: var(--bg-primary); border: 1px solid var(--border); color: var(--text-primary); border-radius: 6px; padding: 8px 12px; font-size: 13px; width: 100%; outline: none; }
        .form-input:focus { border-color: var(--blue); }
        .form-input option { background: var(--bg-card); }
        table { border-collapse: collapse; width: 100%; }
        th { color: var(--text-secondary); font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; padding: 10px 16px; border-bottom: 1px solid var(--border); text-align: left; }
        td { padding: 12px 16px; border-bottom: 1px solid rgba(42,46,57,0.5); font-size: 13px; }
        tr:hover td { background: var(--bg-hover); }
        .nav-link { color: var(--text-secondary); font-size: 13px; font-weight: 500; padding: 6px 12px; border-radius: 6px; transition: all .15s; text-decoration: none; }
        .nav-link:hover, .nav-link.active { color: var(--text-primary); background: var(--bg-hover); }
        .alert-success { background: var(--green-dim); border: 1px solid rgba(38,166,154,0.3); color: var(--green); border-radius: 6px; padding: 12px 16px; margin-bottom: 16px; font-size: 13px; }
        .alert-error { background: var(--red-dim); border: 1px solid rgba(239,83,80,0.3); color: var(--red); border-radius: 6px; padding: 12px 16px; margin-bottom: 16px; font-size: 13px; }
        label { color: var(--text-secondary); font-size: 12px; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; display: block; margin-bottom: 6px; }
        h1 { font-size: 20px; font-weight: 700; color: var(--text-primary); }
        h2 { font-size: 16px; font-weight: 600; color: var(--text-primary); }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg-primary); }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
    </style>
</head>
<body>
    <div style="display:flex; min-height:100vh;">
        <!-- Sidebar -->
        <aside style="width:220px; min-height:100vh; background:var(--bg-card); border-right:1px solid var(--border); display:flex; flex-direction:column; padding:0; flex-shrink:0;">
            <div style="padding:20px 16px 16px; border-bottom:1px solid var(--border);">
                <a href="{{ route('dashboard') }}" style="text-decoration:none;">
                    <div style="font-size:16px; font-weight:800; color:#fff; letter-spacing:-.5px;">TradeLog</div>
                    <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">Options Journal</div>
                </a>
            </div>

            @auth
            <nav style="padding:8px 8px; flex:1; overflow-y:auto;">
                @php
                    $navLink = fn($route, $label, $icon, $routePattern = null) =>
                        '<a href="'.route($route).'" class="nav-link '.(request()->routeIs($routePattern ?? $route.'*') ? 'active' : '').'" style="display:flex;align-items:center;gap:8px;width:100%;margin-bottom:1px;">'.$icon.$label.'</a>';
                    $sec = fn($label) =>
                        '<div style="font-size:10px;color:var(--text-muted);font-weight:700;letter-spacing:.1em;text-transform:uppercase;padding:14px 8px 4px;">'.$label.'</div>';
                @endphp

                {{-- TRADING --}}
                <div style="font-size:10px; color:var(--text-muted); font-weight:700; letter-spacing:.1em; text-transform:uppercase; padding:8px 8px 4px;">Trading</div>

                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" style="display:flex; align-items:center; gap:8px; width:100%; margin-bottom:1px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M3 3h8v8H3V3zm10 0h8v8h-8V3zM3 13h8v8H3v-8zm10 5a4 4 0 108 0 4 4 0 00-8 0z"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('trades.index') }}" class="nav-link {{ request()->routeIs('trades.*') ? 'active' : '' }}" style="display:flex; align-items:center; gap:8px; width:100%; margin-bottom:1px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M3 17l4-8 4 4 4-6 4 4v5H3z"/></svg>
                    Operaciones
                </a>
                <a href="{{ route('trades.create') }}" class="nav-link" style="display:flex; align-items:center; gap:8px; width:100%; margin-bottom:1px; padding-left:28px; font-size:12px;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5v14M5 12h14"/></svg>
                    Nueva Operación
                </a>
                <a href="{{ route('metrics.index') }}" class="nav-link {{ request()->routeIs('metrics.*') ? 'active' : '' }}" style="display:flex; align-items:center; gap:8px; width:100%; margin-bottom:1px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    Métricas
                </a>

                {{-- BITÁCORA --}}
                <div style="font-size:10px; color:var(--text-muted); font-weight:700; letter-spacing:.1em; text-transform:uppercase; padding:14px 8px 4px;">Bitácora</div>

                <a href="{{ route('journal.index') }}" class="nav-link {{ request()->routeIs('journal.*') ? 'active' : '' }}" style="display:flex; align-items:center; gap:8px; width:100%; margin-bottom:1px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M4 19.5A2.5 2.5 0 016.5 17H20M4 19.5A2.5 2.5 0 014 17V5a2 2 0 012-2h12a2 2 0 012 2v12"/></svg>
                    Diario de Trading
                </a>
                <a href="{{ route('plan.index') }}" class="nav-link {{ request()->routeIs('plan.*') ? 'active' : '' }}" style="display:flex; align-items:center; gap:8px; width:100%; margin-bottom:1px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M9 11l3 3L22 4M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                    Plan de Trading
                </a>

                {{-- MERCADO --}}
                <div style="font-size:10px; color:var(--text-muted); font-weight:700; letter-spacing:.1em; text-transform:uppercase; padding:14px 8px 4px;">Mercado</div>

                <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" style="display:flex; align-items:center; gap:8px; width:100%; margin-bottom:1px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    Reporte QQQ
                </a>
                <a href="{{ route('news.index') }}" class="nav-link {{ request()->routeIs('news.*') ? 'active' : '' }}" style="display:flex; align-items:center; gap:8px; width:100%; margin-bottom:1px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/></svg>
                    Noticias
                </a>

                {{-- PORTAFOLIO --}}
                <div style="font-size:10px; color:var(--text-muted); font-weight:700; letter-spacing:.1em; text-transform:uppercase; padding:14px 8px 4px;">Portafolio</div>

                <a href="{{ route('portfolio.index') }}" class="nav-link {{ request()->routeIs('portfolio.*') ? 'active' : '' }}" style="display:flex; align-items:center; gap:8px; width:100%; margin-bottom:1px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M2 20h20M6 20V10M12 20V4M18 20v-6"/></svg>
                    Portafolio LP
                </a>

                {{-- HERRAMIENTAS --}}
                <div style="font-size:10px; color:var(--text-muted); font-weight:700; letter-spacing:.1em; text-transform:uppercase; padding:14px 8px 4px;">Importar</div>

                <a href="{{ route('ibkr.index') }}" class="nav-link {{ request()->routeIs('ibkr.*') ? 'active' : '' }}" style="display:flex; align-items:center; gap:8px; width:100%; margin-bottom:1px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    IBKR Import
                </a>
                <a href="{{ route('import.index') }}" class="nav-link {{ request()->routeIs('import.*') ? 'active' : '' }}" style="display:flex; align-items:center; gap:8px; width:100%; margin-bottom:1px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                    Importar CSV
                </a>
            </nav>

            <div style="padding:12px 16px; border-top:1px solid var(--border);">
                <div style="font-size:12px; color:var(--text-secondary); margin-bottom:4px;">{{ Auth::user()->name }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" style="font-size:11px; color:var(--text-muted); background:none; border:none; cursor:pointer; padding:0;">Cerrar sesión</button>
                </form>
            </div>
            @endauth
        </aside>

        <!-- Main Content -->
        <main style="flex:1; overflow:auto;">
            <div style="max-width:1400px; margin:0 auto; padding:24px 28px;">
                @if ($errors->any())
                    <div class="alert-error">
                        @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                    </div>
                @endif
                @if (session('success'))
                    <div class="alert-success">{{ session('success') }}</div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
