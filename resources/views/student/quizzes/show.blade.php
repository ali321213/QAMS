@extends('layouts.app')

@section('title', 'Quiz Question')

@section('content')
<div class="min-h-screen">
    <nav class="bg-white border-b border-slate-200 px-6 py-4">
        <div class="flex justify-between items-center max-w-7xl mx-auto">
            <div>
                <h1 class="text-xl font-bold text-slate-800">{{ $quiz->title }}</h1>
                <p class="text-xs text-slate-500">
                    Question {{ $index + 1 }} of {{ $totalQuestions }}
                </p>
            </div>
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2">
                    <span class="text-xs uppercase tracking-wide text-slate-500">Time left</span>
                    <span id="timer" class="px-3 py-1 rounded-full bg-slate-800 text-white text-sm font-semibold">
                        {{ $secondsPerQuestion }}
                    </span>
                </div>
                <a href="{{ route('student.quizzes.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                    Exit Quiz
                </a>
            </div>
        </div>
    </nav>
    <main class="max-w-3xl mx-auto px-6 py-8 space-y-6">
        @if ($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow border border-slate-200 p-6 space-y-4">
            <p class="text-sm text-slate-600">
                Subject: {{ $quiz->subject->name ?? '' }} • Ends {{ $quiz->ends_at->format('d M Y H:i') }}
            </p>
            @if ($quiz->description)
                <p class="text-sm text-slate-700">{{ $quiz->description }}</p>
            @endif
        </div>

        <form id="quiz-question-form" method="POST"
              action="{{ route('student.quizzes.question.answer', ['quiz' => $quiz->id, 'index' => $index]) }}"
              class="space-y-6">
            @csrf
            <div class="bg-white rounded-xl shadow border border-slate-200 p-5 space-y-3">
                <p class="text-sm font-semibold text-slate-800">
                    Q{{ $index + 1 }}. {{ $question->question_text }}
                </p>
                <div class="space-y-2 text-sm text-slate-700">
                    <label class="flex items-center gap-2">
                        <input type="radio" name="selected_option" value="a"
                               class="text-blue-600 border-slate-300">
                        <span>A. {{ $question->option_a }}</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="radio" name="selected_option" value="b"
                               class="text-blue-600 border-slate-300">
                        <span>B. {{ $question->option_b }}</span>
                    </label>
                    @if ($question->option_c)
                        <label class="flex items-center gap-2">
                            <input type="radio" name="selected_option" value="c"
                                   class="text-blue-600 border-slate-300">
                            <span>C. {{ $question->option_c }}</span>
                        </label>
                    @endif
                    @if ($question->option_d)
                        <label class="flex items-center gap-2">
                            <input type="radio" name="selected_option" value="d"
                                   class="text-blue-600 border-slate-300">
                            <span>D. {{ $question->option_d }}</span>
                        </label>
                    @endif
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <button type="submit"
                        class="px-5 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                    {{ $index + 1 === $totalQuestions ? 'Finish Quiz' : 'Next Question' }}
                </button>
            </div>
        </form>
    </main>
</div>
<script>
    (function () {
        let remaining = {{ $secondsPerQuestion }};
        const timerEl = document.getElementById('timer');
        const form = document.getElementById('quiz-question-form');

        function tick() {
            remaining--;
            if (remaining <= 0) {
                timerEl.textContent = '0';
                if (form) {
                    form.submit();
                }
                return;
            }
            timerEl.textContent = remaining.toString();
            setTimeout(tick, 1000);
        }

        setTimeout(tick, 1000);
    })();
</script>
@endsection

