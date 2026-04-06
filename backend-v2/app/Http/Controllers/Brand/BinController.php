<?php

namespace App\Http\Controllers\Brand;

use App\Http\Controllers\Controller;
use App\Models\Bin;
use App\Models\Outlet;
use Inertia\Inertia;
use Inertia\Response;

class BinController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        $brand = $user->organization?->brands()->first();

        $outletIds = $brand
            ? Outlet::where('brand_id', $brand->id)->pluck('id')
            : collect();

        $bins = Bin::whereIn('outlet_id', $outletIds)
            ->with([
                'outlet:id,name',
                'pickupRequests' => fn ($q) => $q->where('status', 'pending')->select('id', 'bin_id', 'status'),
            ])
            ->orderByDesc('fill_level')
            ->paginate(15)
            ->through(fn (Bin $bin) => [
                'id' => $bin->id,
                'serial_number' => $bin->serial_number,
                'outlet' => $bin->outlet?->name ?? '-',
                'status' => $bin->status,
                'fill_level' => $bin->fill_level,
                'has_pending_pickup' => $bin->pickupRequests->isNotEmpty(),
            ]);

        return Inertia::render('Brand/Bins/Index', [
            'bins' => $bins,
            'brandName' => $brand?->name,
        ]);
    }
}
