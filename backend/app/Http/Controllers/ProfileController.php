<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\AvatarService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __construct(private AvatarService $avatarService) {}

    public function edit(Request $request): View
    {
        $user = auth()->user();
        $tab = $request->query('tab', 'profile');

        $view = match ($user->primaryRole()) {
            UserRole::Admin => 'admin.profile.edit',
            UserRole::StoreOwner => 'store-owner.profile.edit',
            UserRole::AgencyAdmin => 'agency.profile.edit',
            UserRole::Collector, UserRole::PublicUser => 'collector.profile.edit',
        };

        return view($view, compact('user', 'tab'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->safe()->except('avatar');

        if ($request->hasFile('avatar')) {
            $this->avatarService->delete($user->avatar_path);
            $data['avatar_path'] = $this->avatarService->process($request->file('avatar'));
        }

        $user->update($data);

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    public function removeAvatar(): RedirectResponse
    {
        $user = auth()->user();

        if ($user->avatar_path) {
            $this->avatarService->delete($user->avatar_path);
            $user->update(['avatar_path' => null]);
        }

        return redirect()->back()->with('success', 'Profile picture removed.');
    }

    public function password(ChangePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()->back()->with('success', 'Password changed successfully.');
    }

    public function sendOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $user = $request->user();
        $phone = $request->input('phone');

        if ($user->phone !== $phone) {
            $user->update(['phone' => $phone, 'phone_verified_at' => null]);
        }

        // Send real SMS via Twilio Verify
        try {
            $twilio = new \Twilio\Rest\Client(
                config('services.twilio.sid'),
                config('services.twilio.token'),
            );

            $twilio->verify->v2
                ->services(config('services.twilio.verify_sid'))
                ->verifications
                ->create($phone, 'sms');

            return redirect()->back()->with('success', 'OTP sent to '.$phone.'. Check your phone.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to send OTP: '.$e->getMessage());
        }
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();
        $phone = $user->phone;

        if (! $phone) {
            return redirect()->back()->with('error', 'No phone number on file. Send OTP first.');
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
                    'to' => $phone,
                    'code' => $request->input('otp'),
                ]);

            if ($check->status === 'approved') {
                $user->update(['phone_verified_at' => now()]);

                return redirect()->back()->with('success', 'Phone number verified successfully!');
            }

            return redirect()->back()->with('error', 'Invalid OTP code. Please try again.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Verification failed: '.$e->getMessage());
        }
    }
}
