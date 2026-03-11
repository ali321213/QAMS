@extends('layouts.app')

@section('title', 'My Quizzes')

@section('content')
<div class="min-h-screen bg-slate-50">
    <nav class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="flex justify-between items-center max-w-7xl mx-auto px-6 py-4">
            <div>
                <h1 class="text-lg md:text-xl font-bold text-slate-900">My quizzes</h1>
                <p class="text-xs text-slate-500 hidden sm:block">Create, publish, and review quiz performance.</p>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('teacher.dashboard') }}" class="text-xs md:text-sm text-slate-600 hover:text-slate-800">Dashboard</a>
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
        <div class="flex justify-between items-center">
            <h2 class="text-sm font-semibold text-slate-900">All quizzes</h2>
            <a href="{{ route('teacher.quizzes.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg text-xs md:text-sm font-medium hover:bg-blue-700 shadow-sm">
                <span class="text-base leading-none">+</span>
                <span>Create new quiz</span>
            </a>
        </div>
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
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 text-sm text-slate-800">{{ $quiz->title }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $quiz->subject->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ optional($quiz->starts_at)->format('d M Y H:i') ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $quiz->ends_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $quiz->is_published ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $quiz->is_published ? 'Published' : 'Draft' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('teacher.quizzes.edit', $quiz) }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Edit</a>
                                <a href="{{ route('teacher.quizzes.results', $quiz) }}" class="text-sm text-slate-600 hover:text-slate-800 font-medium">Results</a>
                                @unless($quiz->is_published)
                                    <form action="{{ route('teacher.quizzes.publish', $quiz) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium">
                                            Publish
                                        </button>
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-500 text-sm">
                                No quizzes created yet.
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

