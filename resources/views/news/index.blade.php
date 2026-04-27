@extends('layouts.app')

@section('title', 'News - Trading Journal')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold">Daily News Summaries</h1>
</div>

<div class="bg-white p-4 rounded shadow mb-6">
    <form method="GET" class="flex gap-4">
        <select name="asset_id" class="px-3 py-2 border rounded">
            <option value="">All Assets</option>
            @foreach ($assets as $asset)
                <option value="{{ $asset->id }}" {{ request('asset_id') == $asset->id ? 'selected' : '' }}>
                    {{ $asset->symbol }}
                </option>
            @endforeach
        </select>

        <input type="date" name="date_from" value="{{ request('date_from') }}" class="px-3 py-2 border rounded">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="px-3 py-2 border rounded">

        <button type="submit" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
            Filter
        </button>
    </form>
</div>

<div class="space-y-4">
    @forelse ($summaries as $summary)
        <div class="bg-white p-6 rounded shadow">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <h2 class="text-xl font-bold">{{ $summary->asset->symbol }}</h2>
                    <p class="text-sm text-gray-600">{{ $summary->date->format('M d, Y') }} • {{ ucfirst($summary->source) }}</p>
                </div>
                <span class="px-3 py-1 text-sm rounded {{ $summary->sentiment === 'positive' ? 'bg-green-100 text-green-800' : ($summary->sentiment === 'negative' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                    {{ ucfirst($summary->sentiment) }}
                </span>
            </div>

            <p class="text-gray-700 mb-3">{{ $summary->summary }}</p>

            @if ($summary->key_points)
                <div class="text-sm text-gray-600">
                    <strong>Key Points:</strong>
                    <ul class="list-disc list-inside">
                        @foreach ($summary->key_points as $point)
                            <li>{{ $point }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @empty
        <div class="bg-white p-6 rounded shadow text-center text-gray-500">
            No news summaries available
        </div>
    @endforelse
</div>

<div class="mt-6">
    {{ $summaries->links() }}
</div>
@endsection
