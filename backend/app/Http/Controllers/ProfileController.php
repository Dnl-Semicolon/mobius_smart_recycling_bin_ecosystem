<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();

        $view = match ($user->role) {
            UserRole::Admin => 'admin.profile.edit',
            UserRole::Collector => 'collector.profile.edit',
            default => 'collector.profile.edit',
        };

        return view($view, compact('user'));
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    public function password(ChangePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->validated('password')),
        ]);

        return redirect()->back()->with('success', 'Password changed successfully.');
    }
}
