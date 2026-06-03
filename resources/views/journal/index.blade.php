@extends('layouts.app')
@section('title', 'Diario de Trading')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
    <div>
        <h1>📓 Diario de Trading</h1>
        <div class="text-muted" style="font-size:13px; margin-top:2px;">Bitácora diaria — pre-market plan + revisión post-mercado</div>
    </div>
    <div style="display:flex; gap:10px; align-items:center;">
        @if($journalStreak > 0)
        <div style="padding:8px 16px; background:rgba(249,168,37,0.1); border:1px solid rgba(249,168,37,0.3); border-radius:20px; font-size:13px; color:#f9a825; font-weight:700;">
            🔥 {{ $journalStreak }} día{{ $journalStreak > 1 ? 's' : '' }} de racha
        </div>
        @endif
        <a href="{{ route('journal.day', now()->setTimezone('America/Mexico_City')->toDateString()) }}"
           class="btn-primary" style="text-decoration:none;">✏️ Entrada de Hoy</a>
    </div>
</div>

@if($entries->isEmpty())
<div class="card" style="padding:60px; text-align:center;">
    <div style="font-size:56px; margin-bottom:16px;">📓</div>
    <div style="font-size:18px; font-weight:700; color:#fff; margin-bottom:8px;">Tu diario está vacío</div>
    <div class="text-muted" style="font-size:14px; margin-bottom:24px;">
        Los mejores traders llevan un diario. Empieza hoy.
    </div>
    <a href="{{ route('journal.day', now()->setTimezone('America/Mexico_City')->toDateString()) }}"
       class="btn-primary" style="text-decoration:none; padding:12px 32px; font-size:15px;">
        Escribir mi primera entrada
    </a>
</div>
@else

{{-- Timeline de entradas --}}
<div style="max-width:800px;">
    @foreach($entries as $entry)
    @php
        $gradeColor = match($entry->grade) {
            'A+','A' => 'var(--green)', 'B' => '#5b8af5',
            'C' => '#f9a825', 'D','F' => 'var(--red)', default => 'var(--text-muted)'
        };
        $biasIcon = match($entry->pre_bias) {
            'bullish' => '🟢', 'bearish' => '🔴', default => '🟡'
        };
    @endphp
    <a href="{{ route('journal.day', $entry->entry_date->toDateString()) }}"
       style="text-decoration:none; display:block; margin-bottom:10px;">
        <div class="card" style="padding:18px 24px; transition:border-color .15s;"
             onmouseover="this.style.borderColor='rgba(91,138,245,0.4)'"
             onmouseout="this.style.borderColor='var(--border)'">
            <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
                <div style="display:flex; align-items:center; gap:12px;">
                    {{-- Fecha --}}
                    <div style="text-align:center; width:48px; flex-shrink:0;">
                        <div style="font-size:22px; font-weight:800; color:#fff; line-height:1;">
                            {{ $entry->entry_date->format('d') }}
                        </div>
                        <div class="text-muted" style="font-size:11px; text-transform:uppercase;">
                            {{ $entry->entry_date->locale('es')->isoFormat('MMM') }}
                        </div>
                        <div class="text-muted" style="font-size:10px;">
                            {{ $entry->entry_date->locale('es')->isoFormat('ddd') }}
                        </div>
                    </div>
                    <div style="width:1px; height:48px; background:var(--border); flex-shrink:0;"></div>
                    {{-- Info --}}
                    <div>
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px; flex-wrap:wrap;">
                            @if($entry->pre_bias)
                            <span style="font-size:13px;">{{ $biasIcon }} {{ ucfirst($entry->pre_bias) }}</span>
                            @endif
                            @if($entry->grade)
                            <span style="font-size:12px; font-weight:800; padding:2px 8px; border-radius:4px;
                                background:{{ $gradeColor }}22; color:{{ $gradeColor }}; border:1px solid {{ $gradeColor }}44;">
                                {{ $entry->grade }}
                            </span>
                            @endif
                            @if($entry->followed_plan !== null)
                            <span style="font-size:11px; padding:2px 7px; border-radius:4px;
                                {{ $entry->followed_plan ? 'background:rgba(38,166,154,0.1); color:var(--green);' : 'background:rgba(239,83,80,0.1); color:var(--red);' }}">
                                {{ $entry->followed_plan ? '✓ Seguí el plan' : '✗ No seguí el plan' }}
                            </span>
                            @endif
                        </div>
                        @if($entry->what_went_well)
                        <div style="font-size:12px; color:var(--text-secondary); display:-webkit-box; -webkit-line-clamp:1; -webkit-box-orient:vertical; overflow:hidden; max-width:400px;">
                            {{ $entry->what_went_well }}
                        </div>
                        @elseif($entry->pre_plan)
                        <div style="font-size:12px; color:var(--text-secondary); display:-webkit-box; -webkit-line-clamp:1; -webkit-box-orient:vertical; overflow:hidden; max-width:400px;">
                            {{ $entry->pre_plan }}
                        </div>
                        @endif
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:16px; flex-shrink:0;">
                    @if($entry->mood_start || $entry->mood_end)
                    <div style="text-align:center;">
                        <div style="font-size:18px;">
                            @if($entry->mood_start) {{ $entry->getMoodEmoji($entry->mood_start) }} @endif
                            @if($entry->mood_start && $entry->mood_end) → @endif
                            @if($entry->mood_end) {{ $entry->getMoodEmoji($entry->mood_end) }} @endif
                        </div>
                        <div class="text-muted" style="font-size:10px;">Estado de ánimo</div>
                    </div>
                    @endif
                    <div style="color:#5b8af5; font-size:18px;">›</div>
                </div>
            </div>
            @if($entry->lesson_learned)
            <div style="margin-top:10px; padding:8px 12px; background:rgba(91,138,245,0.06); border-radius:6px; border-left:3px solid rgba(91,138,245,0.4); font-size:12px; color:var(--text-secondary);">
                💡 {{ Str::limit($entry->lesson_learned, 120) }}
            </div>
            @endif
        </div>
    </a>
    @endforeach

    <div style="margin-top:16px;">{{ $entries->links() }}</div>
</div>
@endif
@endsection
