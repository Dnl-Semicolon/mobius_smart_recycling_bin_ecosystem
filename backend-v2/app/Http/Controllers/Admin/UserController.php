<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Rules\MalaysianMobilePhone;
use App\Support\EmailNormalizer;
use App\Support\PhoneNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->with('organization:id,name')
            ->orderBy('id')
            ->paginate(15)
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'roles' => $user->getRolesArray(),
                'organization' => $user->organization?->name,
                'points_balance' => $user->points_balance,
                'created_at' => $user->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    public function create(): Response
    {
        $organizations = Organization::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Users/Create', [
            'organizations' => $organizations,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => EmailNormalizer::normalize($request->input('email')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', new MalaysianMobilePhone],
            'role' => ['required', 'in:brand_owner,store_owner,collector,public_user'],
            'organization_id' => ['nullable', 'exists:organizations,id'],
        ]);

        $password = Str::random(12);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => PhoneNormalizer::normalize($validated['phone'] ?? null),
            'organization_id' => $validated['organization_id'],
            'password' => Hash::make($password),
            'email_verified_at' => now(),
            'roles' => [$validated['role']],
        ]);

        return redirect()->route('admin.users.index')->with('generated_password', $password);
    }
}
