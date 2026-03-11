@extends('layouts.app')

@section('title', 'Edit Assignment')

@section('content')
<div class="min-h-screen">
    <nav class="bg-white border-b border-slate-200 px-6 py-4">
        <div class="flex justify-between items-center max-w-7xl mx-auto">
            <h1 class="text-xl font-bold text-slate-800">Edit Assignment</h1>
            <a href="{{ route('teacher.assignments.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                Back to Assignments
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

        <form method="POST" action="{{ route('teacher.assignments.update', $assignment) }}" class="space-y-6" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Title</label>
                <input type="text" name="title" value="{{ old('title', $assignment->title) }}" required
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Subject</label>
                <select name="subject_id" required
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(old('subject_id', $assignment->subject_id) == $subject->id)>
                            {{ $subject->name }} ({{ $subject->class->name ?? '' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Deadline</label>
                <input type="datetime-local" name="deadline_at"
                    value="{{ old('deadline_at', $assignment->deadline_at->format('Y-m-d\TH:i')) }}" required
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Description / Instructions</label>
                <textarea name="description" rows="4"
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">{{ old('description', $assignment->description) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Attachment (optional)</label>
                <input type="file" name="attachment"
                    class="block w-full text-sm text-slate-700 file:mr-4 file:py-2 file:px-4
                        file:rounded-lg file:border-0 file:text-sm file:font-semibold
                        file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                @if ($assignment->attachment_path)
                    <p class="mt-1 text-xs text-slate-500">
                        Current file:
                        <a href="{{ asset('storage/'.$assignment->attachment_path) }}" target="_blank" class="text-amber-700 hover:text-amber-800 font-medium">
                            Download attachment
                        </a>
                    </p>
                @else
                    <p class="mt-1 text-xs text-slate-500">No file uploaded yet. You can upload one to share detailed questions.</p>
                @endif
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('teacher.assignments.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2 bg-amber-600 text-white rounded-lg text-sm font-medium hover:bg-amber-700">
                    Update Assignment
                </button>
            </div>
        </form>

        <div class="mt-8 border-t border-slate-200 pt-6">
            <h2 class="text-md font-semibold text-slate-800 mb-3">Extend Deadline</h2>
            <form method="POST" action="{{ route('teacher.assignments.extend-deadline', $assignment) }}" class="flex items-center gap-3">
                @csrf
                <input type="datetime-local" name="extended_deadline_at"
                    value="{{ optional($assignment->extended_deadline_at)->format('Y-m-d\TH:i') }}"
                    class="px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-medium hover:bg-slate-900">
                    Extend
                </button>
            </form>
        </div>
    </main>
</div>
@endsection

