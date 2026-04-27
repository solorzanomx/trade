@extends('layouts.app')

@section('title', 'Reset Password - Trading Journal')

@section('content')
<div class="max-w-md mx-auto mt-12">
    <div class="bg-white p-8 rounded shadow">
        <h1 class="text-2xl font-bold mb-6 text-center">Set New Password</h1>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label class="block text-sm font-semibold mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email', $request->email) }}" class="w-full px-4 py-2 border rounded focus:outline-none focus:border-blue-500" required autofocus>
                @error('email')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">New Password</label>
                <input type="password" name="password" class="w-full px-4 py-2 border rounded focus:outline-none focus:border-blue-500" required>
                @error('password')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Confirm Password</label>
                <input type="password" name="password_confirmation" class="w-full px-4 py-2 border rounded focus:outline-none focus:border-blue-500" required>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 font-semibold">
                Reset Password
            </button>
        </form>
    </div>
</div>
@endsection
