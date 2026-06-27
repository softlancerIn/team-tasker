<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function loginPage()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('dashboard');
        }

        if (Auth::check()) {
            $user = Auth::user();

            return redirect()->route($user->role_id == 3 ? 'client.dashboard' : 'dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // First attempt as Super Admin using 'admin' guard
        if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->route('dashboard');
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        if (! $user->is_approved) {
            return redirect()->back()->withInput()->with('error', 'Your account is pending admin approval.');
        }

        Auth::login($user);

        return redirect()->route($user->role_id == 3 ? 'client.dashboard' : 'dashboard');
    }

    public function registerPage()
    {
        if (Auth::check()) {
            $user = Auth::user();

            return redirect()->route($user->role_id == 3 ? 'client.dashboard' : 'dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.unique' => 'This email already exists in our system.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => null, // Admin will assign role upon approval
            'is_approved' => false,
        ]);

        return redirect()->route('loginPage')->with('success', 'Registration successful! Please wait for admin approval before logging in.');
    }

    public function clientRegisterPage()
    {
        if (Auth::check()) {
            $user = Auth::user();

            return redirect()->route($user->role_id == 3 ? 'client.dashboard' : 'dashboard');
        }

        return view('auth.client-register');
    }

    public function clientRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.unique' => 'This email already exists in our system.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => 3, // Client Role
            'is_approved' => false, // Requires admin approval
        ]);

        return redirect()->route('loginPage')->with('success', 'Client registration successful! Please wait for admin approval before logging in.');
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        Auth::logout();

        return to_route('loginPage');
    }

    public function forgotPasswordPage()
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        // In a real app, we would send an email with a token.
        // For this demo/requirement, we'll redirect directly to a reset page with the email.
        return redirect()->route('resetPasswordPage', ['email' => $request->email])
            ->with('success', 'Reset link sent! (Simulated: Redirected to reset page)');
    }

    public function resetPasswordPage(Request $request)
    {
        return view('auth.reset-password', ['email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);

        return redirect()->route('loginPage')->with('success', 'Password reset successfully! You can now log in.');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profiles', 'public');
            $data['profile_image'] = $path;
        }

        $user->update($data);

        return back()->with('success', 'Profile updated successfully!');
    }
}
