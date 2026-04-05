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
        $orgUsers = User::where('organization_id', $user->organization_id)->get(['id', 'name']);

        return Inertia::render('Brand/Outlets/Create', [
            'users' => $orgUsers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $brand = $user->organization?->brands()->first();

        if (! $brand) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
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
