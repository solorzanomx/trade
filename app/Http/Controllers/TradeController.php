<?php

namespace App\Http\Controllers;

use App\Models\Trade;
use App\Models\Asset;
use App\Services\TradeMetricsService;
use Illuminate\Http\Request;

class TradeController extends Controller
{
    public function index(Request $request)
    {
        $trades = $request->user()->trades()
            ->with('asset', 'comments')
            ->when($request->symbol, fn($q) => $q->where('symbol', strtoupper($request->symbol)))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('entry_date')
            ->paginate(20);

        $assets = $request->user()->assets()->where('is_active', true)->get();

        return view('trades.index', compact('trades', 'assets'));
    }

    public function create(Request $request)
    {
        $assets = $request->user()->assets()->where('is_active', true)->get();
        return view('trades.create', compact('assets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'symbol' => 'required|string|uppercase',
            'trade_type' => 'required|in:stock,call,put',
            'position_direction' => 'required|in:long,short',
            'entry_price' => 'required|numeric|min:0.01',
            'entry_date' => 'required|date',
            'entry_time' => 'nullable|date_format:H:i',
            'entry_reason' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
            'capital_used' => 'required|numeric|min:0.01',
            'stop_loss' => 'nullable|numeric',
            'take_profit' => 'nullable|numeric',
        ]);

        $validated['user_id'] = $request->user()->id;

        $asset = Asset::firstOrCreate(
            ['user_id' => $request->user()->id, 'symbol' => $validated['symbol']],
            ['name' => $validated['symbol'], 'asset_type' => 'stock']
        );

        $validated['asset_id'] = $asset->id;
        $trade = Trade::create($validated);

        return redirect()->route('trades.show', $trade)->with('success', 'Trade created successfully');
    }

    public function show(Trade $trade)
    {
        $this->authorize('view', $trade);
        $trade->load('asset', 'comments');
        return view('trades.show', compact('trade'));
    }

    public function edit(Trade $trade)
    {
        $this->authorize('update', $trade);
        $assets = $trade->user->assets()->where('is_active', true)->get();
        return view('trades.edit', compact('trade', 'assets'));
    }

    public function update(Request $request, Trade $trade)
    {
        $this->authorize('update', $trade);

        $validated = $request->validate([
            'exit_price' => 'nullable|numeric|min:0.01',
            'exit_date' => 'nullable|date|after_or_equal:entry_date',
            'exit_time' => 'nullable|date_format:H:i',
            'exit_reason' => 'nullable|string',
            'emotional_state' => 'nullable|string',
            'mistakes_made' => 'nullable|string',
        ]);

        $trade->update($validated);

        if ($trade->exit_price && $trade->exit_date) {
            $trade->calculatePnL();
            $trade->status = 'closed';
            $trade->save();
            (new TradeMetricsService())->recalculateTradeMetrics($trade);
        }

        return redirect()->route('trades.show', $trade)->with('success', 'Trade updated successfully');
    }

    public function destroy(Request $request, Trade $trade)
    {
        $this->authorize('delete', $trade);
        $trade->delete();
        return redirect()->route('trades.index')->with('success', 'Trade deleted successfully');
    }
}
