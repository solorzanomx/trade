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
Actúa como un trader institucional de primer nivel especializado en QQQ/Nasdaq, opciones 0DTE/1DTE y flujo de dinero inteligente.

Genera un reporte COMPLETO para HOY. USA SOLO datos reales y actualizados de hoy. Sé directo, accionable, sin relleno.

ESTRUCTURA OBLIGATORIA — respeta exactamente este orden y formato markdown:

---

## 🧭 BIAS DEL DÍA

**BIAS:** [ALCISTA 🟢 / BAJISTA 🔴 / NEUTRAL 🟡]
**Convicción:** [Alta / Media / Baja]
**Razón en 1 línea:** [máximo 20 palabras, el factor más importante de hoy]

---

## ⚡ RESUMEN EJECUTIVO (1 VISTAZO)

| Factor | Estado | Impacto QQQ |
|--------|--------|-------------|
| Futuros NQ | [valor] [%] | [↑↓→] |
| VIX | [valor] | [↑ malo / ↓ bueno] |
| Tasas 10Y | [valor]% | [↑ malo / ↓ bueno] |
| Smart Money | [Bullish/Bearish/Hedging] | [Alto/Medio] |
| Sentimiento retail | [Bullish/Bearish/Mixed] | [contrarian signal] |
| Evento del día | [nombre] | [hora CT] |

---

## 📅 AGENDA COMPLETA DEL DÍA

> ⚠️ TODO en horario Ciudad de México (CT). Si no hay eventos, indica "Sin eventos relevantes".

| Hora CT | Hora ET | Evento | Impacto | Qué esperar |
|---------|---------|--------|---------|-------------|
| 08:30 | 09:30 | Apertura de mercado | 🔴 Alto | Primer impulso |
| [hora] | [hora] | [evento macro] | [🔴/🟡/🟢] | [qué puede pasar] |

**Incluir obligatoriamente:**
- Datos macro USA (CPI, PPI, NFP, Jobless Claims, GDP, ISM, etc.)
- Discursos FED (Powell u otros) con sesgo hawkish/dovish
- Earnings relevantes (AAPL, NVDA, MSFT, AMZN, GOOGL, META, TSLA)
- Subastas de bonos
- Eventos geopolíticos que muevan mercados
- Vencimiento de opciones si aplica

---

## 🌍 MACRO & GEOPOLÍTICA

**Contexto global de hoy:**

### USA
- [noticia macro más importante]
- [segunda noticia]

### FED / Tasas
- US 10Y: [valor]% ([cambio])
- US 2Y: [valor]% ([cambio])
- [interpretación en 2 líneas: impacto en Nasdaq hoy]

### Internacional / Geopolítica
- [evento geopolítico relevante + impacto en mercados]
- [China/Europa/otros si son relevantes hoy]

---

## 📊 PREMARKET & FUTUROS

| Índice | Precio | Cambio % | Tendencia overnight | Nivel clave |
|--------|--------|----------|--------------------|-----------  |
| NQ (Nasdaq) | [precio] | [%] | [descripción] | [nivel] |
| ES (S&P 500) | [precio] | [%] | [descripción] | [nivel] |
| YM (Dow) | [precio] | [%] | [descripción] | [nivel] |
| VIX | [precio] | [%] | [descripción] | [nivel] |

**High premarket QQQ:** $[precio]
**Low premarket QQQ:** $[precio]
**Gap respecto al cierre:** [+/-]$ ([alcista/bajista])

---

## 📰 NOTICIAS QQQ — TOP HOLDINGS

> Las 10 principales posiciones de QQQ. Noticias de HOY que muevan el precio.

| Ticker | Peso QQQ | Noticia de hoy | Impacto |
|--------|----------|---------------|---------|
| NVDA | ~9% | [noticia] | [↑↓→] |
| AAPL | ~8% | [noticia] | [↑↓→] |
| MSFT | ~8% | [noticia] | [↑↓→] |
| AMZN | ~6% | [noticia] | [↑↓→] |
| GOOGL | ~5% | [noticia] | [↑↓→] |
| META | ~5% | [noticia] | [↑↓→] |
| TSLA | ~4% | [noticia] | [↑↓→] |
| AVGO | ~3% | [noticia] | [↑↓→] |
| COST | ~3% | [noticia] | [↑↓→] |
| NFLX | ~2% | [noticia] | [↑↓→] |

**Impacto neto estimado en QQQ:** [Positivo / Negativo / Neutro]

---

## 📈 NIVELES CLAVE QQQ

### Resistencias
| Nivel | Tipo | Por qué importa |
|-------|------|----------------|
| $[precio] | Resistencia fuerte | [razón] |
| $[precio] | Resistencia media | [razón] |
| $[precio] | Resistencia débil | [razón] |

### Soportes
| Nivel | Tipo | Por qué importa |
|-------|------|----------------|
| $[precio] | Soporte fuerte | [razón] |
| $[precio] | Soporte medio | [razón] |
| $[precio] | Soporte débil | [razón] |

### Niveles especiales
- **VWAP:** $[precio]
- **Gamma Flip:** $[precio] (precio donde los dealers cambian de posición)
- **Nivel psicológico:** $[precio]
- **High/Low semana anterior:** $[H] / $[L]

---

## ⚙️ VOLATILIDAD & OPCIONES

- **VIX:** [valor] → Régimen: [Low <15 / Normal 15-20 / High 20-25 / Extremo >25]
- **VIX9D:** [valor si disponible]
- **Put/Call Ratio QQQ:** [valor] → [interpretación]
- **Gamma Exposure:** [positivo/negativo] → [qué significa para el precio]

**Estrategia de opciones recomendada:**
- Si VIX < 15: Comprar opciones (prima barata)
- Si VIX 15-20: Spreads o scalp directional
- Si VIX > 20: Vender prima, iron condors, spreads

**Estrategia HOY:** [la específica según el VIX actual]

---

## 🐋 FLUJO INSTITUCIONAL

**Smart Money Bias:** [BULLISH / BEARISH / HEDGING]

### Órdenes relevantes (>$100K)
- [descripción de sweep o block trade relevante]
- [otro si existe]

### Dark Pools
- Niveles de concentración: $[precio], $[precio]
- Interpretación: [qué implica]

### Opciones
- Calls dominantes en: $[strike] exp [fecha]
- Puts dominantes en: $[strike] exp [fecha]
- Compras en [BID/ASK] → [señal alcista/bajista]

---

## 🔥 SENTIMIENTO

| Fuente | Sentimiento | Señal contrarian |
|--------|-------------|-----------------|
| Reddit/WSB | [Bullish/Bearish] [%] | [interpretación] |
| StockTwits | [Bullish/Bearish] | [nivel actividad] |
| Twitter/X | [narrativa dominante] | [implicación] |

**Conclusión sentimiento:** [Mixed / Extremo Bullish / Extremo Bearish]
> Si retail está extremadamente bullish → cuidado con largo. Si extremadamente bearish → posible rebote.

---

## ⏰ MAPA DEL DÍA (TIMING)

| Horario CT | Fase | Qué esperar | Estrategia |
|------------|------|-------------|------------|
| 08:30 – 09:00 | Apertura | Primer impulso / gap fill | Scalp, esperar dirección |
| 09:00 – 10:30 | Tendencia mañana | Movimiento más fuerte | Seguir tendencia |
| 10:30 – 12:00 | Chop / consolidación | Movimiento lento, trampas | Evitar o reducir tamaño |
| 12:00 – 14:00 | Media sesión | Volumen bajo | Solo si hay catalizador |
| 14:00 – 15:00 | Power Hour inicio | Aceleración | Momentum trades |
| 15:00 – 15:30 | Cierre | Último movimiento | Opciones 0DTE peligrosas |

**Mejor ventana hoy:** [horario específico CT basado en eventos del día]

---

## 🎯 ESCENARIOS DEL DÍA

### 🟢 ESCENARIO ALCISTA (probabilidad: [%])
- **Condición:** [qué tiene que pasar]
- **Nivel a superar:** $[precio]
- **Target 1:** $[precio] | **Target 2:** $[precio]
- **Catalizador:** [qué lo dispararía]

### 🔴 ESCENARIO BAJISTA (probabilidad: [%])
- **Condición:** [qué tiene que pasar]
- **Nivel a perder:** $[precio]
- **Target 1:** $[precio] | **Target 2:** $[precio]
- **Catalizador:** [qué lo dispararía]

### 🟡 ESCENARIO LATERAL (probabilidad: [%])
- **Rango esperado:** $[low] – $[high]
- **Estrategia:** [iron condor / vender theta / esperar ruptura]

---

## 🎯 PLAN DE TRADE

| Campo | Detalle |
|-------|---------|
| **Bias** | [Bullish/Bearish/Neutral] |
| **Setup** | [CALL / PUT / Bull Spread / Bear Spread] |
| **Strike** | $[precio] (ATM/OTM) |
| **Expiración** | [0DTE / 1DTE / fecha] |
| **Entrada ideal** | $[nivel QQQ] en [horario CT] |
| **Stop loss** | $[nivel QQQ] — perder: $[precio opción] |
| **Target** | $[nivel QQQ] — ganar: $[precio opción] |
| **R:R** | [ratio] |
| **Tamaño sugerido** | [% del capital o número de contratos] |
| **Mejor horario** | [ventana CT] |
| **Invalidación** | [qué evento o nivel cancela el trade] |

---

## 🚨 ALERTAS & RIESGOS

- **Mayor riesgo hoy:** [evento o nivel que puede arruinar el plan]
- **Error a evitar:** [el error más común en este contexto]
- **Si pasa X:** [plan B]

---

## 📋 RESUMEN FINAL (para copiar y pegar)

```
BIAS: [ALCISTA/BAJISTA/NEUTRAL] | VIX: [valor] | NQ: [%]
EVENTO CLAVE: [nombre] a las [hora CT]
SOPORTE: $[S1] / $[S2] | RESISTENCIA: $[R1] / $[R2]
PLAN: [CALL/PUT] $[strike] exp [fecha] | Entrada: $[nivel] | Stop: $[nivel] | Target: $[nivel]
MEJOR HORARIO: [ventana CT]
NO HACER: [error a evitar]
```

---
FORMATO CRÍTICO:
- Usa tablas markdown para todo lo que tenga múltiples campos
- Emojis al inicio de cada sección para escaneo visual rápido
- Números concretos siempre (no "alrededor de" ni "aproximadamente")
- Si no tienes el dato exacto, da el mejor estimado con una nota
- Horarios SIEMPRE en CT (Ciudad de México) primero, ET segundo
PROMPT;
    }
}
