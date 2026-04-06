@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-lg font-bold text-slate-900">Reports</h1>
            <form method="POST" action="{{ route('logout') }}" class="inline">@csrf
                <button type="submit" class="text-sm text-blue-600 font-medium">Logout</button>
            </form>
        </div>
    </div>
    <main class="max-w-7xl mx-auto px-6 py-8">
        @include('admin.qams.partials.nav')

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-sm">
                <p class="text-xs font-medium uppercase text-slate-500">Students</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['students_count'] }}</p>
            </div>
            <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-sm">
                <p class="text-xs font-medium uppercase text-slate-500">Teachers</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['teachers_count'] }}</p>
            </div>
            <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-sm">
                <p class="text-xs font-medium uppercase text-slate-500">Subjects</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['subjects_count'] }}</p>
            </div>
            <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-sm">
                <p class="text-xs font-medium uppercase text-slate-500">Classes</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['classes_count'] }}</p>
            </div>
        </div>
    </main>
</div>
@endsection
