@extends('layouts.app')

@section('title', 'Create Quiz')

@section('content')
<div class="min-h-screen">
    <nav class="bg-white border-b border-slate-200 px-6 py-4">
        <div class="flex justify-between items-center max-w-7xl mx-auto">
            <h1 class="text-xl font-bold text-slate-800">Create Quiz</h1>
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

        <form method="POST" action="{{ route('teacher.quizzes.store') }}" class="space-y-6">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Title</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Subject</label>
                <select name="subject_id" required
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Select subject</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>
                            {{ $subject->name }} ({{ $subject->class->name ?? '' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Start Time (optional)</label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">End Time</label>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
                <textarea name="description" rows="3"
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('description') }}</textarea>
            </div>

            <div x-data="{ questions: [{key: Date.now()}] }" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-md font-semibold text-slate-800">Questions</h2>
                    <button type="button" @click="questions.push({key: Date.now()})"
                        class="px-3 py-1 text-xs bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                        + Add Question
                    </button>
                </div>

                <template x-for="(q, index) in questions" :key="q.key">
                    <div class="border border-slate-200 rounded-lg p-4 space-y-3 bg-slate-50">
                        <div class="flex justify-between items-center">
                            <h3 class="text-sm font-semibold text-slate-700">Question <span x-text="index + 1"></span></h3>
                            <button type="button" @click="questions.splice(index, 1)" x-show="questions.length > 1"
                                class="text-xs text-red-600 hover:text-red-700">
                                Remove
                            </button>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Question Text</label>
                            <textarea :name="`questions[${index}][question_text]`" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Option A</label>
                                <input type="text" :name="`questions[${index}][option_a]`" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Option B</label>
                                <input type="text" :name="`questions[${index}][option_b]`" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Option C (optional)</label>
                                <input type="text" :name="`questions[${index}][option_c]`" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Option D (optional)</label>
                                <input type="text" :name="`questions[${index}][option_d]`" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Correct Option</label>
                                <select :name="`questions[${index}][correct_option]`" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="a">A</option>
                                    <option value="b">B</option>
                                    <option value="c">C</option>
                                    <option value="d">D</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-700 mb-1">Marks</label>
                                <input type="number" min="1" :name="`questions[${index}][marks]`" value="1"
                                    class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('teacher.quizzes.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                    Save Quiz
                </button>
            </div>
        </form>
    </main>
</div>
@endsection

