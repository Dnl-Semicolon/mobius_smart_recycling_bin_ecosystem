<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use App\Services\AvatarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function __construct(private AvatarService $avatarService) {}

    /**
     * Update the authenticated user's profile fields.
     * Supports multipart (with avatar) or JSON (text only).
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->safe()->except(['avatar', 'email']);

        if ($request->hasFile('avatar')) {
            $this->avatarService->delete($user->avatar_path);
            $data['avatar_path'] = $this->avatarService->process($request->file('avatar'));
        }

        $user->update($data);

        return response()->json($this->userPayload($user->fresh()));
    }

    /**
     * Upload or replace the user's avatar.
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048'],
        ]);

        $user = $request->user();

        $this->avatarService->delete($user->avatar_path);
        $user->update([
            'avatar_path' => $this->avatarService->process($request->file('avatar')),
        ]);

        return response()->json($this->userPayload($user->fresh()));
    }

    /**
     * Remove the user's avatar.
     */
    public function removeAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->avatar_path) {
            $this->avatarService->delete($user->avatar_path);
            $user->update(['avatar_path' => null]);
        }

        return response()->json($this->userPayload($user->fresh()));
    }

    /**
     * Change the user's password.
     */
    public function password(ChangePasswordRequest $request): JsonResponse
    {
        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Password updated successfully.']);
    }

    /**
     * Check if a username is available.
     */
    public function checkUsername(Request $request): JsonResponse
    {
        $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:255', 'regex:/^[a-z0-9_]+$/'],
            'except' => ['nullable', 'integer'],
        ]);

        $query = User::where('username', $request->username);

        if ($request->except) {
            $query->where('id', '!=', $request->except);
        }

        $taken = $query->exists();

        return response()->json([
            'available' => ! $taken,
            'message' => $taken ? 'This username is already taken.' : 'Username is available.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(\App\Models\User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone ?? null,
            'bio' => $user->bio,
            'avatar_url' => $user->avatar_path ? asset('storage/'.$user->avatar_path) : null,
            'role' => $user->getRolesArray()[0],
            'roles' => $user->getRolesArray(),
            'points_balance' => $user->points_balance ?? 0,
            'current_streak' => $user->current_streak ?? 0,
            'longest_streak' => $user->longest_streak ?? 0,
            'last_recycled_at' => $user->last_recycled_at,
            'created_at' => $user->created_at,
        ];
    }
}
