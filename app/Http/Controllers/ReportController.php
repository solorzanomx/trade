<?php

namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\ReportTemplate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    // Vista principal: último reporte + historial
    public function index(Request $request)
    {
        $user      = $request->user();
        $templates = $user->reportTemplates()->with('latestReport')->get();
        $template  = $templates->first();

        $report = null;
        $reports = collect();

        if ($template) {
            $reportId = $request->input('report');
            $report   = $reportId
                ? $user->dailyReports()->with('template')->find($reportId)
                : $template->latestReport;

            $reports = $user->dailyReports()
                ->where('report_template_id', $template->id)
                ->orderByDesc('report_date')
                ->take(30)
                ->get();
        }

        return view('reports.index', compact('templates', 'template', 'report', 'reports'));
    }

    // Editor del prompt
    public function edit(Request $request, ReportTemplate $reportTemplate)
    {
        $this->authorize('update', $reportTemplate);
        return view('reports.edit', ['template' => $reportTemplate]);
    }

    public function update(Request $request, ReportTemplate $reportTemplate)
    {
        $this->authorize('update', $reportTemplate);

        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'prompt'        => 'required|string',
            'symbol'        => 'required|string|max:20',
            'schedule_time' => 'required|date_format:H:i',
            'is_active'     => 'boolean',
        ]);

        $reportTemplate->update($data);

        return redirect()->route('reports.index')
            ->with('success', 'Plantilla actualizada correctamente.');
    }

    // Generar manualmente
    public function generate(Request $request, ReportTemplate $reportTemplate)
    {
        $this->authorize('update', $reportTemplate);

        $date = $request->input('date', now()->toDateString());

        \Artisan::call('reports:generate', [
            '--template' => $reportTemplate->slug,
            '--date'     => $date,
            '--user'     => $request->user()->id,
        ]);

        $output = \Artisan::output();

        return redirect()->route('reports.index')
            ->with('success', 'Reporte generado. ' . trim($output));
    }

    // Crear plantilla inicial (solo si no existe)
    public function setup(Request $request)
    {
        $user = $request->user();

        if ($user->reportTemplates()->exists()) {
            return redirect()->route('reports.index');
        }

        $template = $user->reportTemplates()->create([
            'name'          => 'Reporte QQQ',
            'slug'          => 'qqq-daily-' . $user->id,
            'symbol'        => 'QQQ',
            'schedule_time' => '09:35',
            'is_active'     => true,
            'prompt'        => $this->defaultPrompt(),
        ]);

        return redirect()->route('reports.edit', $template)
            ->with('success', 'Plantilla creada. Edita el prompt si lo deseas.');
    }

    private function defaultPrompt(): string
    {
        return <<<'PROMPT'
# REPORTE INTRADÍA PROFESIONAL – OPCIONES QQQ (DAY TRADING)

Actúa como un trader institucional experto en Nasdaq (QQQ) y opciones (0DTE / 1DTE), especializado en flujo de dinero inteligente (smart money), gamma y trading intradía.

Tu objetivo es generar una VENTAJA OPERATIVA para HOY.

⚠️ REGLAS:
- Usa SOLO datos actualizados del día
- Prioriza lo que impacta HOY (no análisis histórico innecesario)
- Sé directo, accionable y sin relleno
- Piensa como hedge fund, no como analista retail

---

# 1. 📅 AGENDA DEL DÍA (CRÍTICO)

Busca TODOS los eventos relevantes de HOY:

### Macro (USA)
- CPI, PPI, NFP, Jobless Claims, GDP, etc.
- Hora en Nueva York (ET) y Ciudad de México (CT)
- Impacto: Alto / Medio / Bajo

### FED
- Discursos (Powell u otros miembros)
- Hora exacta ET y CT
- Sesgo del miembro (hawkish/dovish)

### Otros catalizadores
- Subastas de bonos
- Earnings importantes (especialmente tech: AAPL, NVDA, MSFT, AMZN)

👉 Entregar en tabla clara con HORARIOS

---

# 2. 🌎 CONTEXTO PREMARKET

Analiza:
- Nasdaq Futures (NQ)
- S&P 500 Futures (ES)
- Dow Futures (YM)

Para cada uno: % cambio, tendencia overnight, nivel clave actual.

👉 Determina: Market Bias inicial: Bullish / Bearish / Neutral

---

# 3. ⚡ VOLATILIDAD

- VIX actual
- VIX9D (si disponible)

👉 Determina régimen de volatilidad y estrategia ideal:
- Low IV → comprar opciones
- High IV → spreads / vender prima

---

# 4. 💵 TASAS (CLAVE PARA NASDAQ)

- US 10Y Yield
- US 2Y Yield

👉 Explica en máximo 3 líneas el impacto directo HOY en QQQ

---

# 5. 🐋 FLUJO INSTITUCIONAL (SMART MONEY)

Analiza: Options Flow (QQQ / SPY), Sweep orders, Block trades, Dark Pools, Put/Call Ratio.

Reporta:
- Órdenes relevantes (>100K USD)
- Calls vs Puts dominantes
- Si compras fueron en BID o ASK
- Niveles de dark pool

👉 Smart Money Bias: Bullish / Bearish / Hedging — Convicción: Alta / Media / Baja

---

# 6. 📊 NIVELES CLAVE QQQ (INTRADÍA)

- Soportes (mínimo 3)
- Resistencias (mínimo 3)
- VWAP
- High / Low premarket
- Niveles psicológicos

👉 Marca zonas de reacción fuerte

---

# 7. ⚙️ GAMMA + OPCIONES

- Gamma Exposure
- Gamma Flip Level
- Open Interest relevante

👉 Zonas donde el precio acelera y zonas donde se frena

---

# 8. 🔥 SENTIMIENTO SOCIAL (RETAIL)

Reddit/WSB, StockTwits, Twitter/X — volumen de menciones, narrativa dominante.

👉 Retail Sentiment: Bullish / Bearish / Mixed
👉 Interpretación: retail muy bullish → posible caída; retail muy bearish → posible rebote

---

# 9. 📈 ESCENARIOS DEL DÍA

### 🟢 Alcista — Condición, nivel a romper, target
### 🔴 Bajista — Nivel a perder, targets
### 🟡 Lateral — Rango esperado, estrategia

---

# 10. 🎯 PLAN DE TRADE (QQQ OPCIONES)

| Campo | Detalle |
|-------|---------|
| Bias | Bullish / Bearish / Neutral |
| Setup | CALL / PUT / Spread |
| Strike | ATM o cercano |
| Expiración | 0DTE / 1DTE |
| Entrada | Nivel de QQQ |
| Stop | Claro |
| Target | Claro |
| Mejor horario | Apertura / Media sesión / Power Hour |

---

# 11. ⏰ TIMING DEL MERCADO

- Apertura (9:30 ET / 8:30 CDMX)
- Primer impulso
- Zona de chop (mediodía)
- Power Hour

👉 Dónde hay mayor probabilidad de movimiento hoy

---

# 12. 🚨 ALERTAS DE FLUJO

- Sweep masivo en un strike
- Volumen anormal
- Nivel imán (price magnet)

👉 Señala posibles movimientos explosivos

---

# 13. 🧾 RESUMEN EJECUTIVO

Máximo 8 bullets:
- Bias del día
- Nivel clave
- Evento más importante
- Qué están haciendo las instituciones
- Estrategia recomendada
- Mayor riesgo
- Error a evitar

---

# FORMATO
- Claro, directo, sin relleno
- Usar emojis para escaneo rápido
- Enfocado en ejecución inmediata
PROMPT;
    }
}
