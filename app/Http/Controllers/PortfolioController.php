<?php

namespace App\Http\Controllers;

use App\Models\PortfolioPosition;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $open   = $user->portfolioPositions()->where('status', 'open')->orderBy('entry_date')->get();
        $closed = $user->portfolioPositions()->where('status', 'closed')->orderByDesc('exit_date')->get();

        // ── Métricas generales (posiciones abiertas) ──────────────────────────
        $totalCost  = $open->sum(fn($p) => $p->getCostBasis());
        $totalValue = $open->sum(fn($p) => $p->getCurrentValue());
        $totalPnl   = $open->sum(fn($p) => $p->getUnrealizedPnl());
        $totalDivs  = $open->sum('dividends_received');
        $returnPct  = $totalCost > 0 ? (($totalValue - $totalCost) / $totalCost) * 100 : 0;

        // ── Mejor y peor posición ─────────────────────────────────────────────
        $best  = $open->sortByDesc(fn($p) => $p->getReturnPercent())->first();
        $worst = $open->sortBy(fn($p) => $p->getReturnPercent())->first();

        // ── Allocación por posición (para gráfica) ────────────────────────────
        $allocation = $open->map(fn($p) => [
            'symbol' => $p->symbol,
            'value'  => round($p->getCurrentValue(), 2),
            'pct'    => $totalValue > 0 ? round($p->getCurrentValue() / $totalValue * 100, 1) : 0,
        ])->sortByDesc('value')->values();

        // ── Allocación por sector ─────────────────────────────────────────────
        $bySector = $open->groupBy(fn($p) => $p->sector ?: 'Sin sector')
            ->map(fn($positions, $sector) => [
                'sector' => $sector,
                'value'  => round($positions->sum(fn($p) => $p->getCurrentValue()), 2),
                'pct'    => $totalValue > 0
                    ? round($positions->sum(fn($p) => $p->getCurrentValue()) / $totalValue * 100, 1)
                    : 0,
            ])
            ->sortByDesc('value')
            ->values();

        // ── P&L de posiciones cerradas ────────────────────────────────────────
        $realizedPnl = $closed->sum(fn($p) => $p->getUnrealizedPnl());

        return view('portfolio.index', compact(
            'open', 'closed',
            'totalCost', 'totalValue', 'totalPnl', 'totalDivs', 'returnPct',
            'best', 'worst', 'allocation', 'bySector', 'realizedPnl'
        ));
    }

    public function create()
    {
        return view('portfolio.form', ['position' => null]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'symbol'              => 'required|string|max:20|uppercase',
            'name'                => 'nullable|string|max:255',
            'asset_type'          => 'required|in:stock,etf,crypto,bond,reit',
            'sector'              => 'nullable|string|max:100',
            'shares'              => 'required|numeric|min:0.000001',
            'avg_cost'            => 'required|numeric|min:0',
            'current_price'       => 'nullable|numeric|min:0',
            'target_price'        => 'nullable|numeric|min:0',
            'stop_loss'           => 'nullable|numeric|min:0',
            'dividends_received'  => 'nullable|numeric|min:0',
            'entry_date'          => 'required|date',
            'thesis'              => 'nullable|string',
            'notes'               => 'nullable|string',
        ]);

        $data['user_id'] = $request->user()->id;
        $data['dividends_received'] = $data['dividends_received'] ?? 0;

        PortfolioPosition::create($data);

        return redirect()->route('portfolio.index')->with('success', "Posición {$data['symbol']} agregada al portafolio.");
    }

    public function edit(PortfolioPosition $portfolioPosition)
    {
        $this->authorize('update', $portfolioPosition);
        return view('portfolio.form', ['position' => $portfolioPosition]);
    }

    public function update(Request $request, PortfolioPosition $portfolioPosition)
    {
        $this->authorize('update', $portfolioPosition);

        $data = $request->validate([
            'symbol'              => 'required|string|max:20',
            'name'                => 'nullable|string|max:255',
            'asset_type'          => 'required|in:stock,etf,crypto,bond,reit',
            'sector'              => 'nullable|string|max:100',
            'shares'              => 'required|numeric|min:0.000001',
            'avg_cost'            => 'required|numeric|min:0',
            'current_price'       => 'nullable|numeric|min:0',
            'target_price'        => 'nullable|numeric|min:0',
            'stop_loss'           => 'nullable|numeric|min:0',
            'dividends_received'  => 'nullable|numeric|min:0',
            'entry_date'          => 'required|date',
            'thesis'              => 'nullable|string',
            'notes'               => 'nullable|string',
            'status'              => 'required|in:open,closed',
            'exit_price'          => 'nullable|numeric|min:0',
            'exit_date'           => 'nullable|date',
        ]);

        $portfolioPosition->update($data);

        return redirect()->route('portfolio.index')->with('success', "Posición {$portfolioPosition->symbol} actualizada.");
    }

    public function destroy(PortfolioPosition $portfolioPosition)
    {
        $this->authorize('delete', $portfolioPosition);
        $symbol = $portfolioPosition->symbol;
        $portfolioPosition->delete();
        return redirect()->route('portfolio.index')->with('success', "Posición {$symbol} eliminada.");
    }

    // Actualización rápida de precio actual
    public function updatePrice(Request $request, PortfolioPosition $portfolioPosition)
    {
        $this->authorize('update', $portfolioPosition);
        $data = $request->validate(['current_price' => 'required|numeric|min:0']);
        $portfolioPosition->update($data);
        return back()->with('success', "{$portfolioPosition->symbol} actualizado a \${$data['current_price']}");
    }
}
