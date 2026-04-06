@extends('layouts.app')

@section('title', 'Edit teacher')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-lg font-bold text-slate-900">Edit teacher</h1>
            <a href="{{ route('admin.qams.teachers.index') }}" class="text-sm text-blue-600 font-medium">Back</a>
        </div>
    </div>
    <main class="max-w-2xl mx-auto px-6 py-8">
        @include('admin.qams.partials.nav')
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">{{ $errors->first() }}</div>
        @endif
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <form method="POST" action="{{ route('admin.qams.teachers.update', $teacher) }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Full name</label>
                    <input type="text" name="name" value="{{ old('name', $teacher->name) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Username</label>
                    <input type="text" name="user_name" value="{{ old('user_name', $teacher->user_name) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">New password (optional)</label>
                    <input type="password" name="password" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Job history</label>
                    <textarea name="job_history" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">{{ old('job_history', $teacher->teacherProfile?->job_history) }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Education</label>
                    <textarea name="education" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">{{ old('education', $teacher->teacherProfile?->education) }}</textarea>
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Save</button>
            </form>
        </div>
    </main>
</div>
@endsection
