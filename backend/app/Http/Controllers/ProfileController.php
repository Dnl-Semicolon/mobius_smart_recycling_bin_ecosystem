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

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $request->session()->put('otp_code', $code);
        $request->session()->put('otp_phone', $request->input('phone'));
        $request->session()->put('otp_expires_at', now()->addMinutes(10)->timestamp);

        $user = $request->user();
        if ($user->phone !== $request->input('phone')) {
            $user->update(['phone' => $request->input('phone')]);
        }

        return redirect()->back()->with('otp_code', $code)->with('success', "Your OTP code is: {$code}");
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $sessionCode = $request->session()->get('otp_code');
        $expiresAt = $request->session()->get('otp_expires_at');

        if (! $sessionCode || ! $expiresAt) {
            return redirect()->back()->with('error', 'No OTP was sent. Please request a new one.');
        }

        if (now()->timestamp > $expiresAt) {
            $request->session()->forget(['otp_code', 'otp_phone', 'otp_expires_at']);

            return redirect()->back()->with('error', 'OTP has expired. Please request a new one.');
        }

        if ($request->input('otp') !== $sessionCode) {
            return redirect()->back()->with('error', 'Invalid OTP code. Please try again.');
        }

        $request->user()->update(['phone_verified_at' => now()]);
        $request->session()->forget(['otp_code', 'otp_phone', 'otp_expires_at']);

        return redirect()->back()->with('success', 'Phone number verified successfully!');
    }
}
