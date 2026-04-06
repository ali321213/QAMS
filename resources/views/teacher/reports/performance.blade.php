@extends('layouts.app')

@section('title', 'Performance report')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-lg font-bold text-slate-900">Performance report</h1>
            <form method="POST" action="{{ route('logout') }}" class="inline">@csrf
                <button type="submit" class="text-sm text-blue-600 font-medium">Logout</button>
            </form>
        </div>
    </div>
    <main class="max-w-7xl mx-auto px-6 py-8">
        @include('teacher.partials.nav')

        @if ($subjects->isEmpty())
            <p class="text-sm text-slate-500">No assigned subjects.</p>
        @else
            <form method="GET" action="{{ route('teacher.reports.performance') }}" class="mb-8 flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Subject</label>
                    <select name="subject_id" onchange="this.form.submit()" class="px-3 py-2 border border-slate-300 rounded-lg text-sm min-w-[220px]">
                        @foreach ($subjects as $s)
                            <option value="{{ $s->id }}" @selected($subjectId == $s->id)>{{ $s->schoolClass->name }} — {{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>

            @if ($report)
                <div class="grid sm:grid-cols-2 gap-6">
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                        <p class="text-xs uppercase text-slate-500 font-medium">Quiz average score</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">{{ $report['quiz_average'] }}</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                        <p class="text-xs uppercase text-slate-500 font-medium">Assignment average marks</p>
                        <p class="mt-2 text-3xl font-bold text-slate-900">{{ $report['assignment_average'] }}</p>
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-4">Generated {{ $report['generated_at']->format('d M Y H:i') }}</p>
            @endif
        @endif
    </main>
</div>
@endsection
