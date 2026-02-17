@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="min-h-screen">
    <nav class="bg-white border-b border-slate-200 px-6 py-4">
        <div class="flex justify-between items-center max-w-7xl mx-auto">
            <h1 class="text-xl font-bold text-slate-800">QAMS - Admin Dashboard</h1>
            <a href="{{ route('admin.dashboard') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Back to Dashboard</a>
        </div>
    </nav>
    <main class="max-w-2xl mx-auto px-6 py-8">
        <div class="bg-white rounded-xl shadow border border-slate-200 p-8">
            <h2 class="text-lg font-semibold text-slate-800 mb-6">Edit User: {{ $user->name }}</h2>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="text-sm text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-5">
                @csrf
                @method('PUT')
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                        maxlength="30"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="user_name" class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                    <input type="text" name="user_name" id="user_name" value="{{ old('user_name', $user->user_name) }}" required
                        maxlength="30"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="role" class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                    <select name="role" id="role" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="teacher" {{ old('role', $user->role) == 'teacher' ? 'selected' : '' }}>Teacher</option>
                        <option value="student" {{ old('role', $user->role) == 'student' ? 'selected' : '' }}>Student</option>
                    </select>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1">New Password (leave blank to keep current)</label>
                    <input type="password" name="password" id="password"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirm New Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700">
                        Update User
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="px-6 py-2 border border-slate-300 rounded-lg hover:bg-slate-50 font-medium">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection
