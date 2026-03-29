<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $users = User::query()
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->whereJsonContains('roles', $request->input('role'));
            })
            ->latest()
            ->paginate();

        return UserResource::collection($users)
            ->additional(['message' => 'Users retrieved successfully.'])
            ->response();
    }

    public function show(User $user): \Illuminate\Http\JsonResponse
    {
        return UserResource::make($user)
            ->additional(['message' => 'User retrieved successfully.'])
            ->response();
    }
}
