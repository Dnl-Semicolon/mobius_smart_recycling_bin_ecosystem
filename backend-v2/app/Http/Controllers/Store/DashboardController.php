<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        $outlets = Outlet::where('user_id', $user->id)
            ->with('brand:id,name')
            ->withCount('bins')
            ->get()
            ->map(fn (Outlet $o) => [
                'id' => $o->id,
                'name' => $o->name,
                'brand' => $o->brand->name,
                'address' => $o->address,
                'bin_count' => $o->bins_count,
                'is_active' => $o->is_active,
            ]);

        $totalBins = $outlets->sum('bin_count');

        return Inertia::render('Store/Dashboard', [
            'outlets' => $outlets,
            'totalBins' => $totalBins,
        ]);
    }
}
