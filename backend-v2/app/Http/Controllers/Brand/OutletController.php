<?php

namespace App\Http\Controllers\Brand;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OutletController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $brand = $user->organization?->brands()->first();

        $outlets = $brand
            ? Outlet::where('brand_id', $brand->id)
                ->with(['storeOwner:id,name', 'bins'])
                ->orderByDesc('created_at')
                ->paginate(15)
                ->through(fn (Outlet $outlet) => [
                    'id' => $outlet->id,
                    'name' => $outlet->name,
                    'address' => $outlet->address,
                    'manager' => $outlet->storeOwner?->name ?? '-',
                    'bin_count' => $outlet->bins->count(),
                    'is_active' => $outlet->is_active,
                ])
            : ['data' => [], 'total' => 0, 'links' => [], 'last_page' => 1];

        return Inertia::render('Brand/Outlets/Index', [
            'outlets' => $outlets,
            'brandName' => $brand?->name,
        ]);
    }

    public function create(): Response
    {
        $user = auth()->user();
        $org = $user->organization;
        $orgUsers = User::where('organization_id', $user->organization_id)->get(['id', 'name']);

        return Inertia::render('Brand/Outlets/Create', [
            'users' => $orgUsers,
            'limitInfo' => $org?->getLimitInfo('outlet_limit'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $brand = $user->organization?->brands()->first();

        if (! $brand) {
            abort(403);
        }

        $org = $user->organization;
        if ($org && $org->hasReachedLimit('outlet_limit')) {
            return back()->withErrors(['limit' => 'Outlet limit reached for your plan. Upgrade to add more outlets.']);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'street' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'string', 'max:10'],
            'country' => ['nullable', 'string', 'max:255'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        Outlet::create([
            ...$validated,
            'brand_id' => $brand->id,
            'is_active' => true,
        ]);

        return redirect()->route('brand.outlets.index');
    }
}
