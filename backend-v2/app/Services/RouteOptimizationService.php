<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RouteOptimizationService
{
    /**
     * Generate an optimized route using Google Directions API.
     *
     * @param  array{lat: float, lng: float}  $origin  Collector's start location
     * @param  array<int, array{lat: float, lng: float, label: string}>  $waypoints  Bin locations to visit
     * @return array{polyline: string, distance_km: float, duration_min: int, ordered_indices: array<int>, legs: array}|null
     */
    public function optimize(array $origin, array $waypoints): ?array
    {
        if (empty($waypoints)) {
            return null;
        }

        $apiKey = config('services.google.directions_key');
        if (! $apiKey) {
            return null;
        }

        // Build waypoints string with optimize:true for Google to reorder
        $waypointStrings = array_map(
            fn (array $wp) => $wp['lat'].','.$wp['lng'],
            $waypoints
        );

        $response = Http::get('https://maps.googleapis.com/maps/api/directions/json', [
            'origin' => $origin['lat'].','.$origin['lng'],
            'destination' => $origin['lat'].','.$origin['lng'], // Return to origin
            'waypoints' => 'optimize:true|'.implode('|', $waypointStrings),
            'key' => $apiKey,
            'mode' => 'driving',
            'language' => 'en',
            'region' => 'my',
        ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        if (($data['status'] ?? '') !== 'OK' || empty($data['routes'])) {
            return null;
        }

        $route = $data['routes'][0];
        $legs = $route['legs'];

        $totalDistance = collect($legs)->sum(fn ($leg) => $leg['distance']['value']) / 1000;
        $totalDuration = (int) ceil(collect($legs)->sum(fn ($leg) => $leg['duration']['value']) / 60);

        return [
            'polyline' => $route['overview_polyline']['points'] ?? '',
            'distance_km' => round($totalDistance, 2),
            'duration_min' => $totalDuration,
            'ordered_indices' => $route['waypoint_order'] ?? [],
            'legs' => collect($legs)->map(fn ($leg, $i) => [
                'distance_km' => round($leg['distance']['value'] / 1000, 2),
                'duration_min' => (int) ceil($leg['duration']['value'] / 60),
                'start_address' => $leg['start_address'] ?? '',
                'end_address' => $leg['end_address'] ?? '',
            ])->toArray(),
            'google_response' => $data,
        ];
    }
}
