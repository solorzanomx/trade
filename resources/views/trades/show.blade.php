@extends('layouts.app')

@section('title', $trade->symbol . ' - Trading Journal')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">{{ $trade->symbol }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('trades.edit', $trade) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Edit</a>
            <form method="POST" action="{{ route('trades.destroy', $trade) }}" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700" onclick="return confirm('Delete?')">Delete</button>
            </form>
        </div>
    </div>

    <div class="bg-white p-6 rounded shadow mb-6">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <div class="text-gray-600">Entry Price</div>
                <div class="text-2xl font-bold">${{ number_format($trade->entry_price, 2) }}</div>
            </div>
            <div>
                <div class="text-gray-600">Exit Price</div>
                <div class="text-2xl font-bold">{{ $trade->exit_price ? '$' . number_format($trade->exit_price, 2) : '--' }}</div>
            </div>
            <div>
                <div class="text-gray-600">P&L</div>
                <div class="text-2xl font-bold {{ $trade->p_l >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $trade->p_l !== null ? '$' . number_format($trade->p_l, 2) : '--' }}
                </div>
            </div>
            <div>
                <div class="text-gray-600">Status</div>
                <div class="text-xl">{{ ucfirst($trade->status) }}</div>
            </div>
        </div>
    </div>

    @if ($trade->comments->isNotEmpty())
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-xl font-bold mb-4">Comments</h2>
            <div class="space-y-3">
                @foreach ($trade->comments as $comment)
                    <div class="p-3 border rounded">
                        <p>{{ $comment->comment }}</p>
                        <div class="text-sm text-gray-500 mt-2">{{ $comment->created_at->diffForHumans() }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
