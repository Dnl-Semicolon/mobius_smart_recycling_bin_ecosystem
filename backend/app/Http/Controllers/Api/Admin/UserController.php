<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->where('role', $request->input('role'));
            })
            ->latest()
            ->paginate();

        return response()->json([
            'data' => $users->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
                'created_at' => $user->created_at?->toIso8601String(),
            ]),
            'message' => 'Users retrieved successfully.',
        ]);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
                'created_at' => $user->created_at?->toIso8601String(),
            ],
            'message' => 'User retrieved successfully.',
        ]);
    }
}
