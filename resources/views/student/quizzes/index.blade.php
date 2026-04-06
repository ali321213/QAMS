@extends('layouts.app')

@section('title', 'Quizzes')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-lg font-bold text-slate-900">Quizzes</h1>
            <form method="POST" action="{{ route('logout') }}" class="inline">@csrf
                <button type="submit" class="text-sm text-blue-600 font-medium">Logout</button>
            </form>
        </div>
    </div>
    <main class="max-w-7xl mx-auto px-6 py-8">
        @include('student.partials.nav')
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-6 py-3 font-medium text-slate-600">Quiz</th>
                        <th class="text-left px-6 py-3 font-medium text-slate-600">Subject</th>
                        <th class="text-left px-6 py-3 font-medium text-slate-600">Deadline</th>
                        <th class="text-right px-6 py-3 font-medium text-slate-600">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($quizzes as $quiz)
                        @php
                            $attempt = $attempts->get($quiz->id);
                            $open = $quiz->deadline >= now() && ! $attempt;
                        @endphp
                        <tr>
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $quiz->title }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $quiz->subject->schoolClass->name }} — {{ $quiz->subject->name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $quiz->deadline->format('d M Y H:i') }}</td>
                            <td class="px-6 py-4 text-right">
                                @if ($attempt)
                                    <span class="text-emerald-600 font-medium">Score {{ $attempt->score }}/{{ $attempt->total_questions }}</span>
                                @elseif ($open)
                                    <a href="{{ route('student.quizzes.attempt', $quiz) }}" class="text-blue-600 font-medium">Attempt</a>
                                @else
                                    <span class="text-slate-400">Closed</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-slate-200">{{ $quizzes->links() }}</div>
        </div>
    </main>
</div>
@endsection
