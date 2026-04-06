@extends('layouts.app')

@section('title', 'Create assignment')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-lg font-bold text-slate-900">Create assignment</h1>
            <a href="{{ route('teacher.assignments.index') }}" class="text-sm text-blue-600 font-medium">Back</a>
        </div>
    </div>
    <main class="max-w-xl mx-auto px-6 py-8">
        @include('teacher.partials.nav')
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">{{ $errors->first() }}</div>
        @endif
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <form method="POST" action="{{ route('teacher.qams.assignments.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Subject</label>
                    <select name="subject_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        @foreach ($subjects as $s)
                            <option value="{{ $s->id }}">{{ $s->schoolClass->name }} — {{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Title</label>
                    <input type="text" name="title" value="{{ old('title') }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Description</label>
                    <textarea name="description" rows="4" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Deadline</label>
                    <input type="datetime-local" name="deadline" value="{{ old('deadline') }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Create</button>
            </form>
        </div>
    </main>
</div>
@endsection
