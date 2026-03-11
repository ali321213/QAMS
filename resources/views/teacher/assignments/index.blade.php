@extends('layouts.app')

@section('title', 'My Assignments')

@section('content')
<div class="min-h-screen bg-slate-50">
    <nav class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="flex justify-between items-center max-w-7xl mx-auto px-6 py-4">
            <div>
                <h1 class="text-lg md:text-xl font-bold text-slate-900">My assignments</h1>
                <p class="text-xs text-slate-500 hidden sm:block">Create tasks, collect submissions, and grade student work.</p>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('teacher.dashboard') }}" class="text-xs md:text-sm text-slate-600 hover:text-slate-800">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-6 py-8 space-y-6">
        @if (session('success'))
            <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="flex justify-between items-center">
            <h2 class="text-sm font-semibold text-slate-900">All assignments</h2>
            <a href="{{ route('teacher.assignments.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 text-white rounded-lg text-xs md:text-sm font-medium hover:bg-amber-700 shadow-sm">
                <span class="text-base leading-none">+</span>
                <span>Create assignment</span>
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase">Subject</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase">Assigned</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase">Deadline</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($assignments as $assignment)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 text-sm text-slate-800">{{ $assignment->title }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $assignment->subject->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">{{ $assignment->assigned_at->format('d M Y H:i') }}</td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $assignment->effectiveDeadline()->format('d M Y H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $assignment->is_closed ? 'bg-slate-200 text-slate-700' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $assignment->is_closed ? 'Closed' : 'Open' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <a href="{{ route('teacher.assignments.edit', $assignment) }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Edit</a>
                                <a href="{{ route('teacher.assignments.submissions', $assignment) }}" class="text-sm text-slate-600 hover:text-slate-800 font-medium">Submissions</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-slate-500 text-sm">
                                No assignments created yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($assignments->hasPages())
                <div class="px-6 py-4 border-t border-slate-200">
                    {{ $assignments->links() }}
                </div>
            @endif
        </div>
    </main>
</div>
@endsection

