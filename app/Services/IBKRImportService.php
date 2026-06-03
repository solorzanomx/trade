<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Trade;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class IBKRImportService
{
    private User $user;
    private array $results = [
        'imported'   => 0,
        'closed'     => 0,
        'skipped'    => 0,
        'duplicates' => 0,
        'errors'     => [],
        'trades'     => [],
    ];

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function import(string $xmlPath): array
    {
        if (!file_exists($xmlPath)) {
            $this->results['errors'][] = 'Archivo no encontrado: ' . $xmlPath;
            return $this->results;
        }

        $contents = file_get_contents($xmlPath);
        if (!$contents) {
            $this->results['errors'][] = 'No se pudo leer el archivo XML.';
            return $this->results;
        }

        // Desactivar errores externos de libxml para evitar warnings
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($contents);
        libxml_clear_errors();

        if (!$xml) {
            $this->results['errors'][] = 'XML inválido o formato incorrecto.';
            return $this->results;
        }

        // Recolectar todos los <Trade> de todos los <FlexStatement>
        $rawTrades = collect();
        foreach ($xml->FlexStatements->FlexStatement as $statement) {
            foreach ($statement->Trades->Trade as $trade) {
                $attrs = (array) $trade->attributes();
                $rawTrades->push($attrs['@attributes']);
            }
        }

        // Filtrar: solo STK y OPT en USD, ignorar CASH (forex) y trades vacíos
        $filtered = $rawTrades->filter(function ($t) {
            return in_array($t['assetCategory'], ['STK', 'OPT'])
                && $t['currency'] === 'USD'
                && !empty($t['symbol'])
                && !empty($t['ibOrderID'])
                && $t['transactionType'] === 'ExchTrade';
        });

        // Agrupar partial fills por ibOrderID
        $orders = $filtered->groupBy('ibOrderID');

        foreach ($orders as $orderId => $executions) {
            $this->processOrder($orderId, $executions);
        }

        return $this->results;
    }

    private function processOrder(string $orderId, Collection $executions): void
    {
        try {
            $first = $executions->first();

            // Determinar si es apertura o cierre
            // Algunas ejecuciones del mismo orden pueden mezclar O/C (cierre parcial + reapertura)
            // Usamos el indicador de la mayoría
            $indicators = $executions->pluck('openCloseIndicator');
            $isClose = $indicators->filter(fn($i) => str_contains($i, 'C'))->count()
                     > $indicators->filter(fn($i) => $i === 'O')->count();

            // Calcular totales del orden
            $totalQty   = $executions->sum(fn($e) => abs((float)$e['quantity']));
            $totalMoney = $executions->sum(fn($e) => abs((float)$e['tradeMoney']));
            $totalComm  = $executions->sum(fn($e) => abs((float)$e['ibCommission']));
            $totalPnl   = $executions->sum(fn($e) => (float)$e['fifoPnlRealized']);
            $avgPrice   = $totalQty > 0 ? $totalMoney / $totalQty : 0;

            // Parse fecha/hora del primer execution
            $dateTimeParts = explode(';', $first['dateTime']);
            $dateStr = $dateTimeParts[0]; // 20250602
            $timeStr = $dateTimeParts[1] ?? '000000'; // 101503

            $date = Carbon::createFromFormat('Ymd', $dateStr)->toDateString();
            $time = substr($timeStr, 0, 2) . ':' . substr($timeStr, 2, 2);

            // Tipo de trade
            $tradeType = $this->resolveTradeType($first);

            // Dirección
            $buySell   = strtoupper($first['buySell']);
            $direction = ($buySell === 'BUY') ? 'long' : 'short';

            // Verificar duplicado por ibkr_order_id
            $exists = Trade::where('user_id', $this->user->id)
                ->where('ibkr_order_id', $orderId)
                ->exists();

            if ($exists) {
                $this->results['duplicates']++;
                return;
            }

            if ($isClose) {
                $this->handleClose($orderId, $first, $avgPrice, $totalQty, $totalComm, $totalPnl, $date, $time, $tradeType);
            } else {
                $this->handleOpen($orderId, $first, $avgPrice, $totalQty, $totalComm, $date, $time, $tradeType, $direction);
            }

        } catch (\Throwable $e) {
            $this->results['errors'][] = "Orden {$orderId}: " . $e->getMessage();
        }
    }

    private function handleOpen(
        string $orderId, array $exec,
        float $avgPrice, float $qty, float $comm,
        string $date, string $time, string $tradeType, string $direction
    ): void {
        $asset = $this->ensureAsset($exec);

        $trade = Trade::create([
            'user_id'            => $this->user->id,
            'asset_id'           => $asset->id,
            'symbol'             => strtoupper($exec['symbol']),
            'trade_type'         => $tradeType,
            'position_direction' => $direction,
            'entry_price'        => round($avgPrice, 4),
            'entry_date'         => $date,
            'entry_time'         => $time,
            'quantity'           => $qty,
            'capital_used'       => round($avgPrice * $qty, 2),
            'commission'         => round($comm, 4),
            'status'             => 'open',
            'ibkr_order_id'      => $orderId,
            'strategy'           => 'IBKR Import',
            'entry_reason'       => $this->buildDescription($exec),
        ]);

        $this->results['imported']++;
        $this->results['trades'][] = [
            'action' => 'open',
            'symbol' => $trade->symbol,
            'type'   => $tradeType,
            'price'  => $avgPrice,
            'qty'    => $qty,
            'date'   => $date,
            'pnl'    => null,
        ];
    }

    private function handleClose(
        string $orderId, array $exec,
        float $avgPrice, float $qty, float $comm, float $pnl,
        string $date, string $time, string $tradeType
    ): void {
        $symbol = strtoupper($exec['symbol']);

        // Buscar el trade abierto más reciente para este símbolo
        $openTrade = Trade::where('user_id', $this->user->id)
            ->where('symbol', $symbol)
            ->where('status', 'open')
            ->whereNull('exit_price')
            ->orderByDesc('entry_date')
            ->first();

        if ($openTrade) {
            // Cerrar el trade existente
            $grossPnl = $pnl; // IBKR ya calcula el P&L FIFO real
            $netPnl   = $grossPnl - $comm;

            $openTrade->update([
                'exit_price'  => round($avgPrice, 4),
                'exit_date'   => $date,
                'exit_time'   => $time,
                'commission'  => round(($openTrade->commission ?? 0) + $comm, 4),
                'p_l'         => round($grossPnl, 2),
                'net_p_l'     => round($netPnl, 2),
                'p_l_percent' => $openTrade->capital_used > 0
                    ? round(min(9999.99, max(-9999.99, ($grossPnl / $openTrade->capital_used) * 100)), 4)
                    : 0,
                'status'         => 'closed',
                'ibkr_exec_id'   => $orderId,
            ]);

            // Recalcular métricas
            (new TradeMetricsService())->recalculateTradeMetrics($openTrade);

            $this->results['closed']++;
            $this->results['trades'][] = [
                'action' => 'close',
                'symbol' => $symbol,
                'type'   => $tradeType,
                'price'  => $avgPrice,
                'qty'    => $qty,
                'date'   => $date,
                'pnl'    => round($grossPnl, 2),
            ];

        } else {
            // No hay trade abierto — crear directamente como cerrado
            // Estimamos entry_price desde el P&L
            $asset = $this->ensureAsset($exec);

            $grossPnl = $pnl;
            $netPnl   = $grossPnl - $comm;

            // Para un short (SELL que cierra), la entry original era a precio más alto
            // No podemos reconstruir exactamente, así que usamos el precio de salida y el P&L
            Trade::create([
                'user_id'            => $this->user->id,
                'asset_id'           => $asset->id,
                'symbol'             => $symbol,
                'trade_type'         => $tradeType,
                'position_direction' => 'long', // asumimos long
                'entry_price'        => round($avgPrice, 4), // mejor estimado
                'entry_date'         => $date,
                'exit_price'         => round($avgPrice, 4),
                'exit_date'          => $date,
                'exit_time'          => $time,
                'quantity'           => $qty,
                'capital_used'       => round($avgPrice * $qty, 2),
                'commission'         => round($comm, 4),
                'p_l'                => round($grossPnl, 2),
                'net_p_l'            => round($netPnl, 2),
                'p_l_percent'        => 0,
                'status'             => 'closed',
                'ibkr_order_id'      => $orderId,
                'strategy'           => 'IBKR Import',
                'entry_reason'       => '[Sin apertura registrada] ' . $this->buildDescription($exec),
            ]);

            $this->results['imported']++;
            $this->results['trades'][] = [
                'action' => 'close (sin apertura)',
                'symbol' => $symbol,
                'type'   => $tradeType,
                'price'  => $avgPrice,
                'qty'    => $qty,
                'date'   => $date,
                'pnl'    => round($grossPnl, 2),
            ];
        }
    }

    private function resolveTradeType(array $exec): string
    {
        if ($exec['assetCategory'] === 'OPT') {
            return strtolower($exec['putCall']) === 'c' ? 'call' : 'put';
        }
        return 'stock';
    }

    private function buildDescription(array $exec): string
    {
        $parts = [strtoupper($exec['symbol']), $exec['description'] ?? ''];
        if (!empty($exec['strike']))  $parts[] = 'Strike: ' . $exec['strike'];
        if (!empty($exec['expiry']))  $parts[] = 'Exp: ' . $exec['expiry'];
        if (!empty($exec['exchange'])) $parts[] = 'Exchange: ' . $exec['exchange'];
        return implode(' | ', array_filter($parts));
    }

    private function ensureAsset(array $exec): Asset
    {
        $type = match($exec['assetCategory']) {
            'OPT'  => 'stock',
            default => strtolower($exec['subCategory'] ?? 'stock') === 'reit' ? 'etf' : 'stock',
        };

        return Asset::firstOrCreate(
            ['user_id' => $this->user->id, 'symbol' => strtoupper($exec['symbol'])],
            ['name' => $exec['description'] ?? $exec['symbol'], 'asset_type' => $type]
        );
    }
}
