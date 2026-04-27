<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Trade;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ImportController extends Controller
{
    public function index()
    {
        return view('import.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $rows = array_map('str_getcsv', file($file->getRealPath()));

        $user = $request->user();
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            // Skip empty rows or header rows
            if (count($row) < 7) continue;

            // Skip rows where fecha (column 1) is empty or not a date
            $fechaRaw = trim($row[1] ?? '');
            if (empty($fechaRaw) || !$this->isValidDate($fechaRaw)) continue;

            // Skip rows where symbol (column 2) is empty
            $symbol = strtoupper(trim($row[2] ?? ''));
            if (empty($symbol)) continue;

            try {
                $fecha = Carbon::parse($fechaRaw);
                $tipo = strtolower(trim($row[3] ?? 'stock')); // Call/Put
                $precioCompra = $this->parsePrice($row[4] ?? '0');
                $contratos = (int) trim($row[5] ?? '1');
                $precioVenta = $this->parsePrice($row[6] ?? '0');
                $comision = $this->parsePrice($row[9] ?? '0');
                $estrategia = trim($row[10] ?? '');
                $notas = trim($row[11] ?? '');

                if ($precioCompra <= 0) continue;

                // Determine trade type
                $tradeType = match(true) {
                    str_contains(strtolower($tipo), 'call') => 'call',
                    str_contains(strtolower($tipo), 'put') => 'put',
                    default => 'stock',
                };

                // Get or create asset
                $asset = Asset::firstOrCreate(
                    ['user_id' => $user->id, 'symbol' => $symbol],
                    [
                        'name' => $symbol,
                        'asset_type' => in_array($tradeType, ['call', 'put']) ? 'option' : 'stock',
                        'is_active' => true,
                    ]
                );

                $trade = Trade::create([
                    'user_id'            => $user->id,
                    'asset_id'           => $asset->id,
                    'symbol'             => $symbol,
                    'trade_type'         => $tradeType,
                    'position_direction' => 'long',
                    'entry_price'        => $precioCompra,
                    'entry_date'         => $fecha,
                    'exit_price'         => $precioVenta > 0 ? $precioVenta : null,
                    'exit_date'          => $precioVenta > 0 ? $fecha : null,
                    'quantity'           => max(1, $contratos),
                    'capital_used'       => $precioCompra * max(1, $contratos),
                    'commission'         => abs($comision),
                    'strategy'           => $estrategia ?: null,
                    'entry_reason'       => $notas ?: null,
                    'status'             => $precioVenta > 0 ? 'closed' : 'open',
                ]);

                $trade->calculatePnL();
                $trade->save();

                $imported++;
            } catch (\Exception $e) {
                $skipped++;
                continue;
            }
        }

        return redirect()->route('trades.index')
            ->with('success', "Importación completada: {$imported} trades importados, {$skipped} filas omitidas.");
    }

    private function isValidDate(string $value): bool
    {
        try {
            Carbon::parse($value);
            return preg_match('/\d/', $value) === 1;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function parsePrice(string $value): float
    {
        // Remove $, commas, spaces, parentheses (negative)
        $clean = preg_replace('/[^0-9.\-]/', '', str_replace(['(', ')'], ['-', ''], $value));
        return (float) $clean;
    }
}
