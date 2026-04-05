<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * WHAT THIS DOES:
 * Checks if the logged-in user has the required role(s) to access a route.
 *
 * HOW IT'S USED IN ROUTES:
 *   ->middleware('role:admin')              // only admins
 *   ->middleware('role:admin,collector')    // admins OR collectors
 *
 * WHAT HAPPENS:
 * 1. Laravel calls this middleware BEFORE your controller runs
 * 2. We grab the logged-in user from the request
 * 3. We check if any of their roles is in the allowed list
 * 4. If yes → continue to the controller
 * 5. If no → return 403 Forbidden (or redirect to login page for web)
 */
class EnsureUserHasRole
{
    /**
     * @param  string  ...$roles  One or more role values like 'admin', 'collector'
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Not logged in? Shouldn't happen if 'auth' middleware runs first,
        // but just in case — bounce them.
        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Unauthenticated.'], 401)
                : abort(403, 'You do not have permission to access this page.');
        }

        // Convert the string role names ('admin', 'collector') into UserRole enums
        $allowedRoles = array_map(fn (string $role) => UserRole::from($role), $roles);

        // Check if any of the user's roles match any of the allowed roles
        $userRoles = $user->getRolesArray();
        $hasAllowed = collect($allowedRoles)->contains(fn (UserRole $r) => in_array($r->value, $userRoles, true));

        if (! $hasAllowed) {
            // User is logged in but doesn't have the right role.
            // For API requests: return JSON 403.
            // For web requests: abort with a 403 page.
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden. You do not have the required role.'], 403);
            }

            abort(403, 'You do not have permission to access this page.');
        }

        // User has the right role — let the request through to the controller.
        return $next($request);
    }
}
