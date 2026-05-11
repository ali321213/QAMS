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
                        <select name="add_school_class_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm @error('add_school_class_id') border-red-400 @enderror">
                            @foreach ($classes as $c)
                                <option value="{{ $c->id }}" @selected(old('add_school_class_id', $classes->first()?->id) == $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('add_school_class_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-slate-600 mb-1">Subject name</label>
                        <input type="text" name="add_name" value="{{ old('add_name') }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm @error('add_name') border-red-400 @enderror">
                        @error('add_name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
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
                @php
                    $nested = 'subject_fields.'.$subject->id;
                    $subjectFieldErrors = $errors->has($nested.'.name') || $errors->has($nested.'.school_class_id');
                    $showSubjectEdit = (int) request('edit_subject') === (int) $subject->id || $subjectFieldErrors;
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex flex-wrap justify-between items-start gap-4">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-slate-900">{{ $subject->schoolClass->name }} — {{ $subject->name }}</p>
                            <p class="text-xs text-slate-500 mt-2 mb-1">Assigned teachers</p>
                            <div class="flex flex-wrap gap-2">
                                @forelse ($subject->teachers as $t)
                                    <span class="px-2 py-1 rounded-full bg-amber-50 text-amber-800 text-xs font-medium">{{ $t->name }}</span>
                                @empty
                                    <span class="text-sm text-slate-400">None yet</span>
                                @endforelse
                            </div>
                        </div>
                        <div class="shrink-0 flex items-center gap-3">
                            <button type="button" onclick="qamsToggleSubjectEdit({{ $subject->id }})" class="text-blue-600 font-medium hover:text-blue-800">Edit</button>
                        </div>
                    </div>

                    <div id="subject-edit-{{ $subject->id }}" class="subject-edit-panel border-t border-slate-100 pt-4 mt-4 {{ $showSubjectEdit ? '' : 'hidden' }}">
                        <h3 class="text-xs font-semibold text-slate-700 mb-3">Update subject</h3>
                        <form method="POST" action="{{ route('admin.qams.subjects.update', $subject) }}" class="grid sm:grid-cols-4 gap-4 items-end">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Class</label>
                                <select name="subject_fields[{{ $subject->id }}][school_class_id]" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm @error('subject_fields.'.$subject->id.'.school_class_id') border-red-400 @enderror">
                                    @foreach ($classes as $c)
                                        <option value="{{ $c->id }}" @selected(old('subject_fields.'.$subject->id.'.school_class_id', $subject->school_class_id) == $c->id)>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                @error('subject_fields.'.$subject->id.'.school_class_id')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Name</label>
                                <input type="text" name="subject_fields[{{ $subject->id }}][name]" value="{{ old('subject_fields.'.$subject->id.'.name', $subject->name) }}" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm @error('subject_fields.'.$subject->id.'.name') border-red-400 @enderror">
                                @error('subject_fields.'.$subject->id.'.name')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex flex-wrap gap-2 sm:flex-col sm:justify-end">
                                <button type="submit" class="w-full px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-medium hover:bg-slate-900">Save</button>
                                <button type="button" onclick="qamsCloseSubjectEdit({{ $subject->id }})" class="w-full px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">Cancel</button>
                            </div>
                        </form>
                    </div>

                    @if ($teachers->isNotEmpty())
                        <div class="border-t border-slate-100 pt-4 mt-4">
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
                        </div>
                    @else
                        <p class="text-xs text-slate-400 mt-4 border-t border-slate-100 pt-4">Register a teacher first to assign.</p>
                    @endif
                </div>
            @empty
                <p class="text-slate-500 text-sm">No subjects yet. Add a class first, then create subjects.</p>
            @endforelse
        </div>
    </main>
</div>
<script>
function qamsToggleSubjectEdit(id) {
    const panel = document.getElementById('subject-edit-' + id);
    if (!panel) return;
    const willOpen = panel.classList.contains('hidden');
    document.querySelectorAll('.subject-edit-panel').forEach(function (el) { el.classList.add('hidden'); });
    if (willOpen) panel.classList.remove('hidden');
}
function qamsCloseSubjectEdit(id) {
    const panel = document.getElementById('subject-edit-' + id);
    if (panel) panel.classList.add('hidden');
}
</script>
@endsection
