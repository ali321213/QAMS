@extends('layouts.app')

@section('title', 'Quiz Results')

@section('content')
<div class="min-h-screen">
    <nav class="bg-white border-b border-slate-200 px-6 py-4">
        <div class="flex justify-between items-center max-w-7xl mx-auto">
            <h1 class="text-xl font-bold text-slate-800">Quiz Results - {{ $quiz->title }}</h1>
            <a href="{{ route('teacher.quizzes.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                Back to Quizzes
            </a>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-6 py-8 space-y-6">
        <div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase">Submitted At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase">Score</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($quiz->attempts as $attempt)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 text-sm text-slate-800">
                                {{ $attempt->student->user->name ?? 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ optional($attempt->submitted_at)->format('d M Y H:i') ?? 'In progress' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-800">
                                {{ $attempt->total_score }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-slate-500 text-sm">
                                No attempts yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</div>
@endsection

