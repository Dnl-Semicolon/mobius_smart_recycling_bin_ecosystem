<?php

namespace App\Http\Controllers\StoreOwner;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\StoreOwnerContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function __construct(private StoreOwnerContext $context) {}

    public function index(Request $request): View
    {
        $ctx = $this->context->resolve($request->user());
        abort_unless($ctx->isHQ, 403, 'Only brand administrators can manage staff.');

        $staffByOutlet = $ctx->outlets->map(function ($outlet) {
            return [
                'outlet' => $outlet,
                'managers' => $outlet->managers()->get(),
            ];
        });

        $brand = $ctx->brand;
        $outlets = $ctx->outlets;

        return view('store-owner.staff.index', compact('brand', 'outlets', 'staffByOutlet'));
    }

    public function invite(Request $request): RedirectResponse
    {
        $ctx = $this->context->resolve($request->user());
        abort_unless($ctx->isHQ, 403, 'Only brand administrators can invite staff.');

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'outlet_id' => ['required', 'integer', 'exists:outlets,id'],
        ]);

        $outlet = $ctx->outlets->firstWhere('id', $validated['outlet_id']);
        if (! $outlet) {
            return back()->withErrors(['outlet_id' => 'This outlet does not belong to your brand.'])->withInput();
        }

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            $user = User::create([
                'name' => Str::before($validated['email'], '@'),
                'email' => $validated['email'],
                'password' => Hash::make(Str::random(32)),
                'roles' => ['public_user'],
            ]);

            Log::info('Branch manager account created — welcome email would be sent', [
                'user_id' => $user->id,
                'email' => $validated['email'],
                'brand_id' => $ctx->brand->id,
            ]);
        }

        if (! $outlet->managers()->where('user_id', $user->id)->exists()) {
            $outlet->managers()->attach($user->id, ['role' => 'manager']);
        }

        $user->addRole(UserRole::StoreOwner);

        return redirect()->route('store.staff.index')
            ->with('success', "Invited {$user->email} as manager of {$outlet->name}.");
    }

    public function remove(Request $request, User $user): RedirectResponse
    {
        $ctx = $this->context->resolve($request->user());
        abort_unless($ctx->isHQ, 403, 'Only brand administrators can remove staff.');

        $outletIds = $ctx->outlets->pluck('id');
        $user->outlets()->detach($outletIds);

        if ($user->outlets()->count() === 0 && $user->id !== $ctx->brand->user_id) {
            $user->removeRole(UserRole::StoreOwner);
        }

        return redirect()->route('store.staff.index')
            ->with('success', "Removed {$user->name} from staff.");
    }
}
