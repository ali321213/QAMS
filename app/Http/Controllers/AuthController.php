<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    // Show the registration form.
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    // Handle registration request.
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:30'],
            'user_name' => ['required', 'string', 'max:30', 'unique:users,user_name'],
            'password' => ['required', 'string', 'confirmed', 'max:50', Password::defaults()],
            'role' => ['required', 'string', 'in:admin,teacher,student'],
        ], [
            'user_name.unique' => 'This username is already taken.',
        ]);
        User::create([
            'name' => $validated['name'],
            'user_name' => $validated['user_name'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'active' => '1',
        ]);
        return redirect()->route('login')->with('success', 'Registration successful. You can now login.');
    }

    // Show the login form.
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Handle login request.
    public function login(Request $request)
    {
        dd("reached here");
        $credentials = $request->validate([
            'user_name' => ['required', 'string'],
            'password' => ['required'],
        ]);
        $user = User::where('user_name', $credentials['user_name'])->first();
        if (! $user) {
            return back()->withErrors([
                'user_name' => 'Invalid credentials.',
            ])->onlyInput('user_name');
        }
        if ($user->active !== '1') {
            return back()->withErrors([
                'user_name' => 'Your account has been blocked. Please contact the administrator.',
            ])->onlyInput('user_name');
        }
        if (! Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'user_name' => 'Invalid credentials.',
            ])->onlyInput('user_name');
        }
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    // Handle logout request.
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
