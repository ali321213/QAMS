@extends('layouts.app')

@section('title', 'Performance Report')

@section('content')
<div class="min-h-screen bg-slate-50">
    <nav class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="flex justify-between items-center max-w-7xl mx-auto px-6 py-4">
            <div>
                <h1 class="text-lg md:text-xl font-bold text-slate-900">Performance report</h1>
                <p class="text-xs text-slate-500 hidden sm:block">Review your scores across quizzes and assignments.</p>
            </div>
            <a href="{{ route('student.dashboard') }}" class="text-xs md:text-sm text-blue-600 hover:text-blue-700 font-medium">
                ← Back to dashboard
            </a>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-6 py-8 space-y-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-sm font-semibold text-slate-900 mb-3">Quiz results</h2>
                @forelse ($quizAttempts as $attempt)
                    <div class="py-2 border-b border-slate-100 last:border-0">
                        <p class="text-sm font-medium text-slate-800">
                            {{ $attempt->quiz->title }} ({{ $attempt->quiz->subject->name ?? '' }})
                        </p>
                        <p class="text-xs text-slate-500">
                            Submitted: {{ optional($attempt->submitted_at)->format('d M Y H:i') ?? 'In progress' }}
                            • Score: {{ $attempt->total_score }}
                        </p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No quiz attempts yet.</p>
                @endforelse
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h2 class="text-sm font-semibold text-slate-900 mb-3">Assignment results</h2>
                @forelse ($assignmentSubmissions as $submission)
                    <div class="py-2 border-b border-slate-100 last:border-0">
                        <p class="text-sm font-medium text-slate-800">
                            {{ $submission->assignment->title }} ({{ $submission->assignment->subject->name ?? '' }})
                        </p>
                        <p class="text-xs text-slate-500">
                            Status: {{ ucfirst(str_replace('_', ' ', $submission->status)) }}
                            @if ($submission->marks !== null)
                                • Marks: {{ $submission->marks }}
                            @endif
                        </p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">No assignment submissions yet.</p>
                @endforelse
            </div>
        </div>
    </main>
</div>
@endsection

