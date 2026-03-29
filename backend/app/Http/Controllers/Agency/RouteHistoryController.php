<?php

namespace App\Http\Controllers\Agency;

use App\Http\Controllers\Controller;
use App\Models\CollectionRoute;
use App\Models\CollectorAgency;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RouteHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $agency = CollectorAgency::where('user_id', auth()->id())->firstOrFail();
        $collectorIds = $agency->collectors()->pluck('users.id')->toArray();

        $routes = CollectionRoute::query()
            ->when($collectorIds, fn ($q) => $q->whereIn('collector_id', $collectorIds), fn ($q) => $q->whereRaw('1=0'))
            ->when($request->filled('collector'), fn ($q) => $q->where('collector_id', $request->collector))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->with('collector')
            ->latest()
            ->paginate(20);

        $collectors = $agency->collectors()->get();

        return view('agency.routes.index', compact('agency', 'routes', 'collectors'));
    }

    public function show(CollectionRoute $collectionRoute): View
    {
        $agency = CollectorAgency::where('user_id', auth()->id())->firstOrFail();
        $collectorIds = $agency->collectors()->pluck('users.id')->toArray();

        abort_unless(in_array($collectionRoute->collector_id, $collectorIds), 404);

        $collectionRoute->load('collector');

        return view('agency.routes.show', ['agency' => $agency, 'route' => $collectionRoute]);
    }
}
