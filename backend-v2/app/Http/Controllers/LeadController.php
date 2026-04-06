<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Mail\LeadEmailOtpMail;
use App\Models\Plan;
use App\Models\RegistrationRequest;
use App\Support\EmailOtpStore;
use App\Support\PhoneNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class LeadController extends Controller
{
    public function create(): Response
    {
        $plans = Plan::where('is_active', true)
            ->orderByRaw('price_monthly = 0, price_monthly')
            ->get(['id', 'name']);

        return Inertia::render('GetStarted', [
            'plans' => $plans,
            'selectedPlanId' => request()->query('plan'),
        ]);
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $token = Str::random(40);

        $lead = RegistrationRequest::create([
            ...$validated,
            'contact_phone' => PhoneNormalizer::normalize($validated['contact_phone']),
            'email_verification_token' => $token,
            'status' => 'pending',
        ]);

        try {
            $code = EmailOtpStore::issue(EmailOtpStore::leadKey($token));
            Mail::to($lead->contact_email)->send(new LeadEmailOtpMail($lead, $code));

            return redirect()
                ->route('get-started.verify.show', $token)
                ->with('status', 'otp-sent');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('get-started.verify.show', $token)
                ->with('status', 'otp-send-failed');
        }
    }

    public function confirmation(): Response
    {
        return Inertia::render('GetStartedConfirmation');
    }

    public function verifyShow(Request $request, string $token): Response|RedirectResponse
    {
        $lead = RegistrationRequest::query()
            ->where('email_verification_token', $token)
            ->firstOrFail();

        if ($lead->email_verified_at !== null) {
            return redirect()->route('get-started.confirmation');
        }

        return Inertia::render('GetStartedVerify', [
            'contactEmail' => $lead->contact_email,
            'status' => $request->session()->get('status'),
            'token' => $token,
        ]);
    }

    public function verifyOtp(Request $request, string $token): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $lead = RegistrationRequest::query()
            ->where('email_verification_token', $token)
            ->firstOrFail();

        if (! EmailOtpStore::verify(EmailOtpStore::leadKey($token), $request->string('code')->value())) {
            return back()->withErrors([
                'code' => 'Invalid or expired code. Please try again.',
            ]);
        }

        $lead->email_verified_at = now();
        $lead->save();

        EmailOtpStore::forget(EmailOtpStore::leadKey($token));

        return redirect()->route('get-started.confirmation');
    }

    public function resendOtp(string $token): RedirectResponse
    {
        $lead = RegistrationRequest::query()
            ->where('email_verification_token', $token)
            ->firstOrFail();

        if ($lead->email_verified_at !== null) {
            return redirect()->route('get-started.confirmation');
        }

        try {
            $code = EmailOtpStore::issue(EmailOtpStore::leadKey($token));
            Mail::to($lead->contact_email)->send(new LeadEmailOtpMail($lead, $code));

            return back()->with('status', 'otp-resent');
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('status', 'otp-send-failed');
        }
    }
}
