<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use App\Mail\RegistrationVerificationMail;
use App\Models\RegistrationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BrandRegistrationController extends Controller
{
    public function create(): View
    {
        return view('registration.brand');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:20'],
            'type' => ['required', 'in:beverage_company,recycling_company,government'],
            'description' => ['required', 'string'],
        ]);

        // Normalize phone to +60XXXXXXXXX
        $phone = preg_replace('/[^\d+]/', '', $validated['contact_phone']);
        if (! str_starts_with($phone, '+')) {
            $phone = '+60'.ltrim($phone, '0');
        }
        $validated['contact_phone'] = $phone;

        // Store form data in session, send OTP before creating the request
        $request->session()->put('registration_data', $validated);

        try {
            $twilio = new \Twilio\Rest\Client(
                config('services.twilio.sid'),
                config('services.twilio.token'),
            );

            $twilio->verify->v2
                ->services(config('services.twilio.verify_sid'))
                ->verifications
                ->create($phone, 'sms');

            return redirect()->route('registration.verify-phone')
                ->with('success', 'OTP sent to '.$phone.'. Please verify your phone number.');
        } catch (\Exception $e) {
            // If Twilio fails, still create the request but mark phone as unverified
            $validated['status'] = 'pending';
            $validated['admin_notes'] = 'Phone not verified (OTP send failed)';
            RegistrationRequest::create($validated);
            $request->session()->forget('registration_data');

            return redirect()->route('registration.success')
                ->with('success', 'Application submitted. Phone verification was skipped.');
        }
    }

    public function verifyPhone(): View
    {
        return view('registration.verify-phone');
    }

    public function confirmPhone(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $data = $request->session()->get('registration_data');
        if (! $data) {
            return redirect()->route('registration.brand.create')
                ->with('error', 'Session expired. Please fill the form again.');
        }

        try {
            $twilio = new \Twilio\Rest\Client(
                config('services.twilio.sid'),
                config('services.twilio.token'),
            );

            $check = $twilio->verify->v2
                ->services(config('services.twilio.verify_sid'))
                ->verificationChecks
                ->create([
                    'to' => $data['contact_phone'],
                    'code' => $request->input('otp'),
                ]);

            if ($check->status !== 'approved') {
                return redirect()->back()->with('error', 'Invalid OTP code. Please try again.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Verification failed: '.$e->getMessage());
        }

        // OTP verified — create the registration request with email verification token
        $data['phone_verified_at'] = now();
        $data['email_verification_token'] = Str::uuid();
        $data['status'] = 'pending';
        $data['admin_notes'] = 'Phone verified via OTP. Awaiting email verification.';

        $registrationRequest = RegistrationRequest::create($data);
        $request->session()->forget('registration_data');

        // Send email verification mail
        Mail::to($registrationRequest->contact_email)
            ->send(new RegistrationVerificationMail($registrationRequest));

        return redirect()->route('registration.success')
            ->with('success', 'Phone verified! Check your inbox to verify your email.');
    }

    public function verifyEmail(string $token): RedirectResponse
    {
        $registrationRequest = RegistrationRequest::where('email_verification_token', $token)->first();

        if (! $registrationRequest) {
            return redirect()->route('registration.success')
                ->with('error', 'Invalid or expired verification link.');
        }

        if ($registrationRequest->email_verified_at) {
            return redirect()->route('registration.success')
                ->with('error', 'This email has already been verified.');
        }

        $registrationRequest->update([
            'email_verified_at' => now(),
            'admin_notes' => 'Phone and email verified',
        ]);

        return redirect()->route('registration.success')
            ->with('success', 'Email verified! Your application is now under review.');
    }
}
