<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Services\NewsAggregationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $summaries = $request->user()->newsSummaries()
            ->with('asset')
            ->when($request->asset_id, fn($q) => $q->where('asset_id', $request->asset_id))
            ->when($request->date_from, fn($q) => $q->whereDate('date', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->whereDate('date', '<=', $request->date_to))
            ->orderByDesc('date')
            ->paginate(20);

        $assets = $request->user()->assets()->where('is_active', true)->orderBy('symbol')->get();

        // Assets que el usuario tiene en trades (para sugerir en el formulario)
        $tradedSymbols = $request->user()->trades()
            ->select('symbol')
            ->distinct()
            ->pluck('symbol');

        return view('news.index', compact('summaries', 'assets', 'tradedSymbols'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'symbol' => 'required|string|max:20',
            'date'   => 'required|date',
        ]);

        $user   = $request->user();
        $symbol = strtoupper($request->symbol);
        $date   = Carbon::parse($request->date);

        // Buscar o crear el asset
        $asset = Asset::firstOrCreate(
            ['user_id' => $user->id, 'symbol' => $symbol],
            ['name' => $symbol, 'asset_type' => 'stock', 'is_active' => true]
        );

        try {
            $service = new NewsAggregationService();
            $summary = $service->generateNewsSummary($asset, $date);

            return redirect()->route('news.index')
                ->with('success', "Resumen generado para {$symbol} — {$date->format('d/m/Y')}");
        } catch (\Exception $e) {
            return back()->withErrors(['generate' => 'Error al generar: ' . $e->getMessage()]);
        }
    }

    public function destroy(Request $request, $id)
    {
        $summary = $request->user()->newsSummaries()->findOrFail($id);
        $summary->delete();
        return back()->with('success', 'Resumen eliminado.');
    }
}
