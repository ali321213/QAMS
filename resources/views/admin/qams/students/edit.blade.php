@extends('layouts.app')

@section('title', 'Edit student')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-lg font-bold text-slate-900">Edit student</h1>
            <a href="{{ route('admin.qams.students.index') }}" class="text-sm text-blue-600 font-medium">Back</a>
        </div>
    </div>
    <main class="max-w-2xl mx-auto px-6 py-8">
        @include('admin.qams.partials.nav')
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">{{ $errors->first() }}</div>
        @endif
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            @if ($student->studentProfile?->picture_path)
                <div class="mb-4">
                    <p class="text-xs text-slate-500 mb-1">Current photo</p>
                    <img src="{{ asset('storage/'.$student->studentProfile->picture_path) }}" alt="" class="h-24 w-24 object-cover rounded-lg border border-slate-200">
                </div>
            @endif
            <form method="POST" action="{{ route('admin.qams.students.update', $student) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Full name</label>
                    <input type="text" name="name" value="{{ old('name', $student->name) }}" required maxlength="30" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Username</label>
                    <input type="text" name="user_name" value="{{ old('user_name', $student->user_name) }}" required maxlength="30" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">New password (optional)</label>
                    <input type="password" name="password" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Admission number</label>
                    <input type="text" name="admission_number" value="{{ old('admission_number', $student->studentProfile?->admission_number) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Father's name</label>
                    <input type="text" name="father_name" value="{{ old('father_name', $student->studentProfile?->father_name) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">New photo (optional)</label>
                    <input type="file" name="picture" accept="image/*" class="w-full text-sm text-slate-600">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Class</label>
                    <select name="school_class_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                        @foreach ($classes as $c)
                            <option value="{{ $c->id }}" @selected(old('school_class_id', $student->studentProfile?->school_class_id) == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-2">Subjects</label>
                    <div class="max-h-48 overflow-y-auto border border-slate-200 rounded-lg p-3 space-y-2">
                        @php $enrolled = old('subject_ids', $student->enrolledSubjects->pluck('id')->all()); @endphp
                        @foreach ($subjects as $sub)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="subject_ids[]" value="{{ $sub->id }}" @checked(in_array($sub->id, $enrolled))>
                                <span>{{ $sub->schoolClass->name }} — {{ $sub->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Save</button>
            </form>
        </div>
    </main>
</div>
@endsection
