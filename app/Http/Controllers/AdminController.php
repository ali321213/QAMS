<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard with all users.
     */
    public function index(Request $request)
    {
        $query = User::query()->orderBy('role')->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%");
            })->where('role', 'student');
        }

        $users = $query->paginate(15);

        return view('admin.dashboard', compact('users'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('admin.edit', compact('user'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:30'],
            'user_name' => ['required', 'string', 'max:30', 'unique:users,user_name,'.$user->id],
            'role' => ['required', 'string', 'in:admin,teacher,student'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->user_name = $validated['user_name'];
        $user->role = $validated['role'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.dashboard')->with('success', 'User updated successfully.');
    }

    /**
     * Toggle block/unblock status of the specified user.
     */
    public function toggleBlock(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot block your own account.']);
        }

        $user->active = $user->active === '1' ? '0' : '1';
        $user->save();

        $status = $user->active === '1' ? 'unblocked' : 'blocked';

        return back()->with('success', "User {$status} successfully.");
    }
}
