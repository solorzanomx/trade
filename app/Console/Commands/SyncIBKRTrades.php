<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\IBKRImportService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncIBKRTrades extends Command
{
    protected $signature   = 'ibkr:sync {--user=} {--dry-run}';
    protected $description = 'Sincroniza trades automáticamente desde IBKR Flex Query URL';

    // Endpoints de IBKR Flex Query
    const URL_SEND   = 'https://gdcdyn.interactivebrokers.com/Universal/servlet/FlexStatementService.SendRequest';
    const URL_GET    = 'https://gdcdyn.interactivebrokers.com/Universal/servlet/FlexStatementService.GetStatement';

    public function handle(): int
    {
        $token   = config('services.ibkr.flex_token');
        $queryId = config('services.ibkr.flex_query_id');

        if (!$token || !$queryId) {
            $this->warn('IBKR_FLEX_TOKEN o IBKR_FLEX_QUERY_ID no configurados en .env');
            return self::FAILURE;
        }

        // Solo en días hábiles (a menos que se fuerce)
        if (now()->isWeekend() && !$this->option('dry-run')) {
            $this->info('Fin de semana — sin sincronización.');
            return self::SUCCESS;
        }

        $this->info('Solicitando reporte a IBKR...');

        // ── Paso 1: Solicitar el reporte ──────────────────────────────────────
        $refCode = $this->requestReport($token, $queryId);
        if (!$refCode) {
            $this->error('No se pudo obtener el código de referencia de IBKR.');
            return self::FAILURE;
        }

        $this->info("Código de referencia: {$refCode} — esperando 3 segundos...");
        sleep(3);

        // ── Paso 2: Obtener el XML ────────────────────────────────────────────
        $xml = $this->fetchReport($token, $refCode);
        if (!$xml) {
            $this->error('No se pudo obtener el XML del reporte.');
            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->info('DRY RUN — XML obtenido correctamente. No se importarán datos.');
            return self::SUCCESS;
        }

        // ── Paso 3: Guardar XML temporal e importar ───────────────────────────
        $dir      = storage_path('app/ibkr_temp');
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $filename = 'ibkr_auto_' . now()->format('YmdHis') . '.xml';
        $path     = $dir . '/' . $filename;
        file_put_contents($path, $xml);

        // Importar para cada usuario configurado
        $users = $this->option('user')
            ? User::where('id', $this->option('user'))->get()
            : User::all();

        $totalImported = 0;
        $totalClosed   = 0;

        foreach ($users as $user) {
            $service = new IBKRImportService($user);
            $results = $service->import($path);

            $totalImported += $results['imported'];
            $totalClosed   += $results['closed'];

            $this->info("Usuario {$user->email}: {$results['imported']} nuevas, {$results['closed']} cerradas, {$results['duplicates']} duplicados.");

            if (!empty($results['errors'])) {
                foreach ($results['errors'] as $err) {
                    $this->warn("  Error: {$err}");
                    Log::warning("IBKR Sync error [{$user->email}]: {$err}");
                }
            }
        }

        // Limpiar archivo temporal
        if (file_exists($path)) unlink($path);

        $this->info("✓ Sincronización completada: {$totalImported} nuevas operaciones, {$totalClosed} cerradas.");

        return self::SUCCESS;
    }

    private function requestReport(string $token, string $queryId): ?string
    {
        try {
            $response = Http::timeout(30)->get(self::URL_SEND, [
                't' => $token,
                'q' => $queryId,
                'v' => '3',
            ]);

            if (!$response->successful()) {
                Log::error('IBKR SendRequest failed: ' . $response->body());
                return null;
            }

            $xml = simplexml_load_string($response->body());

            if (!$xml) {
                Log::error('IBKR: respuesta XML inválida en SendRequest');
                return null;
            }

            $status = (string)($xml->Status ?? '');
            if ($status !== 'Success') {
                Log::error('IBKR SendRequest status: ' . $status . ' — ' . ($xml->ErrorMessage ?? ''));
                $this->warn('IBKR respondió: ' . $status . ' — ' . ($xml->ErrorMessage ?? ''));
                return null;
            }

            return (string)$xml->ReferenceCode;

        } catch (\Throwable $e) {
            Log::error('IBKR SendRequest exception: ' . $e->getMessage());
            return null;
        }
    }

    private function fetchReport(string $token, string $refCode): ?string
    {
        $maxAttempts = 5;

        for ($i = 0; $i < $maxAttempts; $i++) {
            try {
                $response = Http::timeout(60)->get(self::URL_GET, [
                    't' => $token,
                    'q' => $refCode,
                    'v' => '3',
                ]);

                if (!$response->successful()) continue;

                $body = $response->body();

                // Si es un XML de estado (aún procesando), esperar
                if (str_contains($body, '<Status>')) {
                    $xml    = simplexml_load_string($body);
                    $status = (string)($xml->Status ?? '');

                    if ($status === 'Success') {
                        // El reporte está listo pero es el XML de respuesta, no el reporte
                        // Intentar de nuevo
                        $this->info("Reporte aún procesando... intento " . ($i + 1));
                        sleep(4);
                        continue;
                    }
                }

                // Verificar que sea un Flex Query Report válido
                if (str_contains($body, '<FlexQueryResponse') || str_contains($body, '<FlexStatements')) {
                    return $body;
                }

                sleep(4);

            } catch (\Throwable $e) {
                Log::warning('IBKR GetStatement attempt ' . ($i+1) . ': ' . $e->getMessage());
                sleep(4);
            }
        }

        return null;
    }
}
