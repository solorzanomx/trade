<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trade;
use App\Models\Asset;
use App\Models\TradeComment;
use App\Services\TradeMetricsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TradeApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $trades = $request->user()->trades()
            ->with('asset', 'comments')
            ->when($request->symbol, fn($q) => $q->where('symbol', strtoupper($request->symbol)))
            ->when($request->date_from, fn($q) => $q->whereDate('entry_date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('entry_date', '<=', $request->date_to))
            ->orderByDesc('entry_date')
            ->paginate(20);

        return response()->json($trades);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'symbol' => 'required|string|uppercase',
            'trade_type' => 'required|in:stock,call,put',
            'position_direction' => 'required|in:long,short',
            'entry_price' => 'required|numeric|min:0.01',
            'entry_date' => 'required|date',
            'entry_time' => 'nullable|date_format:H:i',
            'entry_reason' => 'nullable|string|max:1000',
            'quantity' => 'required|integer|min:1',
            'capital_used' => 'required|numeric|min:0.01',
            'stop_loss' => 'nullable|numeric|min:0.01',
            'take_profit' => 'nullable|numeric|min:0.01',
            'exit_price' => 'nullable|numeric|min:0.01',
            'exit_date' => 'nullable|date|after_or_equal:entry_date',
            'exit_time' => 'nullable|date_format:H:i',
            'exit_reason' => 'nullable|string|max:1000',
            'emotional_state' => 'nullable|string|max:100',
            'mistakes_made' => 'nullable|string|max:1000',
        ]);

        $validated['user_id'] = $request->user()->id;

        $trade = Trade::create($validated);
        if ($trade->exit_price) {
            $trade->calculatePnL();
            $trade->save();
            (new TradeMetricsService())->recalculateTradeMetrics($trade);
        }

        return response()->json($trade->load('asset', 'comments'), 201);
    }

    public function show(Request $request, Trade $trade): JsonResponse
    {
        $this->authorize('view', $trade);
        return response()->json($trade->load('asset', 'comments'));
    }

    public function update(Request $request, Trade $trade): JsonResponse
    {
        $this->authorize('update', $trade);

        $validated = $request->validate([
            'exit_price' => 'nullable|numeric|min:0.01',
            'exit_date' => 'nullable|date|after_or_equal:entry_date',
            'exit_time' => 'nullable|date_format:H:i',
            'exit_reason' => 'nullable|string|max:1000',
            'status' => 'nullable|in:open,closed',
            'emotional_state' => 'nullable|string|max:100',
            'mistakes_made' => 'nullable|string|max:1000',
        ]);

        $trade->update($validated);
        if ($trade->exit_price && $trade->exit_date) {
            $trade->calculatePnL();
            $trade->status = 'closed';
        }
        $trade->save();

        (new TradeMetricsService())->recalculateTradeMetrics($trade);

        return response()->json($trade->load('asset', 'comments'));
    }

    public function destroy(Request $request, Trade $trade): JsonResponse
    {
        $this->authorize('delete', $trade);
        $trade->delete();
        return response()->json(null, 204);
    }

    public function addComment(Request $request, Trade $trade): JsonResponse
    {
        $this->authorize('update', $trade);

        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        $comment = $trade->comments()->create($validated);
        return response()->json($comment, 201);
    }

    public function listAssets(Request $request): JsonResponse
    {
        $assets = $request->user()->assets()
            ->where('is_active', true)
            ->get();

        return response()->json($assets);
    }

    public function storeAsset(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'symbol' => 'required|string|uppercase|unique:assets,symbol,NULL,id,user_id,' . $request->user()->id,
            'name' => 'required|string|max:100',
            'asset_type' => 'required|in:stock,etf,crypto',
            'sector' => 'nullable|string|max:50',
        ]);

        $validated['user_id'] = $request->user()->id;
        $asset = Asset::create($validated);

        return response()->json($asset, 201);
    }

    public function destroyAsset(Request $request, Asset $asset): JsonResponse
    {
        $this->authorize('delete', $asset);
        $asset->delete();
        return response()->json(null, 204);
    }
}
