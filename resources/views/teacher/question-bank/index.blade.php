@extends('layouts.app')

@section('title', 'Question bank')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-lg font-bold text-slate-900">Question bank</h1>
            <form method="POST" action="{{ route('logout') }}" class="inline">@csrf
                <button type="submit" class="text-sm text-blue-600 font-medium">Logout</button>
            </form>
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

        @if ($subjects->isEmpty())
            <p class="text-sm text-slate-500">You have no assigned subjects yet. Ask an administrator to assign you to subjects.</p>
        @else
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8">
                <h2 class="text-sm font-semibold text-slate-900 mb-4">Add question</h2>
                <form method="POST" action="{{ route('teacher.qams.question-bank.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Subject</label>
                        <select name="subject_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                            @foreach ($subjects as $s)
                                <option value="{{ $s->id }}">{{ $s->schoolClass->name }} — {{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Question</label>
                        <textarea name="question" rows="2" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">{{ old('question') }}</textarea>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-3">
                        <div><label class="text-xs text-slate-600">Option A</label><input type="text" name="option_a" value="{{ old('option_a') }}" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm"></div>
                        <div><label class="text-xs text-slate-600">Option B</label><input type="text" name="option_b" value="{{ old('option_b') }}" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm"></div>
                        <div><label class="text-xs text-slate-600">Option C</label><input type="text" name="option_c" value="{{ old('option_c') }}" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm"></div>
                        <div><label class="text-xs text-slate-600">Option D</label><input type="text" name="option_d" value="{{ old('option_d') }}" required class="mt-1 w-full px-3 py-2 border border-slate-300 rounded-lg text-sm"></div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Correct option</label>
                        <select name="correct_option" required class="w-full max-w-xs px-3 py-2 border border-slate-300 rounded-lg text-sm">
                            @foreach (['A','B','C','D'] as $o)
                                <option value="{{ $o }}" @selected(old('correct_option') === $o)>{{ $o }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Save question</button>
                </form>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left px-6 py-3 font-medium text-slate-600">Subject</th>
                            <th class="text-left px-6 py-3 font-medium text-slate-600">Question</th>
                            <th class="text-left px-6 py-3 font-medium text-slate-600">Answer</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($questions as $q)
                            <tr>
                                <td class="px-6 py-4 text-slate-600">{{ $q->subject->schoolClass->name }} — {{ $q->subject->name }}</td>
                                <td class="px-6 py-4 text-slate-900">{{ str($q->question)->limit(80) }}</td>
                                <td class="px-6 py-4 font-medium text-emerald-700">{{ $q->correct_option }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-12 text-center text-slate-500">No questions yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-slate-200">{{ $questions->links() }}</div>
            </div>
        @endif
    </main>
</div>
@endsection
