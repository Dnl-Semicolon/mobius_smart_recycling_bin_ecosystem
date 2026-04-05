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
        $brands = Brand::where('is_active', true)->get(['id', 'name']);
        $storeOwners = User::whereJsonContains('roles', 'store_owner')->get(['id', 'name']);
        $brandOwners = User::whereJsonContains('roles', 'brand_owner')->get(['id', 'name']);

        return Inertia::render('Admin/Outlets/Create', [
            'brands' => $brands,
            'owners' => $storeOwners->merge($brandOwners)->unique('id')->values(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'brand_id' => ['required', 'exists:brands,id'],
            'user_id' => ['required', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'latitude' => ['required', 'numeric'],
            'longitude' => ['required', 'numeric'],
        ]);

        Outlet::create([
            ...$validated,
            'is_active' => true,
        ]);

        return redirect()->route('admin.outlets.index');
    }
}
