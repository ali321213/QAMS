@extends('layouts.app')

@section('title', 'Classes')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-lg font-bold text-slate-900">Classes</h1>
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

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8">
            <h2 class="text-sm font-semibold text-slate-900 mb-4">Add class</h2>
            <form method="POST" action="{{ route('admin.qams.classes.store') }}" class="flex flex-wrap gap-3 items-end">
                @csrf
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Name</label>
                    <input type="text" name="name" value="{{ request('edit_class') ? '' : old('name') }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm @error('name') border-red-400 @enderror">
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700">Add</button>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="text-left px-6 py-3 font-medium text-slate-600">Name</th>
                        <th class="text-left px-6 py-3 font-medium text-slate-600">Subjects</th>
                        <th class="text-right px-6 py-3 font-medium text-slate-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($classes as $class)
                        @php
                            $showEdit = (int) request('edit_class') === (int) $class->id;
                        @endphp
                        <tr>
                            <td class="px-6 py-4 font-medium text-slate-900">{{ $class->name }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ $class->subjects_count }}</td>
                            <td class="px-6 py-4 text-right">
                                <button type="button" onclick="qamsToggleClassEdit({{ $class->id }})" class="text-blue-600 font-medium hover:text-blue-800">Edit</button>
                            </td>
                        </tr>
                        <tr id="class-edit-{{ $class->id }}" class="class-edit-panel bg-slate-50/80 {{ $showEdit ? '' : 'hidden' }}">
                            <td colspan="3" class="px-6 py-4">
                                <form method="POST" action="{{ route('admin.qams.classes.update', $class) }}" class="flex flex-wrap gap-3 items-end max-w-xl">
                                    @csrf
                                    @method('PUT')
                                    <div class="flex-1 min-w-[200px]">
                                        <label class="block text-xs font-medium text-slate-600 mb-1">Class name</label>
                                        <input type="text" name="name" value="{{ old('name', $class->name) }}" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm">
                                    </div>
                                    <button type="submit" class="px-4 py-2 bg-slate-800 text-white rounded-lg text-sm font-medium hover:bg-slate-900">Save</button>
                                    <button type="button" onclick="qamsCloseClassEdit({{ $class->id }})" class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-white">Cancel</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-12 text-center text-slate-500">No classes yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</div>
<script>
function qamsToggleClassEdit(id) {
    const row = document.getElementById('class-edit-' + id);
    if (!row) return;
    const willOpen = row.classList.contains('hidden');
    document.querySelectorAll('.class-edit-panel').forEach(function (el) { el.classList.add('hidden'); });
    if (willOpen) row.classList.remove('hidden');
}
function qamsCloseClassEdit(id) {
    const row = document.getElementById('class-edit-' + id);
    if (row) row.classList.add('hidden');
}
</script>
@endsection
