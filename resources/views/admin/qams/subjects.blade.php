@extends('layouts.app')

@section('title', 'Subjects')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-lg font-bold text-slate-900">Subjects</h1>
            <form method="POST" action="{{ route('logout') }}" class="inline">@csrf
                <button type="submit" class="text-sm text-blue-600 font-medium">Logout</button>
            </form>
        </div>
    </div>
    <main class="max-w-7xl mx-auto px-6 py-8">
        @include('admin.qams.partials.nav')

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">{{ $errors->first() }}</div>
        @endif

        @if ($classes->isNotEmpty())
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8">
                <h2 class="text-sm font-semibold text-slate-900 mb-4">Add subject</h2>
                <form method="POST" action="{{ route('admin.qams.subjects.store') }}" class="grid sm:grid-cols-3 gap-4 items-end">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Class</label>
                        <select name="school_class_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Subject name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                    </div>
                    <div class="sm:col-span-3">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Add subject</button>
                    </div>
                </form>
            </div>
        @else
            <p class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-xl p-4 mb-8">Create at least one class before adding subjects.</p>
        @endif

        <div class="space-y-6">
            @forelse ($subjects as $subject)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <form method="POST" action="{{ route('admin.qams.subjects.update', $subject) }}" class="grid sm:grid-cols-4 gap-4 items-end mb-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Class</label>
                            <select name="school_class_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                                @foreach ($classes as $c)
                                    <option value="{{ $c->id }}" @selected($subject->school_class_id == $c->id)>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-slate-600 mb-1">Name</label>
                            <input type="text" name="name" value="{{ $subject->name }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        </div>
                        <div>
                            <button type="submit" class="w-full px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-medium">Save</button>
                        </div>
                    </form>
                    <p class="text-xs text-slate-500 mb-2">Assigned teachers</p>
                    <div class="flex flex-wrap gap-2 mb-4">
                        @forelse ($subject->teachers as $t)
                            <span class="px-2 py-1 rounded-full bg-amber-50 text-amber-800 text-xs font-medium">{{ $t->name }}</span>
                        @empty
                            <span class="text-sm text-slate-400">None yet</span>
                        @endforelse
                    </div>
                    @if ($teachers->isNotEmpty())
                        <form method="POST" action="{{ route('admin.qams.subjects.assign-teacher', $subject) }}" class="flex flex-wrap gap-3 items-end">
                            @csrf
                            <div class="flex-1 min-w-[200px]">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Assign teacher</label>
                                <select name="teacher_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                                    @foreach ($teachers as $t)
                                        <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->user_name }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium hover:bg-slate-50">Assign</button>
                        </form>
                    @else
                        <p class="text-xs text-slate-400">Register a teacher first to assign.</p>
                    @endif
                </div>
            @empty
                <p class="text-slate-500 text-sm">No subjects yet. Add a class first, then create subjects.</p>
            @endforelse
        </div>
    </main>
</div>
@endsection
