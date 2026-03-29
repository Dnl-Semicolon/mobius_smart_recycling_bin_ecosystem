<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CollectionRouteResource;
use App\Models\CollectionRoute;
use App\Services\RouteOptimizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CollectorRouteController extends Controller
{
    /**
     * List routes for the authenticated collector.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = CollectionRoute::query()
            ->where('collector_id', $request->user()->id)
            ->with('zone')
            ->latest();

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        return CollectionRouteResource::collection($query->get());
    }

    /**
     * Get a specific route with full details.
     */
    public function show(Request $request, CollectionRoute $collectionRoute): CollectionRouteResource|JsonResponse
    {
        if ($collectionRoute->collector_id !== $request->user()->id) {
            return response()->json(['message' => 'Not your route.'], 403);
        }

        $collectionRoute->load('zone');

        return new CollectionRouteResource($collectionRoute);
    }

    /**
     * Accept an assigned route.
     */
    public function accept(Request $request, CollectionRoute $collectionRoute): JsonResponse
    {
        if ($collectionRoute->collector_id !== $request->user()->id) {
            return response()->json(['message' => 'Not your route.'], 403);
        }

        if (! $collectionRoute->accept()) {
            return response()->json(['message' => 'Route cannot be accepted in its current state.'], 409);
        }

        return response()->json(['message' => 'Route accepted.', 'data' => new CollectionRouteResource($collectionRoute)]);
    }

    /**
     * Start navigating a route.
     */
    public function start(Request $request, CollectionRoute $collectionRoute): JsonResponse
    {
        if ($collectionRoute->collector_id !== $request->user()->id) {
            return response()->json(['message' => 'Not your route.'], 403);
        }

        if (! $collectionRoute->start()) {
            return response()->json(['message' => 'Route must be accepted before starting.'], 409);
        }

        return response()->json(['message' => 'Route started.', 'data' => new CollectionRouteResource($collectionRoute)]);
    }

    /**
     * Mark a stop as completed.
     *
     * Requires the collector's GPS coordinates for proximity validation.
     * The collector must be within the configured threshold (default 200m) of the stop.
     */
    public function completeStop(Request $request, CollectionRoute $collectionRoute, int $order): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        if ($collectionRoute->collector_id !== $request->user()->id) {
            return response()->json(['message' => 'Not your route.'], 403);
        }

        if (! $collectionRoute->isActive()) {
            return response()->json(['message' => 'Route is not active.'], 409);
        }

        $result = $collectionRoute->completeStop(
            $order,
            (float) $request->input('latitude'),
            (float) $request->input('longitude'),
        );

        if (! $result['success']) {
            $response = ['message' => $result['message']];
            if (isset($result['distance_meters'])) {
                $response['distance_meters'] = $result['distance_meters'];
            }

            return response()->json($response, 422);
        }

        $collectionRoute->refresh();

        // Auto-complete route if all stops are done
        if ($collectionRoute->allStopsDone()) {
            $collectionRoute->complete();
        }

        return response()->json([
            'message' => $result['message'],
            'data' => new CollectionRouteResource($collectionRoute),
        ]);
    }

    /**
     * Skip a stop with a reason.
     */
    public function skipStop(Request $request, CollectionRoute $collectionRoute, int $order): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        if ($collectionRoute->collector_id !== $request->user()->id) {
            return response()->json(['message' => 'Not your route.'], 403);
        }

        if (! $collectionRoute->isActive()) {
            return response()->json(['message' => 'Route is not active.'], 409);
        }

        $result = $collectionRoute->skipStop($order, $request->input('reason'));

        if (! $result['success']) {
            return response()->json(['message' => $result['message']], 422);
        }

        $collectionRoute->refresh();

        if ($collectionRoute->allStopsDone()) {
            $collectionRoute->complete();
        }

        return response()->json([
            'message' => $result['message'],
            'data' => new CollectionRouteResource($collectionRoute),
        ]);
    }

    /**
     * Complete the entire route.
     */
    public function complete(Request $request, CollectionRoute $collectionRoute): JsonResponse
    {
        if ($collectionRoute->collector_id !== $request->user()->id) {
            return response()->json(['message' => 'Not your route.'], 403);
        }

        if (! $collectionRoute->complete()) {
            return response()->json(['message' => 'Route cannot be completed in its current state.'], 409);
        }

        return response()->json(['message' => 'Route completed.', 'data' => new CollectionRouteResource($collectionRoute)]);
    }

    /**
     * Reject an assigned route.
     */
    public function reject(Request $request, CollectionRoute $collectionRoute): JsonResponse
    {
        if ($collectionRoute->collector_id !== $request->user()->id) {
            return response()->json(['message' => 'Not your route.'], 403);
        }

        if (! $collectionRoute->reject()) {
            return response()->json(['message' => 'Route cannot be rejected in its current state.'], 409);
        }

        return response()->json(['message' => 'Route rejected.']);
    }

    /**
     * Manually trigger route generation for the collector's zones.
     */
    public function generate(Request $request, RouteOptimizationService $service): JsonResponse
    {
        $collector = $request->user();
        $zones = $collector->zones()->where('is_active', true)->get();

        $routes = [];
        foreach ($zones as $zone) {
            $route = $service->generateRoute($zone);
            if ($route) {
                $routes[] = new CollectionRouteResource($route);
            }
        }

        if (empty($routes)) {
            return response()->json(['message' => 'No routes generated. Not enough bins need collection.']);
        }

        return response()->json([
            'message' => count($routes).' route(s) generated.',
            'data' => $routes,
        ]);
    }
}
