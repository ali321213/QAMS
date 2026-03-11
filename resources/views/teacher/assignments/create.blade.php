@extends('layouts.app')

@section('title', 'Create Assignment')

@section('content')
<div class="min-h-screen">
    <nav class="bg-white border-b border-slate-200 px-6 py-4">
        <div class="flex justify-between items-center max-w-7xl mx-auto">
            <h1 class="text-xl font-bold text-slate-800">Create Assignment</h1>
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

        <form method="POST" action="{{ route('teacher.assignments.store') }}" class="space-y-6" enctype="multipart/form-data">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Title</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Subject</label>
                <select name="subject_id" required
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    <option value="">Select subject</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>
                            {{ $subject->name }} ({{ $subject->class->name ?? '' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Deadline</label>
                <input type="datetime-local" name="deadline_at" value="{{ old('deadline_at') }}" required
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Attachment (optional)</label>
                <input type="file" name="attachment"
                    class="block w-full text-sm text-slate-700 file:mr-4 file:py-2 file:px-4
                        file:rounded-lg file:border-0 file:text-sm file:font-semibold
                        file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                <p class="mt-1 text-xs text-slate-500">Upload a PDF, DOCX, or any file with the detailed questions/instructions.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Description / Instructions</label>
                <textarea name="description" rows="4"
                    class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">{{ old('description') }}</textarea>
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('teacher.assignments.index') }}" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50">
                    Cancel
                </a>
                <button type="submit" class="px-5 py-2 bg-amber-600 text-white rounded-lg text-sm font-medium hover:bg-amber-700">
                    Save Assignment
                </button>
            </div>
        </form>
    </main>
</div>
@endsection

