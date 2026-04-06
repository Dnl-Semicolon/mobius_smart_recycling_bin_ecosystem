<?php

namespace App\Http\Controllers\Collector;

use App\Http\Controllers\Controller;
use App\Models\CollectionRoute;
use App\Models\RouteStop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RouteController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        $routes = CollectionRoute::where('collector_id', $user->id)
            ->withCount('stops')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (CollectionRoute $r) => [
                'id' => $r->id,
                'status' => $r->status,
                'stops_count' => $r->stops_count,
                'total_distance_km' => $r->total_distance_km,
                'total_duration_min' => $r->total_duration_min,
                'created_at' => $r->created_at->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Collector/Routes/Index', [
            'routes' => $routes,
        ]);
    }

    public function show(CollectionRoute $route): Response
    {
        $user = auth()->user();

        if ($route->collector_id !== $user->id) {
            abort(403);
        }

        $route->load('stops.bin');

        return Inertia::render('Collector/Routes/Show', [
            'route' => [
                'id' => $route->id,
                'status' => $route->status,
                'polyline' => $route->route_polyline,
                'total_distance_km' => $route->total_distance_km,
                'total_duration_min' => $route->total_duration_min,
                'depot' => [
                    'lat' => (float) $route->depot_latitude,
                    'lng' => (float) $route->depot_longitude,
                ],
                'stops' => $route->stops->map(fn (RouteStop $s) => [
                    'id' => $s->id,
                    'order' => $s->stop_order,
                    'bin_serial' => $s->bin->serial_number,
                    'address' => $s->address,
                    'lat' => (float) $s->bin->latitude,
                    'lng' => (float) $s->bin->longitude,
                    'distance_km' => $s->distance_km,
                    'duration_min' => $s->duration_min,
                    'status' => $s->status,
                ]),
            ],
            'googleMapsKey' => config('services.google.maps_key'),
        ]);
    }

    public function accept(CollectionRoute $route): RedirectResponse
    {
        $route->update(['status' => 'accepted']);

        return back();
    }

    public function start(CollectionRoute $route): RedirectResponse
    {
        $route->update(['status' => 'in_progress', 'started_at' => now()]);

        return back();
    }

    public function completeStop(Request $request, CollectionRoute $route, int $stopOrder): RedirectResponse
    {
        $stop = $route->stops()->where('stop_order', $stopOrder)->firstOrFail();

        $stop->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_latitude' => $request->input('lat'),
            'completed_longitude' => $request->input('lng'),
        ]);

        // Check if all stops are complete
        $allComplete = $route->stops()->where('status', '!=', 'completed')->doesntExist();
        if ($allComplete) {
            $route->update(['status' => 'completed', 'completed_at' => now()]);
        }

        return back();
    }
}
