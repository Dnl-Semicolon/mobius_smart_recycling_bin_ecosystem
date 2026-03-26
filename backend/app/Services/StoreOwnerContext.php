<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Support\Collection;

class StoreOwnerContextData
{
    /**
     * @param  Collection<int, Outlet>  $outlets
     * @param  array<int>  $binIds
     */
    public function __construct(
        public readonly Brand $brand,
        public readonly Collection $outlets,
        public readonly bool $isHQ,
        public readonly array $binIds,
        public readonly ?Outlet $selectedOutlet = null,
    ) {}
}

class StoreOwnerContext
{
    public function resolve(User $user, ?int $outletId = null): StoreOwnerContextData
    {
        // Check if user is brand HQ (brand registrant)
        $brand = Brand::where('user_id', $user->id)->first();
        $isHQ = $brand !== null;

        if (! $brand) {
            // Find brand through outlet assignments
            $assignedOutlet = $user->outlets()->with('brand')->first();
            $brand = $assignedOutlet?->brand;
        }

        abort_unless($brand, 403, 'No brand associated with your account.');

        // Scope outlets: HQ sees all, branch sees assigned only
        $outlets = $isHQ
            ? $brand->outlets()->with('bins')->get()
            : $user->outlets()->where('brand_id', $brand->id)->with('bins')->get();

        // Handle outlet filter
        $selectedOutlet = null;
        if ($outletId !== null) {
            $selectedOutlet = $outlets->firstWhere('id', $outletId);
            abort_unless($selectedOutlet, 403, 'Outlet not in your scope.');
            $outlets = collect([$selectedOutlet]);
        }

        // Collect bin IDs across all scoped outlets
        $binIds = $outlets->flatMap(fn (Outlet $outlet) => $outlet->bins->pluck('id'))->all();

        return new StoreOwnerContextData(
            brand: $brand,
            outlets: $outlets,
            isHQ: $isHQ,
            binIds: $binIds,
            selectedOutlet: $selectedOutlet,
        );
    }
}
