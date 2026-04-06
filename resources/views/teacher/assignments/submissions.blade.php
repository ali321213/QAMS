@extends('layouts.app')

@section('title', 'Grade submissions')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div>
                <h1 class="text-lg font-bold text-slate-900">{{ $assignment->title }}</h1>
                <p class="text-xs text-slate-500">{{ $assignment->subject->schoolClass->name }} — {{ $assignment->subject->name }}</p>
            </div>
            <a href="{{ route('teacher.assignments.index') }}" class="text-sm text-blue-600 font-medium">Back</a>
        </div>
    </div>
    <main class="max-w-7xl mx-auto px-6 py-8">
        @include('teacher.partials.nav')
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">{{ $errors->first() }}</div>
        @endif

        <div class="space-y-6">
            @foreach ($assignment->submissions as $submission)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex flex-wrap justify-between gap-4 mb-4">
                        <div>
                            <p class="font-medium text-slate-900">{{ $submission->student->name }}</p>
                            <p class="text-xs text-slate-500">{{ $submission->student->user_name }}</p>
                            <p class="text-xs text-slate-500 mt-1">Status: <span class="font-medium">{{ $submission->status }}</span> · Marks: {{ $submission->marks }}</p>
                            @if ($submission->file_path)
                                <a href="{{ asset('storage/'.$submission->file_path) }}" target="_blank" class="text-sm text-blue-600 mt-2 inline-block">Download submission</a>
                            @else
                                <p class="text-xs text-slate-400 mt-2">No file uploaded</p>
                            @endif
                        </div>
                    </div>
                    <form method="POST" action="{{ route('teacher.qams.submissions.grade', $submission) }}" class="flex flex-wrap gap-3 items-end">
                        @csrf
                        <div>
                            <label class="block text-xs text-slate-600 mb-1">Marks (0–100)</label>
                            <input type="number" name="marks" min="0" max="100" value="{{ old('marks', $submission->marks) }}" required class="w-28 px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        </div>
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs text-slate-600 mb-1">Feedback</label>
                            <input type="text" name="feedback" value="{{ old('feedback', $submission->feedback) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        </div>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Save grade</button>
                    </form>
                </div>
            @endforeach
        </div>
    </main>
</div>
@endsection
