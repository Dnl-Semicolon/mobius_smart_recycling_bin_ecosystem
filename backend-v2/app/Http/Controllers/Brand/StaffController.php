<?php

namespace App\Http\Controllers\Brand;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        $staff = User::where('organization_id', $user->organization_id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
                'roles' => $u->getRolesArray(),
                'created_at' => $u->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Brand/Staff/Index', [
            'staff' => $staff,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Brand/Staff/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $password = Str::random(12);

        User::create([
            'organization_id' => $user->organization_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($password),
            'email_verified_at' => now(),
            'roles' => ['store_owner'],
        ]);

        return redirect()->route('brand.staff.index')->with('generated_password', $password);
    }
}
