<?php

namespace App\Services;

use App\Enums\PickupStatus;
use App\Enums\RouteStatus;
use App\Models\Bin;
use App\Models\CollectionRoute;
use App\Models\PickupRequest;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RouteOptimizationService
{
    /**
     * Generate an optimized collection route for a zone.
     *
     * @return CollectionRoute|null Returns null if not enough eligible bins or no collectors available.
     */
    public function generateRoute(Zone $zone): ?CollectionRoute
    {
        $bins = $zone->eligibleBins();

        if ($bins->count() < $zone->min_bins_for_dispatch) {
            return null;
        }

        $collector = $this->findAvailableCollector($zone);
        if (! $collector) {
            Log::warning('No available collector for zone.', ['zone' => $zone->slug]);

            return null;
        }

        $vroomRequest = $this->buildVroomRequest($zone, $bins, $collector);
        $vroomResponse = $this->callVroom($vroomRequest);

        if (! $vroomResponse) {
            // Fallback: use nearest-neighbor ordering if VROOM is unavailable
            return $this->generateFallbackRoute($zone, $bins, $collector, $vroomRequest);
        }

        return $this->createRouteFromVroomResponse(
            $zone,
            $collector,
            $bins,
            $vroomRequest,
            $vroomResponse
        );
    }

    /**
     * Build a VROOM-compatible request payload.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, Bin>  $bins
     * @return array<string, mixed>
     */
    public function buildVroomRequest(Zone $zone, $bins, User $collector): array
    {
        $vehicles = [
            [
                'id' => $collector->id,
                'profile' => 'car',
                'start' => [(float) $zone->depot_longitude, (float) $zone->depot_latitude],
                'end' => [(float) $zone->depot_longitude, (float) $zone->depot_latitude],
                'capacity' => [20],
                'description' => $collector->name,
            ],
        ];

        $jobs = [];
        foreach ($bins as $bin) {
            $outlet = $bin->currentAssignment?->outlet;
            if (! $outlet) {
                continue;
            }

            $priority = $this->calculatePriority($bin->fill_level);

            $jobs[] = [
                'id' => $bin->id,
                'location' => [(float) $outlet->longitude, (float) $outlet->latitude],
                'service' => 300, // 5 minutes per stop
                'priority' => $priority,
                'delivery' => [1],
                'description' => sprintf(
                    '%s - %s (%d%%)',
                    $outlet->name,
                    $bin->serial_number,
                    $bin->fill_level
                ),
            ];
        }

        return [
            'vehicles' => $vehicles,
            'jobs' => $jobs,
        ];
    }

    /**
     * Call the VROOM HTTP API.
     *
     * @param  array<string, mixed>  $request
     * @return array<string, mixed>|null
     */
    public function callVroom(array $request): ?array
    {
        $baseUrl = config('services.vroom.url', 'http://localhost:3000');

        try {
            $response = Http::timeout(30)
                ->post($baseUrl, $request);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('VROOM API error.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::warning('VROOM API unreachable.', [
                'url' => $baseUrl,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Parse VROOM response and create a CollectionRoute.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, Bin>  $bins
     * @param  array<string, mixed>  $vroomRequest
     * @param  array<string, mixed>  $vroomResponse
     */
    public function createRouteFromVroomResponse(
        Zone $zone,
        User $collector,
        $bins,
        array $vroomRequest,
        array $vroomResponse
    ): CollectionRoute {
        $binsById = $bins->keyBy('id');
        $route = $vroomResponse['routes'][0] ?? [];
        $steps = $route['steps'] ?? [];
        $stops = [];
        $order = 1;
        $cumulativeDistance = 0;

        foreach ($steps as $step) {
            if ($step['type'] !== 'job') {
                continue;
            }

            $binId = $step['id'];
            $bin = $binsById[$binId] ?? null;
            if (! $bin) {
                continue;
            }

            $outlet = $bin->currentAssignment?->outlet;
            $cumulativeDistance += ($step['distance'] ?? 0);

            // Create a PickupRequest for this bin
            $pickup = PickupRequest::create([
                'bin_id' => $bin->id,
                'status' => PickupStatus::Pending,
            ]);

            $stops[] = [
                'order' => $order,
                'bin_id' => $bin->id,
                'pickup_request_id' => $pickup->id,
                'outlet_name' => $outlet?->name ?? 'Unknown',
                'address' => $outlet?->address ?? '',
                'latitude' => (float) ($outlet?->latitude ?? 0),
                'longitude' => (float) ($outlet?->longitude ?? 0),
                'fill_level' => $bin->fill_level,
                'service_time_sec' => 300,
                'eta_minutes' => (int) round(($step['arrival'] ?? 0) / 60),
                'arrival_distance_km' => round($cumulativeDistance / 1000, 2),
                'status' => 'pending',
                'completed_at' => null,
            ];

            $order++;
        }

        $totalDistanceKm = round(($route['distance'] ?? 0) / 1000, 2);
        $totalDurationMin = (int) round(($route['duration'] ?? 0) / 60);

        $routeGeometry = $route['geometry'] ?? null;

        return CollectionRoute::create([
            'zone_id' => $zone->id,
            'collector_id' => $collector->id,
            'status' => RouteStatus::Pending,
            'stops' => $stops,
            'total_distance_km' => $totalDistanceKm,
            'total_duration_min' => $totalDurationMin,
            'vroom_request' => $vroomRequest,
            'vroom_response' => $vroomResponse,
            'route_geometry' => $routeGeometry,
        ]);
    }

    /**
     * Fallback route when VROOM is unavailable — uses naive nearest-neighbor ordering.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, Bin>  $bins
     * @param  array<string, mixed>  $vroomRequest
     */
    public function generateFallbackRoute(
        Zone $zone,
        $bins,
        User $collector,
        array $vroomRequest
    ): CollectionRoute {
        $stops = [];
        $remaining = $bins->values()->all();
        $currentLat = (float) $zone->depot_latitude;
        $currentLng = (float) $zone->depot_longitude;
        $order = 1;

        while (! empty($remaining)) {
            $nearestIdx = 0;
            $nearestDist = PHP_FLOAT_MAX;

            foreach ($remaining as $idx => $bin) {
                $outlet = $bin->currentAssignment?->outlet;
                if (! $outlet) {
                    continue;
                }

                $dist = $this->haversineDistance(
                    $currentLat,
                    $currentLng,
                    (float) $outlet->latitude,
                    (float) $outlet->longitude
                );

                if ($dist < $nearestDist) {
                    $nearestDist = $dist;
                    $nearestIdx = $idx;
                }
            }

            $bin = $remaining[$nearestIdx];
            $outlet = $bin->currentAssignment?->outlet;
            array_splice($remaining, $nearestIdx, 1);

            $pickup = PickupRequest::create([
                'bin_id' => $bin->id,
                'status' => PickupStatus::Pending,
            ]);

            $stops[] = [
                'order' => $order,
                'bin_id' => $bin->id,
                'pickup_request_id' => $pickup->id,
                'outlet_name' => $outlet?->name ?? 'Unknown',
                'address' => $outlet?->address ?? '',
                'latitude' => (float) ($outlet?->latitude ?? 0),
                'longitude' => (float) ($outlet?->longitude ?? 0),
                'fill_level' => $bin->fill_level,
                'service_time_sec' => 300,
                'eta_minutes' => null,
                'arrival_distance_km' => round($nearestDist, 2),
                'status' => 'pending',
                'completed_at' => null,
            ];

            $currentLat = (float) ($outlet?->latitude ?? $currentLat);
            $currentLng = (float) ($outlet?->longitude ?? $currentLng);
            $order++;
        }

        return CollectionRoute::create([
            'zone_id' => $zone->id,
            'collector_id' => $collector->id,
            'status' => RouteStatus::Pending,
            'stops' => $stops,
            'total_distance_km' => null,
            'total_duration_min' => null,
            'vroom_request' => $vroomRequest,
            'vroom_response' => ['fallback' => true, 'reason' => 'VROOM unavailable'],
        ]);
    }

    /**
     * Calculate priority from fill level (0-100 scale → 0-10 VROOM priority).
     */
    public function calculatePriority(int $fillLevel): int
    {
        return match (true) {
            $fillLevel >= 95 => 10,
            $fillLevel >= 90 => 7,
            $fillLevel >= 85 => 5,
            $fillLevel >= 80 => 3,
            default => 1,
        };
    }

    /**
     * Find the best available collector for a zone.
     */
    private function findAvailableCollector(Zone $zone): ?User
    {
        // First try the primary collector
        $primary = $zone->primaryCollector();
        if ($primary && ! $this->collectorHasActiveRoute($primary)) {
            return $primary;
        }

        // Then try other zone collectors
        foreach ($zone->collectors as $collector) {
            if (! $this->collectorHasActiveRoute($collector)) {
                return $collector;
            }
        }

        return null;
    }

    /**
     * Check if a collector already has an active (non-completed) route.
     */
    private function collectorHasActiveRoute(User $collector): bool
    {
        return CollectionRoute::query()
            ->where('collector_id', $collector->id)
            ->active()
            ->exists();
    }

    /**
     * Haversine distance in kilometers between two lat/lng points.
     */
    private function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
