@extends('layouts.app')

@section('title', 'Assignments')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-lg font-bold text-slate-900">Assignments</h1>
            <div class="flex gap-3">
                @if ($subjects->isNotEmpty())
                    <a href="{{ route('teacher.assignments.create') }}" class="text-sm font-medium text-blue-600">Create assignment</a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="inline">@csrf
                    <button type="submit" class="text-sm text-slate-600">Logout</button>
                </form>
            </div>
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
            @forelse ($assignments as $assignment)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h2 class="font-semibold text-slate-900">{{ $assignment->title }}</h2>
                            <p class="text-sm text-slate-500 mt-1">{{ $assignment->subject->schoolClass->name }} — {{ $assignment->subject->name }}</p>
                            <p class="text-xs text-slate-500 mt-2">Deadline {{ $assignment->deadline->format('d M Y H:i') }}</p>
                            <span class="inline-block mt-2 px-2 py-0.5 rounded-full text-xs font-medium {{ $assignment->published ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                {{ $assignment->published ? 'Published' : 'Draft' }}
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('teacher.assignments.submissions', $assignment) }}" class="px-3 py-1.5 rounded-lg bg-slate-900 text-white text-sm font-medium">Submissions &amp; grade</a>
                            @if (! $assignment->published)
                                <form method="POST" action="{{ route('teacher.qams.assignments.publish', $assignment) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-emerald-600 text-white text-sm font-medium hover:bg-emerald-700">Publish</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('teacher.qams.assignments.extend', $assignment) }}" class="flex flex-wrap items-end gap-2">
                                @csrf
                                <div>
                                    <label class="block text-[10px] uppercase text-slate-500 mb-0.5">New deadline</label>
                                    <input type="datetime-local" name="deadline" required class="px-2 py-1.5 border border-slate-300 rounded-lg text-xs">
                                </div>
                                <button type="submit" class="px-3 py-1.5 rounded-lg border border-slate-300 text-sm font-medium hover:bg-slate-50">Extend</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-500">No assignments yet.</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $assignments->links() }}</div>
    </main>
</div>
@endsection
