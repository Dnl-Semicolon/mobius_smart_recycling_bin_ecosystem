<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\AvatarService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct(private AvatarService $avatarService) {}

    public function index(Request $request): View
    {
        $users = User::query()
            ->when($request->filled('role'), function ($query) use ($request) {
                $query->whereJsonContains('roles', $request->input('role'));
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%'.$request->input('search').'%')
                        ->orWhere('email', 'like', '%'.$request->input('search').'%');
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $roles = UserRole::cases();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        $roles = UserRole::cases();

        return view('admin.users.create', compact('roles'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => Hash::make($request->validated('password')),
            'roles' => $request->validated('roles'),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(Request $request, User $user): View
    {
        $roles = UserRole::cases();
        $tab = $request->query('tab', 'profile');

        return view('admin.users.edit', compact('user', 'roles', 'tab'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = [
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'username' => $request->validated('username'),
            'phone' => $request->validated('phone'),
            'bio' => $request->validated('bio'),
            'roles' => $request->validated('roles'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validated('password'));
        }

        if ($request->hasFile('avatar')) {
            $this->avatarService->delete($user->avatar_path);
            $data['avatar_path'] = $this->avatarService->process($request->file('avatar'));
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('success', 'User updated successfully.');
    }

    public function removeAvatar(User $user): JsonResponse
    {
        if ($user->avatar_path) {
            $this->avatarService->delete($user->avatar_path);
            $user->update(['avatar_path' => null]);
        }

        return response()->json(['message' => 'Avatar removed.']);
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        if ($user->avatar_path) {
            $this->avatarService->delete($user->avatar_path);
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
