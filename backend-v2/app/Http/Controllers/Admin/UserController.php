<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->orderBy('id')
            ->paginate(15)
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'roles' => $user->getRolesArray(),
                'organization_id' => $user->organization_id,
                'points_balance' => $user->points_balance,
                'created_at' => $user->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }
}
