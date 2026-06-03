<?php

namespace App\Console\Commands;

use App\Models\DailyReport;
use App\Models\ReportTemplate;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateDailyReport extends Command
{
    protected $signature   = 'reports:generate {--template=} {--date=} {--user=}';
    protected $description = 'Genera el reporte diario de trading (QQQ u otro) usando Claude o Perplexity';

    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : now()->setTimezone('America/Mexico_City');

        // Solo lunes-viernes
        if ($date->isWeekend() && !$this->option('date')) {
            $this->info('Fin de semana — no se genera reporte.');
            return self::SUCCESS;
        }

        $query = ReportTemplate::where('is_active', true);

        if ($this->option('template')) {
            $query->where('slug', $this->option('template'));
        }
        if ($this->option('user')) {
            $query->where('user_id', $this->option('user'));
        }

        $templates = $query->get();

        if ($templates->isEmpty()) {
            $this->warn('No hay plantillas activas.');
            return self::SUCCESS;
        }

        foreach ($templates as $template) {
            $this->generateReport($template, $date);
        }

        return self::SUCCESS;
    }

    private function generateReport(ReportTemplate $template, Carbon $date): void
    {
        // Evitar duplicados
        $exists = DailyReport::where('user_id', $template->user_id)
            ->where('report_template_id', $template->id)
            ->whereDate('report_date', $date)
            ->exists();

        if ($exists) {
            $this->info("Ya existe reporte para {$template->name} — {$date->toDateString()}");
            return;
        }

        $this->info("Generando: {$template->name} para {$date->toDateString()}...");

        $prompt = $this->buildPrompt($template->prompt, $date);

        try {
            $content = $this->callClaude($prompt)
                ?? $this->callPerplexity($prompt)
                ?? null;

            if (!$content) {
                throw new \RuntimeException('Ninguna API respondió correctamente.');
            }

            DailyReport::create([
                'user_id'            => $template->user_id,
                'report_template_id' => $template->id,
                'report_date'        => $date->toDateString(),
                'content'            => $content,
                'source'             => 'claude',
                'status'             => 'generated',
            ]);

            $this->info("✓ Reporte generado para {$template->name}");

        } catch (\Throwable $e) {
            Log::error("Error generando reporte {$template->slug}: " . $e->getMessage());
            $this->error("Error: " . $e->getMessage());

            DailyReport::create([
                'user_id'            => $template->user_id,
                'report_template_id' => $template->id,
                'report_date'        => $date->toDateString(),
                'content'            => '',
                'source'             => 'claude',
                'status'             => 'error',
                'error_message'      => $e->getMessage(),
            ]);
        }
    }

    private function buildPrompt(string $template, Carbon $date): string
    {
        $dayName   = $date->locale('es')->isoFormat('dddd');
        $dateStr   = $date->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
        $etTime    = $date->copy()->setTimezone('America/New_York')->format('H:i');
        $cdmxTime  = $date->copy()->setTimezone('America/Mexico_City')->format('H:i');

        return "HOY es {$dayName}, {$dateStr}. Hora ET: {$etTime}, CDMX: {$cdmxTime}.\n\n" . $template;
    }

    private function callClaude(string $prompt): ?string
    {
        $key = config('services.anthropic.api_key');
        if (!$key) return null;

        try {
            $response = Http::timeout(120)
                ->withHeaders([
                    'x-api-key'         => $key,
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model'      => 'claude-opus-4-5',
                    'max_tokens' => 4096,
                    'messages'   => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            if ($response->successful()) {
                return $response->json('content.0.text');
            }

            Log::warning('Claude error: ' . $response->body());
        } catch (\Throwable $e) {
            Log::warning('Claude exception: ' . $e->getMessage());
        }

        return null;
    }

    private function callPerplexity(string $prompt): ?string
    {
        $key = config('services.perplexity.api_key');
        if (!$key) return null;

        try {
            $response = Http::timeout(120)
                ->withHeaders([
                    'Authorization' => "Bearer {$key}",
                    'Content-Type'  => 'application/json',
                ])
                ->post('https://api.perplexity.ai/chat/completions', [
                    'model'       => 'sonar-pro',
                    'messages'    => [
                        ['role' => 'system', 'content' => 'Eres un trader institucional experto. Responde siempre en español. Usa datos reales y actualizados de hoy.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.2,
                    'max_tokens'  => 4000,
                ]);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            Log::warning('Perplexity error: ' . $response->body());
        } catch (\Throwable $e) {
            Log::warning('Perplexity exception: ' . $e->getMessage());
        }

        return null;
    }
}
