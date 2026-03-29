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
}
