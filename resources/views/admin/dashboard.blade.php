@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="min-h-screen bg-slate-50">
    <nav class="bg-white/80 border-b border-slate-200 backdrop-blur">
        <div class="flex justify-between items-center max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-slate-900 text-white text-xs font-semibold">
                    AD
                </span>
                <div>
                    <h1 class="text-lg md:text-xl font-bold text-slate-900">Admin dashboard</h1>
                    <p class="text-xs text-slate-500 hidden sm:block">Manage users, roles, and access control</p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <div class="hidden sm:flex flex-col items-end">
                    <span class="text-sm font-medium text-slate-800">{{ auth()->user()->name }}</span>
                    <span class="text-xs text-slate-500">Administrator</span>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-6 py-8 space-y-6">
        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="p-6 border-b border-slate-200">
                <h2 class="text-base font-semibold text-slate-900">User management</h2>
                <p class="text-xs text-slate-500 mt-1">Search, edit, and block/unblock admin, teacher, and student accounts.</p>

                <form method="GET" action="{{ route('admin.dashboard') }}" class="mt-4">
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search by name or username..."
                            class="flex-1 px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 font-medium">
                            Search
                        </button>
                        @if (request('search'))
                            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 border border-slate-300 rounded-lg hover:bg-slate-50">
                                Clear
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Username</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-slate-600 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-slate-600 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($users as $user)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4 text-sm text-slate-700">{{ $user->id }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700">{{ $user->name }}</td>
                                <td class="px-6 py-4 text-sm text-slate-700">{{ $user->user_name }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        @if($user->role === 'admin') bg-purple-100 text-purple-800
                                        @elseif($user->role === 'teacher') bg-amber-100 text-amber-800
                                        @else bg-blue-100 text-blue-800
                                        @endif">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        {{ $user->active === '1' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $user->active === '1' ? 'Active' : 'Blocked' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                        class="text-blue-600 hover:text-blue-700 text-sm font-medium">Edit</a>
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.toggle-block', $user) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-sm font-medium
                                                {{ $user->active === '1' ? 'text-red-600 hover:text-red-700' : 'text-green-600 hover:text-green-700' }}">
                                                {{ $user->active === '1' ? 'Block' : 'Unblock' }}
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-500">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="px-6 py-4 border-t border-slate-200">
                    {{ $users->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </main>
</div>
@endsection
