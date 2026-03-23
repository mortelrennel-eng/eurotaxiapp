<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
            ->where('is_active', 1)
            ->first();

        if ($user) {
            // Support both 'password' and legacy 'password_hash' column names
            $storedHash = $user->password ?? $user->password_hash ?? null;

            if (
                $storedHash && (
                    Hash::check($request->password, $storedHash) ||
                    password_verify($request->password, $storedHash)
                )
            ) {
                Auth::login($user, $request->boolean('remember'));
                $request->session()->regenerate();
                return redirect()->intended(route('dashboard'))
                    ->with('success', 'Welcome back, ' . ($user->full_name ?? $user->name) . '!');
            }
        }

        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ])->onlyInput('email');
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:25', 'regex:/^[a-zA-Z]+$/'],
            'last_name' => ['required', 'string', 'max:25', 'regex:/^[a-zA-Z]+( [a-zA-Z]+)?$/'],
            'email' => ['required', 'email', 'unique:users,email', 'regex:/^(?!.*\.{2})[a-zA-Z][a-zA-Z0-9.]{4,28}[a-zA-Z0-9]@gmail\.com$/i'],
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:staff,secretary,manager,dispatcher',
        ], [
            'first_name.regex' => 'Ang first name ay dapat mga letra lamang at bawal ang spacing o numbers.',
            'first_name.max' => 'Ang first name ay hanggang 25 characters lamang.',
            'last_name.regex' => 'Ang last name ay dapat mga letra lamang at isang spacing lamang ang pinapayagan.',
            'last_name.max' => 'Ang last name ay hanggang 25 characters lamang.',
            'email.regex' => 'Gmail address lamang ang tinatanggap (halimbawa: yourname@gmail.com).',
            'email.unique' => 'Ang email na ito ay may existing na account na.',
        ]);

        // Generate username based on role and first name
        $rolePrefix = $request->role;
        $firstName = strtolower(str_replace(' ', '', $request->first_name));
        $username = $rolePrefix . '-' . $firstName;
        
        // Ensure unique username
        $originalUsername = $username;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . '-' . $counter;
            $counter++;
        }

        $user = User::create([
            'full_name' => $request->first_name . ' ' . $request->last_name,
            'email' => $request->email,
            'username' => $username,
            'password' => Hash::make($request->password),
            'password_hash' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Account created successfully!');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
