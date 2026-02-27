<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * API token-based authentication for Flutter (and any other mobile/external client).
 *
 * HOW THIS WORKS WITH FLUTTER:
 *
 * 1. Flutter sends POST /api/v1/auth/login with { email, password, device_name }
 * 2. We verify the credentials against the users table
 * 3. If valid, we create a Sanctum token (random string stored in personal_access_tokens table)
 * 4. We return the plain-text token to Flutter
 * 5. Flutter stores it in SharedPreferences (or flutter_secure_storage)
 * 6. Every future request from Flutter includes the header:
 *       Authorization: Bearer 3|abc123xyz...
 * 7. Laravel Sanctum reads that header, looks up the token, identifies the user
 * 8. The user stays "logged in" until they call /auth/logout (which deletes the token)
 *
 * KEY DIFFERENCE FROM WEB AUTH:
 * - Web uses cookies/sessions (browser handles this automatically)
 * - API uses tokens (Flutter must store and send the token manually)
 * - Both authenticate the same users table — same users, same roles
 */
class AuthController extends Controller
{
    /**
     * Login and get a Bearer token.
     *
     * Flutter calls: POST /api/v1/auth/login
     * Body: { "email": "...", "password": "...", "device_name": "Daniel's iPhone" }
     * Returns: { "token": "3|abc123...", "user": { ... } }
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'device_name' => ['required', 'string'],  // e.g. "iPhone 15", "Pixel 8"
        ]);

        $user = User::where('email', $request->email)->first();

        // Check if user exists and password matches.
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Create a new Sanctum token for this device.
        // The device_name helps identify which token belongs to which device
        // (useful if a user logs in from multiple phones).
        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
            ],
        ]);
    }

    /**
     * Logout — delete the current token.
     *
     * Flutter calls: POST /api/v1/auth/logout
     * Header: Authorization: Bearer {token}
     * Flutter should then delete the token from SharedPreferences.
     */
    public function logout(Request $request): JsonResponse
    {
        // Delete ONLY the token that was used to authenticate this request.
        // If the user is logged in on multiple devices, the other tokens remain valid.
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * Get the currently authenticated user's info.
     *
     * Flutter calls: GET /api/v1/auth/user
     * Header: Authorization: Bearer {token}
     * Useful for checking if the stored token is still valid on app startup.
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
        ]);
    }
}
