<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Handles web-based login (browser sessions, NOT API tokens).
 *
 * Flow:
 * 1. User visits /login → showLoginForm() → sees the login page
 * 2. User submits email + password → login() → validates → Auth::attempt()
 * 3. If valid → redirect based on role (admin → /admin, collector → /collector, etc.)
 * 4. If invalid → back to login with error
 */
class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Auth::attempt checks the email + password against the users table.
        // If correct, it creates a session cookie — the user is now "logged in".
        // The 'remember' checkbox creates a long-lived cookie so the session
        // survives even after the browser is closed.
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        // Regenerate the session ID to prevent session fixation attacks.
        $request->session()->regenerate();

        // Redirect based on the user's role:
        return match ($request->user()->primaryRole()) {
            UserRole::Admin => redirect()->intended(route('admin.dashboard')),
            UserRole::Collector => redirect()->intended(route('collector.dashboard')),
            UserRole::StoreOwner => redirect()->intended(route('store.dashboard')),
            UserRole::AgencyAdmin => redirect()->intended(route('agency.dashboard')),
            UserRole::PublicUser => redirect()->intended(route('public.dashboard')),
            default => redirect()->intended('/'),
        };
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        // Invalidate the session and regenerate the CSRF token.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
