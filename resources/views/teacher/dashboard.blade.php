@extends('layouts.app')

@section('title', 'Teacher Dashboard')

@section('content')
<div class="min-h-screen bg-slate-50">
    <nav class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="flex justify-between items-center max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white text-xs font-semibold">
                    TR
                </span>
                <div>
                    <h1 class="text-lg md:text-xl font-bold text-slate-900">Teacher dashboard</h1>
                    <p class="text-xs text-slate-500 hidden sm:block">Create quizzes, manage assignments, and monitor performance</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="hidden sm:flex flex-col items-end">
                    <span class="text-sm font-medium text-slate-800">{{ auth()->user()->name }}</span>
                    <span class="text-xs text-slate-500">Teacher</span>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-6 py-8 md:py-10 space-y-6">
        @if (session('success'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="{{ route('teacher.quizzes.index') }}"
               class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 p-[1px] shadow-sm hover:shadow-md transition">
                <div class="h-full rounded-2xl bg-slate-950/5 px-5 py-5 flex flex-col justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-blue-100/80">Quizzes</p>
                        <h2 class="mt-1 text-lg font-semibold text-white">Question banks & tests</h2>
                        <p class="mt-1 text-xs text-blue-100/80">
                            Build and publish quizzes. Auto-marking is handled for you.
                        </p>
                    </div>
                    <div class="mt-4 flex items-end justify-between">
                        <p class="text-3xl font-bold text-white">{{ $quizzesCount }}</p>
                        <span class="inline-flex items-center text-[11px] font-medium text-blue-100 group-hover:translate-x-0.5 transition">
                            Manage quizzes
                            <svg class="ml-1 h-3 w-3" viewBox="0 0 16 16" fill="none">
                                <path d="M6 3l4 5-4 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>
                </div>
            </a>
            <a href="{{ route('teacher.assignments.index') }}"
               class="group relative overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200 hover:border-amber-300/80 hover:shadow-md transition">
                <div class="h-full px-5 py-5 flex flex-col justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-amber-500">Assignments</p>
                        <h2 class="mt-1 text-lg font-semibold text-slate-900">Homework & projects</h2>
                        <p class="mt-1 text-xs text-slate-500">
                            Collect submissions, extend deadlines, and grade in one place.
                        </p>
                    </div>
                    <div class="mt-4 flex items-end justify-between">
                        <p class="text-3xl font-bold text-amber-600">{{ $assignmentsCount }}</p>
                        <span class="inline-flex items-center text-[11px] font-medium text-amber-600 group-hover:translate-x-0.5 transition">
                            Manage assignments
                            <svg class="ml-1 h-3 w-3" viewBox="0 0 16 16" fill="none">
                                <path d="M6 3l4 5-4 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>
                </div>
            </a>
            <a href="{{ route('dashboard') }}"
               class="group relative overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200 hover:border-slate-300 hover:shadow-md transition">
                <div class="h-full px-5 py-5 flex flex-col justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Switch role</p>
                        <h2 class="mt-1 text-lg font-semibold text-slate-900">Main dashboard</h2>
                        <p class="mt-1 text-xs text-slate-500">
                            Return to the central dashboard and choose a different role.
                        </p>
                    </div>
                    <span class="mt-4 inline-flex items-center text-[11px] font-medium text-slate-700 group-hover:translate-x-0.5 transition">
                        Go to main dashboard
                        <svg class="ml-1 h-3 w-3" viewBox="0 0 16 16" fill="none">
                            <path d="M6 3l4 5-4 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </div>
            </a>
        </section>
    </main>
</div>
@endsection

