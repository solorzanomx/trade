<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyNewsSummary;
use App\Models\Asset;
use App\Services\NewsAggregationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class NewsApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset_id' => 'nullable|exists:assets,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $query = $request->user()->newsSummaries()
            ->with('asset')
            ->orderByDesc('date');

        if ($validated['asset_id'] ?? null) {
            $query->where('asset_id', $validated['asset_id']);
        }
        if ($validated['date_from'] ?? null) {
            $query->whereDate('date', '>=', $validated['date_from']);
        }
        if ($validated['date_to'] ?? null) {
            $query->whereDate('date', '<=', $validated['date_to']);
        }

        $summaries = $query->paginate(20);
        return response()->json($summaries);
    }

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'date' => 'nullable|date|before_or_equal:today',
        ]);

        $asset = Asset::findOrFail($validated['asset_id']);
        $this->authorize('update', $asset);

        $date = $validated['date'] ?? now()->subDay();

        $service = new NewsAggregationService();
        $summary = $service->generateNewsSummary($asset, Carbon::parse($date));

        return response()->json($summary, 201);
    }
}
