<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bin;
use App\Models\CollectionRoute;
use App\Models\PickupRequest;
use App\Models\RouteStop;
use App\Models\User;
use App\Services\RouteOptimizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RouteController extends Controller
{
    public function index(): Response
    {
        $routes = CollectionRoute::query()
            ->with('collector:id,name')
            ->withCount('stops')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->through(fn (CollectionRoute $r) => [
                'id' => $r->id,
                'collector' => $r->collector?->name ?? 'Unassigned',
                'status' => $r->status,
                'stops_count' => $r->stops_count,
                'total_distance_km' => $r->total_distance_km,
                'total_duration_min' => $r->total_duration_min,
                'created_at' => $r->created_at->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Admin/Routes/Index', [
            'routes' => $routes,
        ]);
    }

    public function generate(): Response
    {
        // Get all pending pickup requests with bin locations
        $pendingPickups = PickupRequest::where('status', 'pending')
            ->with('bin:id,serial_number,latitude,longitude,outlet_id', 'bin.outlet:id,name')
            ->get()
            ->map(fn (PickupRequest $p) => [
                'id' => $p->id,
                'bin_id' => $p->bin_id,
                'serial' => $p->bin->serial_number,
                'outlet' => $p->bin->outlet?->name ?? 'Unknown',
                'lat' => $p->bin->latitude,
                'lng' => $p->bin->longitude,
                'reason' => $p->reason,
            ]);

        $collectors = User::whereJsonContains('roles', 'collector')->get(['id', 'name']);

        return Inertia::render('Admin/Routes/Generate', [
            'pendingPickups' => $pendingPickups,
            'collectors' => $collectors,
        ]);
    }

    public function store(Request $request, RouteOptimizationService $routeService): RedirectResponse
    {
        $validated = $request->validate([
            'collector_id' => ['required', 'exists:users,id'],
            'pickup_ids' => ['required', 'array', 'min:1'],
            'pickup_ids.*' => ['exists:pickup_requests,id'],
            'origin_lat' => ['required', 'numeric'],
            'origin_lng' => ['required', 'numeric'],
        ]);

        $pickups = PickupRequest::whereIn('id', $validated['pickup_ids'])
            ->with('bin')
            ->get();

        $waypoints = $pickups->map(fn (PickupRequest $p) => [
            'lat' => (float) $p->bin->latitude,
            'lng' => (float) $p->bin->longitude,
            'label' => $p->bin->serial_number,
            'pickup_id' => $p->id,
            'bin_id' => $p->bin_id,
        ])->values()->toArray();

        $origin = [
            'lat' => (float) $validated['origin_lat'],
            'lng' => (float) $validated['origin_lng'],
        ];

        $result = $routeService->optimize($origin, $waypoints);

        if (! $result) {
            return back()->withErrors(['route' => 'Failed to generate route. Check Google Directions API.']);
        }

        DB::transaction(function () use ($validated, $waypoints, $result, $pickups) {
            $route = CollectionRoute::create([
                'collector_id' => $validated['collector_id'],
                'status' => 'pending',
                'depot_latitude' => $validated['origin_lat'],
                'depot_longitude' => $validated['origin_lng'],
                'depot_name' => 'Start Location',
                'total_distance_km' => $result['distance_km'],
                'total_duration_min' => $result['duration_min'],
                'route_polyline' => $result['polyline'],
                'google_response' => $result['google_response'],
            ]);

            // Create stops in optimized order
            $orderedIndices = $result['ordered_indices'];
            foreach ($orderedIndices as $order => $originalIndex) {
                $wp = $waypoints[$originalIndex];
                $leg = $result['legs'][$order] ?? [];

                RouteStop::create([
                    'collection_route_id' => $route->id,
                    'bin_id' => $wp['bin_id'],
                    'pickup_request_id' => $wp['pickup_id'],
                    'stop_order' => $order + 1,
                    'address' => $leg['end_address'] ?? '',
                    'distance_km' => $leg['distance_km'] ?? 0,
                    'duration_min' => $leg['duration_min'] ?? 0,
                    'status' => 'pending',
                ]);
            }

            // Mark pickup requests as assigned
            foreach ($pickups as $pickup) {
                $pickup->update([
                    'status' => 'assigned',
                    'assigned_to' => $validated['collector_id'],
                    'assigned_at' => now(),
                ]);
            }
        });

        return redirect()->route('admin.routes.index');
    }
}
