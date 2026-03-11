@extends('layouts.app')

@section('title', 'Edit Quiz')

@section('content')
<div class="min-h-screen">
    <nav class="bg-white border-b border-slate-200 px-6 py-4">
        <div class="flex justify-between items-center max-w-7xl mx-auto">
            <h1 class="text-xl font-bold text-slate-800">Edit Quiz</h1>
            <a href="{{ route('teacher.quizzes.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                Back to Quizzes
            </a>
        </div>
    </nav>
    <main class="max-w-3xl mx-auto px-6 py-8">
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <ul class="text-sm text-red-700 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('teacher.quizzes.update', $quiz) }}" class="space-y-6">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Title</label>
                <input type="text" name="title" value="{{ old('title', $quiz->title) }}" required
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Subject</label>
                <select name="subject_id" required
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(old('subject_id', $quiz->subject_id) == $subject->id)>
                            {{ $subject->name }} ({{ $subject->class->name ?? '' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Start Time (optional)</label>
                    <input type="datetime-local" name="starts_at"
                        value="{{ old('starts_at', optional($quiz->starts_at)->format('Y-m-d\TH:i')) }}"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">End Time</label>
                    <input type="datetime-local" name="ends_at"
                        value="{{ old('ends_at', $quiz->ends_at->format('Y-m-d\TH:i')) }}" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                <textarea name="description" rows="3"
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description', $quiz->description) }}</textarea>
            </div>

            <p class="text-sm text-slate-500">
                To keep things simple, editing individual questions is not included here. You can delete and recreate the quiz if you need different questions.
            </p>

            <div class="flex justify-end gap-3">
                <a href="{{ route('teacher.quizzes.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                    Update Quiz
                </button>
            </div>
        </form>
    </main>
</div>
@endsection

