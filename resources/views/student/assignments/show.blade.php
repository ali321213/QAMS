@extends('layouts.app')

@section('title', 'Assignment')

@section('content')
<div class="min-h-screen">
    <nav class="bg-white border-b border-slate-200 px-6 py-4">
        <div class="flex justify-between items-center max-w-7xl mx-auto">
            <h1 class="text-xl font-bold text-slate-800">{{ $assignment->title }}</h1>
            <a href="{{ route('student.assignments.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                Back to Assignments
            </a>
        </div>
    </nav>
    <main class="max-w-3xl mx-auto px-6 py-8 space-y-6">
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

        <div class="bg-white rounded-xl shadow border border-slate-200 p-6 space-y-3">
            <p class="text-sm text-slate-600">
                Subject: {{ $assignment->subject->name ?? '' }}
            </p>
            <p class="text-sm text-slate-600">
                Assigned: {{ $assignment->assigned_at->format('d M Y H:i') }} • Deadline:
                {{ $assignment->effectiveDeadline()->format('d M Y H:i') }}
            </p>
            @if ($assignment->description)
                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $assignment->description }}</p>
            @endif
            @if ($assignment->attachment_path)
                <a href="{{ asset('storage/'.$assignment->attachment_path) }}" target="_blank"
                   class="inline-flex items-center gap-2 mt-2 text-sm text-blue-600 hover:text-blue-700 font-medium">
                    <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none">
                        <path d="M4 8.5L8 12.5L12 8.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 3.5V12.3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    Download assignment file
                </a>
            @endif
        </div>

        @if ($submission)
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-2">
                <p class="text-sm font-semibold text-slate-800">Your Submission</p>
                <p class="text-sm text-slate-600">
                    Status: {{ ucfirst(str_replace('_', ' ', $submission->status)) }}
                    @if ($submission->submitted_at)
                        • Submitted at {{ $submission->submitted_at->format('d M Y H:i') }}
                    @endif
                </p>
                @if ($submission->marks !== null)
                    <p class="text-sm text-slate-600">Marks: {{ $submission->marks }}</p>
                @endif
                @if ($submission->feedback)
                    <p class="text-sm text-slate-600">Feedback: {{ $submission->feedback }}</p>
                @endif
                @if ($submission->file_path)
                    <a href="{{ asset('storage/'.$submission->file_path) }}" target="_blank"
                        class="inline-block mt-2 text-sm text-blue-600 hover:text-blue-700 font-medium">
                        View Uploaded File
                    </a>
                @endif
            </div>
        @endif

        @php
            $deadlinePassed = $assignment->effectiveDeadline()->isPast();
        @endphp

        @if (!$deadlinePassed)
            <div class="bg-white rounded-xl shadow border border-slate-200 p-6">
                <h2 class="text-md font-semibold text-slate-800 mb-3">Submit Assignment</h2>
                <form method="POST" action="{{ route('student.assignments.submit', $assignment) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="file" name="file" required
                        class="block w-full text-sm text-slate-700 file:mr-4 file:py-2 file:px-4
                            file:rounded-lg file:border-0 file:text-sm file:font-semibold
                            file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">
                        Upload
                    </button>
                </form>
            </div>
        @else
            <p class="text-sm text-red-600">
                The deadline has passed. You can no longer submit this assignment.
            </p>
        @endif
    </main>
</div>
@endsection

