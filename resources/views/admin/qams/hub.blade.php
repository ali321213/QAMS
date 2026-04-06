@extends('layouts.app')

@section('title', 'QAMS Administration')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-lg font-bold text-slate-900">QAMS administration</h1>
            <form method="POST" action="{{ route('logout') }}" class="inline">@csrf
                <button type="submit" class="text-sm text-blue-600 font-medium">Logout</button>
            </form>
        </div>
    </div>
    <main class="max-w-7xl mx-auto px-6 py-8">
        @include('admin.qams.partials.nav')

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <a href="{{ route('admin.qams.classes.index') }}" class="block rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:border-blue-300 transition">
                <h2 class="font-semibold text-slate-900">Classes</h2>
                <p class="text-sm text-slate-500 mt-2">Add and update school classes.</p>
            </a>
            <a href="{{ route('admin.qams.subjects.index') }}" class="block rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:border-blue-300 transition">
                <h2 class="font-semibold text-slate-900">Subjects</h2>
                <p class="text-sm text-slate-500 mt-2">Subjects per class and assign teachers.</p>
            </a>
            <a href="{{ route('admin.qams.teachers.index') }}" class="block rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:border-blue-300 transition">
                <h2 class="font-semibold text-slate-900">Teachers</h2>
                <p class="text-sm text-slate-500 mt-2">Register teachers with job history and education.</p>
            </a>
            <a href="{{ route('admin.qams.students.index') }}" class="block rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:border-blue-300 transition">
                <h2 class="font-semibold text-slate-900">Students</h2>
                <p class="text-sm text-slate-500 mt-2">Admission numbers, class, subjects, photo.</p>
            </a>
            <a href="{{ route('admin.qams.reports') }}" class="block rounded-2xl border border-slate-200 bg-white p-6 shadow-sm hover:border-blue-300 transition">
                <h2 class="font-semibold text-slate-900">Reports</h2>
                <p class="text-sm text-slate-500 mt-2">Counts for students, teachers, subjects, classes.</p>
            </a>
        </div>
    </main>
</div>
@endsection
