<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
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
        $outlets = Outlet::query()
            ->with(['brand:id,name', 'storeOwner:id,name', 'bins'])
            ->orderByDesc('created_at')
            ->paginate(15)
            ->through(fn (Outlet $outlet) => [
                'id' => $outlet->id,
                'name' => $outlet->name,
                'address' => $outlet->address,
                'brand' => $outlet->brand->name,
                'store_owner' => $outlet->storeOwner?->name ?? '-',
                'bin_count' => $outlet->bins->count(),
                'is_active' => $outlet->is_active,
                'created_at' => $outlet->created_at->format('Y-m-d'),
            ]);

        return Inertia::render('Admin/Outlets/Index', [
            'outlets' => $outlets,
        ]);
    }

    public function create(): Response
    {
        $brands = Brand::with('organization')->where('is_active', true)->get(['id', 'name', 'organization_id']);

        $users = User::whereIn('organization_id', $brands->pluck('organization_id')->unique())
            ->get(['id', 'name', 'organization_id']);

        // Pass limit info per org so frontend can show "2 of 3 used" when brand is selected
        $orgLimits = [];
        foreach ($brands->pluck('organization')->unique('id') as $org) {
            if ($org) {
                $orgLimits[$org->id] = $org->getLimitInfo('outlet_limit');
            }
        }

        return Inertia::render('Admin/Outlets/Create', [
            'brands' => $brands,
            'users' => $users,
            'orgLimits' => $orgLimits,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'brand_id' => ['required', 'exists:brands,id'],
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

        // Enforce outlet limit
        $brand = Brand::find($validated['brand_id']);
        $org = $brand?->organization;
        if ($org && $org->hasReachedLimit('outlet_limit')) {
            return back()->withErrors(['limit' => 'Outlet limit reached for this organization\'s plan. Upgrade to add more outlets.']);
        }

        Outlet::create([
            ...$validated,
            'is_active' => true,
        ]);

        return redirect()->route('admin.outlets.index');
    }
}
