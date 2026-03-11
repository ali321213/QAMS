@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')
<div class="min-h-screen bg-slate-50">
    <nav class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="flex justify-between items-center max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white font-bold text-sm">
                    Q
                </span>
                <div>
                    <h1 class="text-lg md:text-xl font-bold text-slate-900">QAMS – Student</h1>
                    <p class="text-xs text-slate-500 hidden sm:block">Quiz & Assignment Management System</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="hidden sm:flex flex-col items-end">
                    <span class="text-sm font-medium text-slate-800">{{ auth()->user()->name }}</span>
                    <span class="text-xs text-slate-500">Student</span>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-6 py-8 md:py-10 space-y-8">
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
            <a href="{{ route('student.quizzes.index') }}"
               class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 p-[1px] shadow-sm hover:shadow-md transition">
                <div class="h-full rounded-2xl bg-slate-950/5 px-5 py-5 flex flex-col justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-blue-100/80">Quizzes</p>
                        <h2 class="mt-1 text-lg font-semibold text-white">Attempt quizzes</h2>
                        <p class="mt-1 text-xs text-blue-100/80">
                            Start or resume timed quizzes assigned to your class.
                        </p>
                    </div>
                    <span class="mt-4 inline-flex items-center text-xs font-medium text-blue-100 group-hover:translate-x-0.5 transition">
                        Go to quizzes
                        <svg class="ml-1 h-3 w-3" viewBox="0 0 16 16" fill="none">
                            <path d="M6 3l4 5-4 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </div>
            </a>
            <a href="{{ route('student.assignments.index') }}"
               class="group relative overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200 hover:border-amber-300/80 hover:shadow-md transition">
                <div class="h-full px-5 py-5 flex flex-col justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-amber-500">Assignments</p>
                        <h2 class="mt-1 text-lg font-semibold text-slate-900">Submit work</h2>
                        <p class="mt-1 text-xs text-slate-500">
                            Upload solutions before the deadline to avoid zero marks.
                        </p>
                    </div>
                    <span class="mt-4 inline-flex items-center text-xs font-medium text-amber-600 group-hover:translate-x-0.5 transition">
                        View assignments
                        <svg class="ml-1 h-3 w-3" viewBox="0 0 16 16" fill="none">
                            <path d="M6 3l4 5-4 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </div>
            </a>
            <a href="{{ route('student.reports.performance') }}"
               class="group relative overflow-hidden rounded-2xl bg-white shadow-sm border border-slate-200 hover:border-emerald-300/80 hover:shadow-md transition">
                <div class="h-full px-5 py-5 flex flex-col justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-emerald-500">Performance</p>
                        <h2 class="mt-1 text-lg font-semibold text-slate-900">Track progress</h2>
                        <p class="mt-1 text-xs text-slate-500">
                            See your quiz scores and graded assignments in one place.
                        </p>
                    </div>
                    <span class="mt-4 inline-flex items-center text-xs font-medium text-emerald-600 group-hover:translate-x-0.5 transition">
                        View report
                        <svg class="ml-1 h-3 w-3" viewBox="0 0 16 16" fill="none">
                            <path d="M6 3l4 5-4 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </div>
            </a>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-slate-900">Upcoming quizzes</h2>
                    <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-[11px] font-medium text-blue-700">
                        {{ $upcomingQuizzes->count() }} scheduled
                    </span>
                </div>
                @forelse ($upcomingQuizzes as $quiz)
                    <div class="py-2.5 border-b border-slate-100 last:border-0 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-slate-900">{{ $quiz->title }}</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                {{ $quiz->subject->name ?? '' }} • Ends {{ $quiz->ends_at->format('d M Y H:i') }}
                            </p>
                        </div>
                        <span class="mt-0.5 rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-medium text-blue-700">
                            Quiz
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No upcoming quizzes.</p>
                @endforelse
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-6">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-sm font-semibold text-slate-900">Pending assignments</h2>
                    <span class="rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-medium text-amber-700">
                        {{ $pendingAssignments->count() }} open
                    </span>
                </div>
                @forelse ($pendingAssignments as $assignment)
                    <div class="py-2.5 border-b border-slate-100 last:border-0 flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-slate-900">{{ $assignment->title }}</p>
                            <p class="text-[11px] text-slate-500 mt-0.5">
                                {{ $assignment->subject->name ?? '' }} • Due {{ $assignment->effectiveDeadline()->format('d M Y H:i') }}
                            </p>
                        </div>
                        <span class="mt-0.5 rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700">
                            Assignment
                        </span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No pending assignments.</p>
                @endforelse
            </div>
        </section>
    </main>
</div>
@endsection

