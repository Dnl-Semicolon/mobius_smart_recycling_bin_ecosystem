<?php

namespace App\Http\Controllers\Api;

use App\Enums\RouteStatus;
use App\Http\Controllers\Controller;
use App\Models\CollectionRoute;
use App\Models\PickupRequest;
use App\Services\RouteOptimizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollectorRouteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $routes = CollectionRoute::query()
            ->where('collector_id', $request->user()->id)
            ->with('routeStops')
            ->latest()
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'status' => $r->status->value ?? $r->status,
                'depot_name' => $r->depot_name,
                'total_distance_km' => (float) $r->total_distance_km,
                'total_duration_min' => (int) $r->total_duration_min,
                'stops_count' => $r->routeStops->count(),
                'started_at' => $r->started_at?->toISOString(),
                'completed_at' => $r->completed_at?->toISOString(),
                'created_at' => $r->created_at?->toISOString(),
            ]);

        return response()->json(['routes' => $routes]);
    }

    public function show(Request $request, CollectionRoute $collectionRoute): JsonResponse
    {
        if ($collectionRoute->collector_id !== $request->user()->id) {
            return response()->json(['error' => 'Not your route'], 403);
        }

        $collectionRoute->load('routeStops.bin.outlet.brand');

        return response()->json([
            'route' => [
                'id' => $collectionRoute->id,
                'status' => $collectionRoute->status->value ?? $collectionRoute->status,
                'depot_latitude' => (float) $collectionRoute->depot_latitude,
                'depot_longitude' => (float) $collectionRoute->depot_longitude,
                'depot_name' => $collectionRoute->depot_name,
                'total_distance_km' => (float) $collectionRoute->total_distance_km,
                'total_duration_min' => (int) $collectionRoute->total_duration_min,
                'route_polyline' => $collectionRoute->route_polyline,
                'started_at' => $collectionRoute->started_at?->toISOString(),
                'completed_at' => $collectionRoute->completed_at?->toISOString(),
                'stops' => $collectionRoute->routeStops->sortBy('stop_order')->values()->map(fn ($s) => [
                    'id' => $s->id,
                    'stop_order' => (int) $s->stop_order,
                    'bin_serial' => $s->bin?->serial_number,
                    'outlet' => $s->bin?->outlet?->name,
                    'brand' => $s->bin?->outlet?->brand?->name,
                    'address' => $s->address,
                    'distance_km' => (float) $s->distance_km,
                    'duration_min' => (int) $s->duration_min,
                    'status' => $s->status,
                    'eta' => $s->eta?->toISOString(),
                    'completed_at' => $s->completed_at?->toISOString(),
                    'latitude' => (float) $s->bin?->latitude,
                    'longitude' => (float) $s->bin?->longitude,
                ]),
            ],
        ]);
    }

    public function generate(Request $request, RouteOptimizationService $service): JsonResponse
    {
        // Get all pending pickup requests
        $pendingIds = PickupRequest::where('status', 'pending')->pluck('id')->toArray();

        if (empty($pendingIds)) {
            return response()->json(['error' => 'No pending pickup requests'], 404);
        }

        $route = $service->generate($request->user(), $pendingIds);

        if (! $route) {
            return response()->json(['error' => 'Could not generate route'], 500);
        }

        return response()->json([
            'message' => 'Route generated',
            'route_id' => $route->id,
            'stops_count' => $route->routeStops->count(),
            'total_distance_km' => $route->total_distance_km,
            'total_duration_min' => $route->total_duration_min,
        ], 201);
    }

    public function accept(Request $request, CollectionRoute $collectionRoute): JsonResponse
    {
        if ($collectionRoute->collector_id !== $request->user()->id) {
            return response()->json(['error' => 'Not your route'], 403);
        }

        if ($collectionRoute->status !== RouteStatus::Pending) {
            return response()->json(['error' => 'Route cannot be accepted'], 409);
        }

        $collectionRoute->update(['status' => RouteStatus::Accepted]);

        return response()->json(['message' => 'Route accepted', 'status' => 'accepted']);
    }

    public function start(Request $request, CollectionRoute $collectionRoute): JsonResponse
    {
        if ($collectionRoute->collector_id !== $request->user()->id) {
            return response()->json(['error' => 'Not your route'], 403);
        }

        if ($collectionRoute->status !== RouteStatus::Accepted) {
            return response()->json(['error' => 'Route must be accepted first'], 409);
        }

        $collectionRoute->update(['status' => RouteStatus::InProgress, 'started_at' => now()]);

        return response()->json(['message' => 'Route started', 'status' => 'in_progress']);
    }

    public function completeStop(Request $request, CollectionRoute $collectionRoute, int $order): JsonResponse
    {
        if ($collectionRoute->collector_id !== $request->user()->id) {
            return response()->json(['error' => 'Not your route'], 403);
        }

        $stop = $collectionRoute->routeStops()->where('stop_order', $order)->first();
        if (! $stop) {
            return response()->json(['error' => 'Stop not found'], 404);
        }

        $stop->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_latitude' => $request->input('latitude'),
            'completed_longitude' => $request->input('longitude'),
            'proof_image_path' => $request->input('proof_image_path'),
        ]);

        // Reset bin fill level
        if ($stop->bin) {
            $stop->bin->update([
                'fill_level' => 0,
                'weight_grams' => 0,
                'sensor_levels' => ['level_25' => false, 'level_50' => false, 'level_75' => false, 'level_100' => false],
                'last_pickup_at' => now(),
            ]);
        }

        // Complete the pickup request
        if ($stop->pickupRequest) {
            $stop->pickupRequest->update(['status' => 'completed', 'completed_at' => now()]);
        }

        return response()->json(['message' => "Stop {$order} completed"]);
    }

    public function skipStop(Request $request, CollectionRoute $collectionRoute, int $order): JsonResponse
    {
        if ($collectionRoute->collector_id !== $request->user()->id) {
            return response()->json(['error' => 'Not your route'], 403);
        }

        $stop = $collectionRoute->routeStops()->where('stop_order', $order)->first();
        if (! $stop) {
            return response()->json(['error' => 'Stop not found'], 404);
        }

        $stop->update([
            'status' => 'skipped',
            'skip_reason' => $request->input('reason', 'Skipped by collector'),
        ]);

        return response()->json(['message' => "Stop {$order} skipped"]);
    }

    public function complete(Request $request, CollectionRoute $collectionRoute): JsonResponse
    {
        if ($collectionRoute->collector_id !== $request->user()->id) {
            return response()->json(['error' => 'Not your route'], 403);
        }

        $collectionRoute->update(['status' => RouteStatus::Completed, 'completed_at' => now()]);

        return response()->json(['message' => 'Route completed', 'status' => 'completed']);
    }

    public function reject(Request $request, CollectionRoute $collectionRoute): JsonResponse
    {
        if ($collectionRoute->collector_id !== $request->user()->id) {
            return response()->json(['error' => 'Not your route'], 403);
        }

        $collectionRoute->update(['status' => RouteStatus::Rejected]);

        // Unassign pickup requests
        foreach ($collectionRoute->routeStops as $stop) {
            if ($stop->pickupRequest) {
                $stop->pickupRequest->update(['status' => 'pending', 'assigned_to' => null, 'assigned_at' => null]);
            }
        }

        return response()->json(['message' => 'Route rejected', 'status' => 'rejected']);
    }
}
