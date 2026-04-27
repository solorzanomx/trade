@extends('layouts.app')

@section('title', 'Edit Trade - Trading Journal')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">Edit Trade: {{ $trade->symbol }}</h1>

    <div class="bg-white p-6 rounded shadow">
        <form action="{{ route('trades.update', $trade) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Exit Price</label>
                    <input type="number" step="0.01" name="exit_price" placeholder="0.00" class="w-full px-3 py-2 border rounded" value="{{ old('exit_price', $trade->exit_price) }}">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Exit Date</label>
                    <input type="date" name="exit_date" class="w-full px-3 py-2 border rounded" value="{{ old('exit_date', $trade->exit_date) }}">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Emotional State</label>
                    <input type="text" name="emotional_state" placeholder="Calm, Anxious, etc." class="w-full px-3 py-2 border rounded" value="{{ old('emotional_state', $trade->emotional_state) }}">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Exit Reason</label>
                <textarea name="exit_reason" placeholder="Why did you exit?" class="w-full px-3 py-2 border rounded h-24">{{ old('exit_reason', $trade->exit_reason) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Mistakes Made</label>
                <textarea name="mistakes_made" placeholder="What went wrong?" class="w-full px-3 py-2 border rounded h-24">{{ old('mistakes_made', $trade->mistakes_made) }}</textarea>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Update Trade
                </button>
                <a href="{{ route('trades.show', $trade) }}" class="border px-6 py-2 rounded hover:bg-gray-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
