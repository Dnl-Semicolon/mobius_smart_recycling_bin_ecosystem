<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollectionRoute;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CollectionRouteManagementController extends Controller
{
    public function index(Request $request): View
    {
        $routes = CollectionRoute::query()
            ->with(['collector', 'routeStops.bin'])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.collection-routes.index', compact('routes'));
    }

    public function show(CollectionRoute $collectionRoute): View
    {
        $collectionRoute->load(['collector', 'routeStops.bin.outlet', 'routeStops.pickupRequest']);

        return view('admin.collection-routes.show', compact('collectionRoute'));
    }
}
