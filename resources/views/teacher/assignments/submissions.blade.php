@extends('layouts.app')

@section('title', 'Assignment Submissions')

@section('content')
<div class="min-h-screen">
    <nav class="bg-white border-b border-slate-200 px-6 py-4">
        <div class="flex justify-between items-center max-w-7xl mx-auto">
            <h1 class="text-xl font-bold text-slate-800">Submissions - {{ $assignment->title }}</h1>
            <a href="{{ route('teacher.assignments.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                Back to Assignments
            </a>
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
            <h2 class="text-lg font-semibold text-slate-800">Submissions</h2>
            <form method="POST" action="{{ route('teacher.assignments.auto-zero', $assignment) }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">
                    Auto-assign Zero for Missing
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow border border-slate-200 overflow-hidden">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase">Submitted At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase">Marks</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-slate-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse ($assignment->submissions as $submission)
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4 text-sm text-slate-800">
                                {{ $submission->student->user->name ?? 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ optional($submission->submitted_at)->format('d M Y H:i') ?? 'Not submitted' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-800">
                                {{ $submission->marks ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    @if($submission->status === 'graded') bg-emerald-100 text-emerald-800
                                    @elseif($submission->status === 'auto_zero') bg-red-100 text-red-800
                                    @elseif($submission->status === 'submitted') bg-blue-100 text-blue-800
                                    @else bg-slate-100 text-slate-700
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $submission->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if ($submission->file_path)
                                    <a href="{{ asset('storage/'.$submission->file_path) }}" target="_blank"
                                        class="text-sm text-blue-600 hover:text-blue-700 font-medium mr-3">
                                        View File
                                    </a>
                                @endif
                                <form method="POST" action="{{ route('teacher.assignments.grade', [$assignment, $submission]) }}" class="inline-flex items-center gap-2">
                                    @csrf
                                    <input type="number" name="marks" value="{{ $submission->marks ?? 0 }}" min="0"
                                        class="w-20 px-2 py-1 border border-slate-300 rounded-lg text-sm">
                                    <input type="text" name="feedback" value="{{ $submission->feedback }}"
                                        placeholder="Feedback"
                                        class="w-40 px-2 py-1 border border-slate-300 rounded-lg text-sm">
                                    <button type="submit" class="px-3 py-1 bg-emerald-600 text-white rounded-lg text-xs font-medium hover:bg-emerald-700">
                                        Save
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-500 text-sm">
                                No submissions yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</div>
@endsection

