@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-slate-800">QAMS</h1>
                <p class="text-slate-600 mt-1">Quiz and Assignment Management System</p>
                <h2 class="text-lg font-semibold text-slate-700 mt-6">Registration</h2>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="text-sm text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        maxlength="30" autofocus
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="user_name" class="block text-sm font-medium text-slate-700 mb-1">Username</label>
                    <input type="text" name="user_name" id="user_name" value="{{ old('user_name') }}" required
                        maxlength="30"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" id="password" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="role" class="block text-sm font-medium text-slate-700 mb-1">Role</label>
                    <select name="role" id="role" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                        <option value="teacher" {{ old('role') == 'teacher' ? 'selected' : '' }}>Teacher</option>
                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                <button type="submit" class="w-full py-3 px-4 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Register
                </button>
            </form>
            <p class="mt-6 text-center text-sm text-slate-600">
                Already have an account? <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500">Login</a>
            </p>
        </div>
    </div>
</div>
@endsection
