@extends('layouts.app')

@section('title', 'Create Trade - Trading Journal')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-3xl font-bold mb-6">Log New Trade</h1>

    <div class="bg-white p-6 rounded shadow">
        <form action="{{ route('trades.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Symbol</label>
                    <input type="text" name="symbol" placeholder="AAPL" class="w-full px-3 py-2 border rounded" value="{{ old('symbol') }}" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Trade Type</label>
                    <select name="trade_type" class="w-full px-3 py-2 border rounded" required>
                        <option value="stock">Stock</option>
                        <option value="call">Call Option</option>
                        <option value="put">Put Option</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Position</label>
                    <select name="position_direction" class="w-full px-3 py-2 border rounded" required>
                        <option value="long">Long</option>
                        <option value="short">Short</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Quantity</label>
                    <input type="number" name="quantity" placeholder="100" class="w-full px-3 py-2 border rounded" value="{{ old('quantity') }}" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Entry Price</label>
                    <input type="number" step="0.01" name="entry_price" placeholder="0.00" class="w-full px-3 py-2 border rounded" value="{{ old('entry_price') }}" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Entry Date</label>
                    <input type="date" name="entry_date" class="w-full px-3 py-2 border rounded" value="{{ old('entry_date') }}" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Capital Used</label>
                    <input type="number" step="0.01" name="capital_used" placeholder="0.00" class="w-full px-3 py-2 border rounded" value="{{ old('capital_used') }}" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Stop Loss</label>
                    <input type="number" step="0.01" name="stop_loss" placeholder="0.00" class="w-full px-3 py-2 border rounded" value="{{ old('stop_loss') }}">
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-1">Take Profit</label>
                    <input type="number" step="0.01" name="take_profit" placeholder="0.00" class="w-full px-3 py-2 border rounded" value="{{ old('take_profit') }}">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-1">Entry Reason</label>
                <textarea name="entry_reason" placeholder="Why did you enter this trade?" class="w-full px-3 py-2 border rounded h-24">{{ old('entry_reason') }}</textarea>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Create Trade
                </button>
                <a href="{{ route('trades.index') }}" class="border px-6 py-2 rounded hover:bg-gray-50">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
