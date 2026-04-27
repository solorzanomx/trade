@extends('layouts.app')

@section('title', 'Login - Trading Journal')

@section('content')
<div class="max-w-md mx-auto mt-12">
    <div class="bg-white p-8 rounded shadow">
        <h1 class="text-2xl font-bold mb-6 text-center">Trading Journal</h1>

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-semibold mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2 border rounded focus:outline-none focus:border-blue-500" required autofocus>
                @error('email')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Password</label>
                <input type="password" name="password" class="w-full px-4 py-2 border rounded focus:outline-none focus:border-blue-500" required>
                @error('password')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="remember" id="remember" class="h-4 w-4">
                <label for="remember" class="ml-2 text-sm">Remember me</label>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 font-semibold">
                Sign In
            </button>
        </form>

        <hr class="my-6">

        <p class="text-center text-gray-600">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                Sign Up
            </a>
        </p>

        @if (Route::has('password.request'))
            <p class="text-center mt-4">
                <a href="{{ route('password.request') }}" class="text-gray-600 hover:text-gray-700 text-sm">
                    Forgot your password?
                </a>
            </p>
        @endif
    </div>
</div>
@endsection
