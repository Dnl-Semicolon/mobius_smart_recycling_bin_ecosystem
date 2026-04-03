<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login and get a Bearer token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'device_name' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    /**
     * Register a new public user and get a Bearer token.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'roles' => ['public_user'],
            'profile_photo_path' => '',
            'points_balance' => 0,
            'current_streak' => 0,
            'longest_streak' => 0,
        ]);

        $token = $user->createToken($request->validated('device_name'))->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user),
        ], 201);
    }

    /**
     * Logout -- delete the current token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * Get the currently authenticated user's info.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json($this->userPayload($user));
    }

    /**
     * @return array<string, mixed>
     */
    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'phone_verified_at' => $user->phone_verified_at?->toIso8601String(),
            'organization_id' => $user->organization_id,
            'role' => $user->getRolesArray()[0],
            'roles' => $user->getRolesArray(),
            'points_balance' => $user->points_balance ?? 0,
            'current_streak' => $user->current_streak ?? 0,
            'longest_streak' => $user->longest_streak ?? 0,
            'last_recycled_at' => $user->last_recycled_at?->toIso8601String(),
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }
}
