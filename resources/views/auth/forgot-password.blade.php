@extends('layouts.app')

@section('title', 'Forgot Password - Trading Journal')

@section('content')
<div class="max-w-md mx-auto mt-12">
    <div class="bg-white p-8 rounded shadow">
        <h1 class="text-2xl font-bold mb-6 text-center">Reset Password</h1>

        @if (session('status'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded text-green-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <p class="text-gray-600 text-sm mb-4">
                Enter your email address and we'll send you a link to reset your password.
            </p>

            <div>
                <label class="block text-sm font-semibold mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2 border rounded focus:outline-none focus:border-blue-500" required autofocus>
                @error('email')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 font-semibold">
                Send Reset Link
            </button>
        </form>

        <hr class="my-6">

        <p class="text-center">
            <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 text-sm">
                Back to Login
            </a>
        </p>
    </div>
</div>
@endsection
