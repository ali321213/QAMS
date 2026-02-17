@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-slate-800">QAMS</h1>
                <p class="text-slate-600 mt-1">Quiz and Assignment Management System</p>
                <h2 class="text-lg font-semibold text-slate-700 mt-6">Login</h2>
            </div>

            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="text-sm text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="user_name" class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                    <input type="text" name="user_name" id="user_name" value="{{ old('user_name') }}" required
                        autofocus
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" id="password" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember"
                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <label for="remember" class="ml-2 text-sm text-slate-600">Remember me</label>
                </div>
                <button type="submit" class="w-full py-3 px-4 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Login
                </button>
            </form>
            <p class="mt-6 text-center text-sm text-slate-600">
                Don't have an account? <a href="{{ route('register') }}" class="font-medium text-blue-600 hover:text-blue-500">Register</a>
            </p>
        </div>
    </div>
</div>
@endsection
