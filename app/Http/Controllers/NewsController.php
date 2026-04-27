<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $summaries = $request->user()->newsSummaries()
            ->with('asset')
            ->when($request->asset_id, fn($q) => $q->where('asset_id', $request->asset_id))
            ->when($request->date_from, fn($q) => $q->whereDate('date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->whereDate('date', '<=', $request->date_to))
            ->orderByDesc('date')
            ->paginate(15);

        $assets = $request->user()->assets()->where('is_active', true)->get();

        return view('news.index', compact('summaries', 'assets'));
    }
}
