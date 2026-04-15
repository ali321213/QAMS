@extends('layouts.app')

@section('title', 'Attempt quiz')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-lg font-bold text-slate-900">{{ $quiz->title }}</h1>
                <p class="text-xs text-slate-500">{{ $quiz->subject->schoolClass->name }} — {{ $quiz->subject->name }}</p>
            </div>
            <a href="{{ route('student.quizzes.index') }}" class="text-sm text-blue-600 font-medium">Back</a>
        </div>
    </div>
    <main class="max-w-3xl mx-auto px-6 py-8">
        @include('student.partials.nav')
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">{{ $errors->first() }}</div>
        @endif

        @if ($questions->isEmpty())
            <p class="text-sm text-slate-500">No questions in the bank for this subject yet. Contact your teacher.</p>
        @else
            <form method="POST" action="{{ route('student.qams.quizzes.attempt', $quiz) }}" class="space-y-8">
                @csrf
                @foreach ($questions as $i => $q)
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                        <p class="text-xs font-medium text-slate-500 mb-2">Question {{ $i + 1 }}</p>
                        <p class="text-slate-900 font-medium mb-4">{{ $q->question }}</p>
                        <div class="space-y-2">
                            @foreach (['A' => $q->option_a, 'B' => $q->option_b, 'C' => $q->option_c, 'D' => $q->option_d] as $letter => $text)
                                <label class="flex items-start gap-3 p-3 rounded-lg border border-slate-100 hover:bg-slate-50 cursor-pointer">
                                    <input type="radio" name="answers[{{ $q->id }}]" value="{{ $letter }}" class="mt-1" {{ old('answers.'.$q->id) === $letter ? 'checked' : '' }}>
                                    <span class="text-sm text-slate-700"><span class="font-semibold text-slate-900">{{ $letter }}.</span> {{ $text }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700">Submit answers</button>
            </form>
        @endif
    </main>
</div>
@endsection
