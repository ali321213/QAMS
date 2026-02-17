@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="min-h-screen">
    <nav class="bg-white border-b border-slate-200 px-6 py-4">
        <div class="flex justify-between items-center max-w-7xl mx-auto">
            <h1 class="text-xl font-bold text-slate-800">QAMS</h1>
            <div class="flex items-center gap-4">
                <span class="text-slate-600">{{ $user->name }} ({{ ucfirst($user->role) }})</span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Logout</button>
                </form>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-6 py-12">
        <div class="bg-white rounded-xl shadow border border-slate-200 p-8">
            <h2 class="text-2xl font-bold text-slate-800">Welcome, {{ $user->name }}</h2>
            <p class="text-slate-600 mt-2">You are logged in as {{ ucfirst($user->role) }}.</p>
            <p class="text-slate-500 text-sm mt-6">Separate dashboards for Students and Teachers are not required in this test phase.</p>
        </div>
    </main>
</div>
@endsection
