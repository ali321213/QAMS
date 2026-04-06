@extends('layouts.app')

@section('title', $assignment->title)

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-lg font-bold text-slate-900">{{ $assignment->title }}</h1>
            <a href="{{ route('student.assignments.index') }}" class="text-sm text-blue-600 font-medium">Back</a>
        </div>
    </div>
    <main class="max-w-2xl mx-auto px-6 py-8">
        @include('student.partials.nav')
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">{{ $errors->first() }}</div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-6">
            <p class="text-xs text-slate-500">{{ $assignment->subject->schoolClass->name }} — {{ $assignment->subject->name }}</p>
            <p class="text-sm text-slate-600 mt-2">Deadline: <span class="font-medium text-slate-900">{{ $assignment->deadline->format('d M Y H:i') }}</span></p>
            @if ($assignment->description)
                <div class="mt-4 text-sm text-slate-700 whitespace-pre-wrap">{{ $assignment->description }}</div>
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <h2 class="text-sm font-semibold text-slate-900 mb-4">Your submission</h2>
            <p class="text-xs text-slate-500 mb-2">Status: <span class="font-medium text-slate-800">{{ $submission->status }}</span>
                @if ($submission->submitted_at)
                    · Submitted {{ $submission->submitted_at->format('d M Y H:i') }}
                @endif
            </p>
            @if ($submission->file_path)
                <a href="{{ asset('storage/'.$submission->file_path) }}" target="_blank" class="text-sm text-blue-600 mb-4 inline-block">Download your file</a>
            @endif
            @if ($submission->feedback)
                <p class="text-sm text-slate-600 mt-2"><span class="font-medium">Feedback:</span> {{ $submission->feedback }}</p>
            @endif
            @if ($submission->status === 'graded')
                <p class="text-sm font-semibold text-emerald-700 mt-2">Marks: {{ $submission->marks }}</p>
            @endif

            @if ($assignment->deadline >= now() && in_array($submission->status, ['pending', 'auto_zero'], true))
                <form method="POST" action="{{ route('student.qams.assignments.submit', $assignment) }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Upload file (max 10 MB)</label>
                        <input type="file" name="file" required class="w-full text-sm text-slate-600">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Submit</button>
                </form>
            @elseif ($assignment->deadline < now() && $submission->status === 'pending')
                <p class="mt-4 text-sm text-amber-700">Deadline passed. You can no longer submit.</p>
            @endif
        </div>
    </main>
</div>
@endsection
