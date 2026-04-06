@extends('layouts.app')

@section('title', 'Assignments')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-lg font-bold text-slate-900">Assignments</h1>
            <form method="POST" action="{{ route('logout') }}" class="inline">@csrf
                <button type="submit" class="text-sm text-blue-600 font-medium">Logout</button>
            </form>
        </div>
    </div>
    <main class="max-w-7xl mx-auto px-6 py-8">
        @include('student.partials.nav')

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-6 py-3 font-medium text-slate-600">Assignment</th>
                        <th class="text-left px-6 py-3 font-medium text-slate-600">Subject</th>
                        <th class="text-left px-6 py-3 font-medium text-slate-600">Deadline</th>
                        <th class="text-right px-6 py-3 font-medium text-slate-600">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($assignments as $assignment)
                        @php $sub = $submissions->get($assignment->id); @endphp
                        <tr>
                            <td class="px-6 py-4">
                                <a href="{{ route('student.assignments.show', $assignment) }}" class="font-medium text-blue-600 hover:text-blue-700">{{ $assignment->title }}</a>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ $assignment->subject->schoolClass->name }} — {{ $assignment->subject->name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $assignment->deadline->format('d M Y H:i') }}</td>
                            <td class="px-6 py-4 text-right text-slate-600">
                                @if ($sub)
                                    <span class="font-medium">{{ $sub->status }}</span>
                                    @if ($sub->status === 'graded')
                                        · {{ $sub->marks }} marks
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-6 py-4 border-t border-slate-200">{{ $assignments->links() }}</div>
        </div>
    </main>
</div>
@endsection
