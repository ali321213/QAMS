@extends('layouts.app')

@section('title', 'Available Quizzes')

@section('content')
<div class="min-h-screen bg-slate-50">
    <nav class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="flex justify-between items-center max-w-7xl mx-auto px-6 py-4">
            <div>
                <h1 class="text-lg md:text-xl font-bold text-slate-900">Available quizzes</h1>
                <p class="text-xs text-slate-500 hidden sm:block">Start or resume quizzes that are open for your class.</p>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('student.dashboard') }}" class="text-xs md:text-sm text-slate-600 hover:text-slate-800">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-6 py-8 space-y-6">
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

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase">Start</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase">End</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($quizzes as $quiz)
                        @php $attempt = $attempts[$quiz->id] ?? null; @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 text-sm text-slate-800">{{ $quiz->title }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $quiz->subject->name ?? '' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ optional($quiz->starts_at)->format('d M Y H:i') ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $quiz->ends_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($attempt && $attempt->status === 'submitted')
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-emerald-100 text-emerald-800">
                                        Completed ({{ $attempt->total_score }})
                                    </span>
                                @elseif ($quiz->ends_at->isPast())
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-700">
                                        Closed
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                        Open
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if (!$quiz->ends_at->isPast())
                                    @if ($attempt && $attempt->status === 'submitted')
                                        <span class="text-sm text-slate-500">Score: {{ $attempt->total_score }}</span>
                                    @else
                                        <form method="POST" action="{{ route('student.quizzes.start', $quiz) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                                {{ $attempt ? 'Resume' : 'Attempt' }}
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    @if ($attempt)
                                        <span class="text-sm text-slate-500">Score: {{ $attempt->total_score }}</span>
                                    @else
                                        <span class="text-sm text-slate-500">Missed</span>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-500 text-sm">
                                No quizzes available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($quizzes->hasPages())
                <div class="px-6 py-4 border-t border-slate-200">
                    {{ $quizzes->links() }}
                </div>
            @endif
        </div>
    </main>
</div>
@endsection

