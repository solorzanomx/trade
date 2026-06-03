@extends('layouts.app')
@section('title', 'Editar Prompt — ' . $template->name)

@section('content')
<div style="max-width:900px;">
    <div style="display:flex; align-items:center; gap:12px; margin-bottom:24px;">
        <a href="{{ route('reports.index') }}" style="color:var(--text-muted); text-decoration:none; font-size:13px;">← Reportes</a>
        <span class="text-muted">/</span>
        <h1>Editar Prompt — {{ $template->name }}</h1>
    </div>

    <form method="POST" action="{{ route('reports.update', $template) }}">
        @csrf @method('PUT')

        {{-- Config básica --}}
        <div class="card" style="padding:24px; margin-bottom:12px;">
            <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.1em; margin-bottom:18px; padding-bottom:12px; border-bottom:1px solid var(--border);">
                Configuración
            </div>
            <div style="display:grid; grid-template-columns:2fr 1fr 1fr; gap:16px; margin-bottom:16px;">
                <div>
                    <label>Nombre del reporte</label>
                    <input type="text" name="name" class="form-input" value="{{ old('name', $template->name) }}" required>
                </div>
                <div>
                    <label>Símbolo principal</label>
                    <input type="text" name="symbol" class="form-input" value="{{ old('symbol', $template->symbol) }}"
                           style="text-transform:uppercase;" placeholder="QQQ" required>
                </div>
                <div>
                    <label>Hora (CDMX)</label>
                    <input type="time" name="schedule_time" class="form-input"
                           value="{{ old('schedule_time', $template->schedule_time) }}" required>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:10px;">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" id="isActive"
                       {{ old('is_active', $template->is_active) ? 'checked' : '' }}
                       style="width:16px; height:16px; accent-color:var(--green);">
                <label for="isActive" style="text-transform:none; letter-spacing:0; font-size:13px; color:var(--text-primary); margin:0; cursor:pointer;">
                    Activo — se genera automáticamente cada día hábil
                </label>
            </div>
        </div>

        {{-- Editor del prompt --}}
        <div class="card" style="overflow:hidden; margin-bottom:20px;">
            <div style="padding:16px 20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <div style="font-size:13px; font-weight:700; color:#fff;">Prompt</div>
                    <div class="text-muted" style="font-size:12px; margin-top:2px;">
                        Este texto se envía a Claude/Perplexity cada mañana. Puedes modificarlo libremente.
                    </div>
                </div>
                <div style="display:flex; gap:8px;">
                    <button type="button" id="btnPreview" class="btn-ghost" style="font-size:12px; padding:6px 12px;">
                        👁 Preview
                    </button>
                    <button type="button" id="btnEdit" class="btn-ghost" style="font-size:12px; padding:6px 12px; display:none;">
                        ✏️ Editar
                    </button>
                </div>
            </div>

            {{-- Editor --}}
            <div id="editorPane" style="position:relative;">
                <textarea name="prompt" id="promptTextarea"
                          style="width:100%; min-height:600px; background:var(--bg-primary); color:var(--text-primary);
                                 border:none; padding:20px; font-family:'JetBrains Mono',monospace; font-size:13px;
                                 line-height:1.7; resize:vertical; outline:none; box-sizing:border-box;">{{ old('prompt', $template->prompt) }}</textarea>
                <div style="position:absolute; bottom:12px; right:16px; font-size:11px; color:var(--text-muted);" id="charCount"></div>
            </div>

            {{-- Preview --}}
            <div id="previewPane" style="display:none; padding:24px; max-width:none;" id="previewContent"></div>
        </div>

        <div style="display:flex; gap:12px;">
            <button type="submit" class="btn-primary" style="padding:10px 28px; font-size:14px;">Guardar cambios</button>
            <a href="{{ route('reports.index') }}" class="btn-ghost" style="text-decoration:none; padding:10px 20px; font-size:14px;">Cancelar</a>
        </div>
    </form>
</div>

<script>
const textarea = document.getElementById('promptTextarea');
const charCount = document.getElementById('charCount');
const editorPane = document.getElementById('editorPane');
const previewPane = document.getElementById('previewPane');
const btnPreview = document.getElementById('btnPreview');
const btnEdit    = document.getElementById('btnEdit');

// Char counter
function updateCount() {
    charCount.textContent = textarea.value.length.toLocaleString() + ' chars';
}
textarea.addEventListener('input', updateCount);
updateCount();

// Tab support en el textarea
textarea.addEventListener('keydown', function(e) {
    if (e.key === 'Tab') {
        e.preventDefault();
        const start = this.selectionStart;
        const end   = this.selectionEnd;
        this.value  = this.value.substring(0, start) + '  ' + this.value.substring(end);
        this.selectionStart = this.selectionEnd = start + 2;
    }
});

// Preview toggle — render markdown simple
btnPreview.addEventListener('click', function() {
    const text = textarea.value;
    // Markdown simple sin librerías
    let html = text
        .replace(/^### (.+)$/gm, '<h3>$1</h3>')
        .replace(/^## (.+)$/gm, '<h2>$1</h2>')
        .replace(/^# (.+)$/gm, '<h1>$1</h1>')
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.+?)\*/g, '<em>$1</em>')
        .replace(/^---$/gm, '<hr>')
        .replace(/^- (.+)$/gm, '<li>$1</li>')
        .replace(/(<li>.*<\/li>)/gs, '<ul>$1</ul>')
        .replace(/^👉 (.+)$/gm, '<blockquote>👉 $1</blockquote>')
        .replace(/\n\n/g, '</p><p>')
        .replace(/^(?!<[h|u|l|h|b|p])/gm, '');

    previewPane.innerHTML = '<div id="reportContent">' + html + '</div>';
    editorPane.style.display  = 'none';
    previewPane.style.display = 'block';
    btnPreview.style.display  = 'none';
    btnEdit.style.display     = 'inline-block';
});

btnEdit.addEventListener('click', function() {
    editorPane.style.display  = 'block';
    previewPane.style.display = 'none';
    btnPreview.style.display  = 'inline-block';
    btnEdit.style.display     = 'none';
});
</script>

<style>
#reportContent h1 { font-size:18px; font-weight:800; color:#fff; margin:20px 0 10px; border-bottom:1px solid var(--border); padding-bottom:8px; }
#reportContent h2 { font-size:15px; font-weight:700; color:#fff; margin:16px 0 8px; }
#reportContent h3 { font-size:13px; font-weight:700; color:#5b8af5; margin:14px 0 6px; text-transform:uppercase; }
#reportContent p  { font-size:13px; color:var(--text-primary); line-height:1.7; margin-bottom:10px; }
#reportContent ul { font-size:13px; color:var(--text-primary); padding-left:20px; margin-bottom:10px; }
#reportContent li { margin-bottom:4px; line-height:1.7; }
#reportContent strong { color:#fff; }
#reportContent em { color:#f9a825; font-style:normal; }
#reportContent hr { border:none; border-top:1px solid var(--border); margin:16px 0; }
#reportContent blockquote { border-left:3px solid #5b8af5; padding:8px 16px; margin:10px 0; background:rgba(91,138,245,0.06); border-radius:0 6px 6px 0; }
</style>
@endsection
