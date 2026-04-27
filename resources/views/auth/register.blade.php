@extends('layouts.app')

@section('title', 'Register - Trading Journal')

@section('content')
<div class="max-w-md mx-auto mt-12">
    <div class="bg-white p-8 rounded shadow">
        <h1 class="text-2xl font-bold mb-6 text-center">Create Account</h1>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-semibold mb-2">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full px-4 py-2 border rounded focus:outline-none focus:border-blue-500" required autofocus>
                @error('name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-2 border rounded focus:outline-none focus:border-blue-500" required>
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

            <div>
                <label class="block text-sm font-semibold mb-2">Confirm Password</label>
                <input type="password" name="password_confirmation" class="w-full px-4 py-2 border rounded focus:outline-none focus:border-blue-500" required>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 font-semibold">
                Create Account
            </button>
        </form>

        <hr class="my-6">

        <p class="text-center text-gray-600">
            Already have an account?
            <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-700 font-semibold">
                Sign In
            </a>
        </p>
    </div>
</div>
@endsection
