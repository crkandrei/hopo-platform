<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isStaff()) {
                return redirect(($user->location && !$user->location->bracelet_required) ? url('/start-session') : url('/scan'));
            }
            return redirect(url('/dashboard'));
        }

        return view('auth.login');
    }

    /**
     * Login utilizator
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required',
        ]);

        // Găsește utilizatorul după username
        $user = User::where('username', $request->username)->first();

        // Verifică dacă utilizatorul există și parola este corectă
        if (!$user || !Hash::check($request->password, $user->password)) {
            Log::warning('Login failed: invalid credentials', [
                'username' => $request->username,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return back()->withErrors([
                'username' => 'Credențialele furnizate sunt incorecte.',
            ]);
        }

        // Verifică dacă utilizatorul este activ
        if (!$user->isActive()) {
            Log::warning('Login failed: inactive account', [
                'username' => $request->username,
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
            ]);
            return back()->withErrors([
                'username' => 'Contul este inactiv.',
            ]);
        }

        // Autentifică utilizatorul
        Auth::login($user, true); // true = remember me
        $request->session()->regenerate();

        // Înregistrează ultima accesare
        $user->update(['last_login_at' => now()]);
        
        // Redirect bazat pe rol
        if ($user->isStaff()) {
            $destination = ($user->location && !$user->location->bracelet_required) ? url('/start-session') : url('/scan');
            return redirect($destination);
        }

        return redirect()->intended(url('/dashboard'));
    }

    /**
     * Logout utilizator
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect(url('/login'));
    }

    /**
     * Show change password form
     */
    public function showChangePasswordForm()
    {
        return view('auth.change-password');
    }

    /**
     * Schimbă parola utilizatorului
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors([
                'current_password' => 'Parola curentă este incorectă.',
            ]);
        }

        $user->update([
            'password' => $request->new_password,
        ]);

        return back()->with('success', 'Parola a fost schimbată cu succes!');
    }
}
