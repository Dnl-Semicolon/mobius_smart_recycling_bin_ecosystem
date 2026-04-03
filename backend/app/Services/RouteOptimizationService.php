<?php

namespace App\Services;

use App\Enums\RouteStatus;
use App\Models\CollectionRoute;
use App\Models\PickupRequest;
use App\Models\RouteStop;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RouteOptimizationService
{
    public function generate(User $collector, array $pickupRequestIds): ?CollectionRoute
    {
        $pickupRequests = PickupRequest::with('bin.outlet')
            ->whereIn('id', $pickupRequestIds)
            ->where('status', 'pending')
            ->get();

        if ($pickupRequests->isEmpty()) {
            return null;
        }

        // Build waypoints from bin locations
        $waypoints = [];
        foreach ($pickupRequests as $pr) {
            if ($pr->bin && $pr->bin->latitude && $pr->bin->longitude) {
                $waypoints[] = [
                    'pickup_request' => $pr,
                    'bin' => $pr->bin,
                    'lat' => $pr->bin->latitude,
                    'lng' => $pr->bin->longitude,
                ];
            }
        }

        if (empty($waypoints)) {
            return null;
        }

        // Use first bin's outlet as depot (start/end point)
        $firstOutlet = $waypoints[0]['bin']->outlet;
        $depotLat = $firstOutlet?->latitude ?? $waypoints[0]['lat'];
        $depotLng = $firstOutlet?->longitude ?? $waypoints[0]['lng'];
        $depotName = $firstOutlet?->name ?? 'Depot';

        // Call Google Directions API with optimizeWaypoints
        $waypointStr = 'optimize:true|'.implode('|', array_map(
            fn ($wp) => "{$wp['lat']},{$wp['lng']}",
            $waypoints
        ));

        $response = Http::get('https://maps.googleapis.com/maps/api/directions/json', [
            'origin' => "{$depotLat},{$depotLng}",
            'destination' => "{$depotLat},{$depotLng}",
            'waypoints' => $waypointStr,
            'key' => config('services.google.directions_api_key'),
        ]);

        if (! $response->successful() || $response->json('status') !== 'OK') {
            Log::error('Google Directions API failed', ['response' => $response->body()]);

            return null;
        }

        $route = $response->json('routes.0');
        $waypointOrder = $route['waypoint_order'] ?? [];
        $legs = $route['legs'] ?? [];
        $polyline = $route['overview_polyline']['points'] ?? '';

        // Calculate totals
        $totalDistanceM = collect($legs)->sum('distance.value');
        $totalDurationS = collect($legs)->sum('duration.value');

        // Create the CollectionRoute
        $collectionRoute = CollectionRoute::create([
            'collector_id' => $collector->id,
            'status' => RouteStatus::Pending,
            'depot_latitude' => $depotLat,
            'depot_longitude' => $depotLng,
            'depot_name' => $depotName,
            'total_distance_km' => round($totalDistanceM / 1000, 2),
            'total_duration_min' => (int) ceil($totalDurationS / 60),
            'route_polyline' => $polyline,
            'google_response' => $route,
        ]);

        // Create RouteStops in optimized order
        foreach ($waypointOrder as $stopIndex => $originalIndex) {
            $wp = $waypoints[$originalIndex];
            $leg = $legs[$stopIndex] ?? null;

            RouteStop::create([
                'collection_route_id' => $collectionRoute->id,
                'bin_id' => $wp['bin']->id,
                'pickup_request_id' => $wp['pickup_request']->id,
                'stop_order' => $stopIndex + 1,
                'address' => $leg['end_address'] ?? $wp['bin']->outlet?->address ?? '',
                'distance_km' => round(($leg['distance']['value'] ?? 0) / 1000, 2),
                'duration_min' => (int) ceil(($leg['duration']['value'] ?? 0) / 60),
                'status' => 'pending',
            ]);

            // Update pickup request
            $wp['pickup_request']->update([
                'status' => 'assigned',
                'assigned_to' => $collector->id,
                'assigned_at' => now(),
            ]);
        }

        return $collectionRoute->load('routeStops.bin.outlet.brand');
    }
}
