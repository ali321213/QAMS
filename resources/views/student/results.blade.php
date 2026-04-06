@extends('layouts.app')

@section('title', 'My results')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-lg font-bold text-slate-900">My results</h1>
            <form method="POST" action="{{ route('logout') }}" class="inline">@csrf
                <button type="submit" class="text-sm text-blue-600 font-medium">Logout</button>
            </form>
        </div>
    </div>
    <main class="max-w-7xl mx-auto px-6 py-8">
        @include('student.partials.nav')

        <div class="grid lg:grid-cols-2 gap-8">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-sm font-semibold text-slate-900">Quiz results</h2>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse ($quizResults as $row)
                        <li class="px-6 py-4">
                            <p class="font-medium text-slate-900">{{ $row->quiz->title ?? 'Quiz #'.$row->quiz_id }}</p>
                            <p class="text-xs text-slate-500 mt-1">{{ $row->quiz->subject->name ?? '' }}</p>
                            <p class="text-sm text-emerald-700 font-semibold mt-2">{{ $row->score }} / {{ $row->total_questions }}</p>
                            <p class="text-xs text-slate-400">{{ $row->submitted_at?->format('d M Y H:i') }}</p>
                        </li>
                    @empty
                        <li class="px-6 py-12 text-center text-slate-500 text-sm">No quiz attempts yet.</li>
                    @endforelse
                </ul>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                    <h2 class="text-sm font-semibold text-slate-900">Assignments</h2>
                </div>
                <ul class="divide-y divide-slate-100">
                    @forelse ($assignmentResults as $row)
                        <li class="px-6 py-4">
                            <p class="font-medium text-slate-900">{{ $row->assignment->title ?? 'Assignment #'.$row->assignment_id }}</p>
                            <p class="text-xs text-slate-500 mt-1">{{ $row->assignment->subject->name ?? '' }}</p>
                            <p class="text-sm text-slate-700 mt-2">Status: <span class="font-medium">{{ $row->status }}</span> · Marks: {{ $row->marks }}</p>
                            @if ($row->feedback)
                                <p class="text-xs text-slate-600 mt-1">{{ $row->feedback }}</p>
                            @endif
                        </li>
                    @empty
                        <li class="px-6 py-12 text-center text-slate-500 text-sm">No assignment records yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </main>
</div>
@endsection
