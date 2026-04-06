<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\RegisterEmailOtpMail;
use App\Support\EmailOtpStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class EmailOtpController extends Controller
{
    public function show(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user->email_verified_at !== null) {
            return redirect()->route('public.dashboard');
        }

        return Inertia::render('auth/verify-email-otp', [
            'email' => $user->email,
            'status' => $request->session()->get('status'),
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();
        $cacheKey = EmailOtpStore::userKey($user->id);

        if (! EmailOtpStore::verify($cacheKey, $request->string('code')->value())) {
            return back()->withErrors([
                'code' => 'Invalid or expired code. Please try again.',
            ]);
        }

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        EmailOtpStore::forget($cacheKey);

        return redirect()->route('public.dashboard');
    }

    public function resend(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->email_verified_at !== null) {
            return redirect()->route('public.dashboard');
        }

        try {
            $code = EmailOtpStore::issue(EmailOtpStore::userKey($user->id));

            Mail::to($user->email)->send(new RegisterEmailOtpMail($user, $code));

            return back()->with('status', 'otp-resent');
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('status', 'otp-send-failed');
        }
    }
}
