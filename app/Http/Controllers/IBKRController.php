<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use App\Services\IBKRImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IBKRController extends Controller
{
    const URL_SEND = 'https://gdcdyn.interactivebrokers.com/Universal/servlet/FlexStatementService.SendRequest';
    const URL_GET  = 'https://gdcdyn.interactivebrokers.com/Universal/servlet/FlexStatementService.GetStatement';

    public function index(Request $request)
    {
        $user = $request->user();

        // Stats rápidas
        $totalTrades  = Trade::where('user_id', $user->id)->count();
        $openTrades   = Trade::where('user_id', $user->id)->where('status', 'open')->count();
        $closedTrades = Trade::where('user_id', $user->id)->where('status', 'closed')->count();
        $lastTrade    = Trade::where('user_id', $user->id)->orderByDesc('entry_date')->first();

        $hasConfig = config('services.ibkr.flex_token') && config('services.ibkr.flex_query_id');

        return view('ibkr.index', compact('totalTrades', 'openTrades', 'closedTrades', 'lastTrade', 'hasConfig'));
    }

    /**
     * Sincronización manual desde el navegador vía IBKR Flex Query API.
     */
    public function sync(Request $request)
    {
        $token   = config('services.ibkr.flex_token');
        $queryId = config('services.ibkr.flex_query_id');

        if (!$token || !$queryId) {
            return back()->withErrors(['sync' => 'IBKR_FLEX_TOKEN o IBKR_FLEX_QUERY_ID no configurados en .env']);
        }

        // Paso 1: Solicitar reporte
        try {
            $resp1 = Http::timeout(30)->get(self::URL_SEND, ['t' => $token, 'q' => $queryId, 'v' => '3']);
            $xml1  = simplexml_load_string($resp1->body());

            if (!$xml1 || (string)($xml1->Status ?? '') !== 'Success') {
                $msg = (string)($xml1->ErrorMessage ?? $resp1->body());
                return back()->withErrors(['sync' => 'IBKR rechazó la solicitud: ' . $msg]);
            }

            $refCode = (string)$xml1->ReferenceCode;
        } catch (\Throwable $e) {
            return back()->withErrors(['sync' => 'Error al contactar IBKR: ' . $e->getMessage()]);
        }

        // Paso 2: Obtener XML (retry con espera)
        $xmlBody = null;
        for ($i = 0; $i < 6; $i++) {
            sleep($i === 0 ? 3 : 4);
            try {
                $resp2 = Http::timeout(60)->get(self::URL_GET, ['t' => $token, 'q' => $refCode, 'v' => '3']);
                $body  = $resp2->body();
                if (str_contains($body, '<FlexQueryResponse') || str_contains($body, '<FlexStatements')) {
                    $xmlBody = $body;
                    break;
                }
            } catch (\Throwable $e) {
                Log::warning('IBKR sync web attempt ' . ($i+1) . ': ' . $e->getMessage());
            }
        }

        if (!$xmlBody) {
            return back()->withErrors(['sync' => 'IBKR no devolvió el reporte después de varios intentos. Inténtalo en 1-2 minutos.']);
        }

        // Paso 3: Guardar e importar
        $dir  = storage_path('app/ibkr_temp');
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        $path = $dir . '/ibkr_web_' . now()->format('YmdHis') . '.xml';
        file_put_contents($path, $xmlBody);

        $service = new IBKRImportService($request->user());
        $results = $service->import($path);

        if (file_exists($path)) unlink($path);

        return redirect()->route('ibkr.index')->with('ibkr_results', $results);
    }

    public function import(Request $request)
    {
        $request->validate([
            'xml_file' => 'required|file|max:51200',
        ]);

        // Guardar con nombre fijo y extensión .xml
        $dir      = storage_path('app/ibkr_temp');
        $filename = Str::random(32) . '.xml';
        $fullPath = $dir . '/' . $filename;

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Mover el archivo uploadado directamente
        $request->file('xml_file')->move($dir, $filename);

        if (!file_exists($fullPath)) {
            return back()->withErrors(['xml_file' => 'No se pudo guardar el archivo en el servidor.']);
        }

        $service = new IBKRImportService($request->user());
        $results = $service->import($fullPath);

        // Limpiar el archivo temporal
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        return redirect()->route('ibkr.index')->with('ibkr_results', $results);
    }
}
