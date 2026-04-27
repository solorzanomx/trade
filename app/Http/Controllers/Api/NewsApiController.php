<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DailyNewsSummary;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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

        // Check if summary already exists
        $existing = DailyNewsSummary::where('user_id', $request->user()->id)
            ->where('asset_id', $asset->id)
            ->whereDate('date', $date)
            ->first();

        if ($existing) {
            return response()->json($existing, 200);
        }

        // TODO: Call Perplexity/Claude API to generate news summary
        // This is a placeholder - real implementation will use NewsAggregationService
        $summary = DailyNewsSummary::create([
            'user_id' => $request->user()->id,
            'asset_id' => $asset->id,
            'date' => $date,
            'summary' => 'News summary generation queued. Check back later.',
            'source' => 'manual',
            'sentiment' => 'neutral',
        ]);

        return response()->json($summary, 201);
    }
}
