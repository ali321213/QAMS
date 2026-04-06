@extends('layouts.app')

@section('title', 'Students')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-lg font-bold text-slate-900">Students</h1>
            <div class="flex gap-3 items-center">
                <a href="{{ route('admin.qams.students.create') }}" class="text-sm font-medium text-blue-600">Register student</a>
                <form method="POST" action="{{ route('logout') }}" class="inline">@csrf
                    <button type="submit" class="text-sm text-slate-600">Logout</button>
                </form>
            </div>
        </div>
    </div>
    <main class="max-w-7xl mx-auto px-6 py-8">
        @include('admin.qams.partials.nav')
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-6 py-3 font-medium text-slate-600">Name</th>
                        <th class="text-left px-6 py-3 font-medium text-slate-600">Admission</th>
                        <th class="text-left px-6 py-3 font-medium text-slate-600">Class</th>
                        <th class="text-right px-6 py-3 font-medium text-slate-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($students as $student)
                        <tr>
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $student->name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $student->studentProfile?->admission_number }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $student->studentProfile?->schoolClass?->name ?? '—' }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.qams.students.edit', $student) }}" class="text-blue-600 font-medium">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-slate-200">{{ $students->links() }}</div>
        </div>
    </main>
</div>
@endsection
