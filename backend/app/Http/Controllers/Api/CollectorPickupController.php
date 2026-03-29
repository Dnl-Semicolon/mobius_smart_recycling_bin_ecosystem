<?php

namespace App\Http\Controllers\Api;

use App\Enums\PickupStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PickupRequestResource;
use App\Models\PickupRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollectorPickupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $collectorId = (int) $request->user()->id;
        $relations = ['bin.currentAssignment.outlet'];

        $availablePickups = PickupRequest::query()
            ->pending()
            ->with($relations)
            ->latest()
            ->get();

        $myActivePickups = PickupRequest::query()
            ->claimed()
            ->where('claimed_by', $collectorId)
            ->with($relations)
            ->latest('claimed_at')
            ->get();

        $myHistory = PickupRequest::query()
            ->where('status', PickupStatus::Completed)
            ->where('claimed_by', $collectorId)
            ->with($relations)
            ->latest('completed_at')
            ->limit(20)
            ->get();

        return response()->json([
            'data' => [
                'available' => PickupRequestResource::collection($availablePickups)->resolve($request),
                'active' => PickupRequestResource::collection($myActivePickups)->resolve($request),
                'history' => PickupRequestResource::collection($myHistory)->resolve($request),
            ],
            'message' => 'Collector pickups retrieved successfully.',
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $collectorId = (int) $request->user()->id;

        $totalCompleted = PickupRequest::query()
            ->where('status', PickupStatus::Completed)
            ->where('claimed_by', $collectorId)
            ->count();

        $thisWeek = PickupRequest::query()
            ->where('status', PickupStatus::Completed)
            ->where('claimed_by', $collectorId)
            ->where('completed_at', '>=', now()->startOfWeek())
            ->count();

        $activeNow = PickupRequest::query()
            ->claimed()
            ->where('claimed_by', $collectorId)
            ->count();

        return response()->json([
            'data' => [
                'total_completed' => $totalCompleted,
                'this_week' => $thisWeek,
                'active_now' => $activeNow,
                'avg_minutes' => PickupRequest::avgResponseMinutes($collectorId),
            ],
            'message' => 'Collector stats retrieved successfully.',
        ]);
    }

    public function claim(Request $request, PickupRequest $pickupRequest): JsonResponse
    {
        if (! $pickupRequest->claimByCollector((int) $request->user()->id)) {
            return response()->json([
                'data' => null,
                'message' => 'Pickup is no longer available for claim.',
            ], 409);
        }

        $pickupRequest->load('bin.currentAssignment.outlet');

        return PickupRequestResource::make($pickupRequest)
            ->additional(['message' => 'Pickup claimed successfully.'])
            ->response();
    }

    public function complete(Request $request, PickupRequest $pickupRequest): JsonResponse
    {
        $result = $pickupRequest->completeByCollector((int) $request->user()->id);

        if ($result === PickupRequest::COMPLETE_RESULT_FORBIDDEN) {
            return response()->json([
                'data' => null,
                'message' => 'You can only complete pickups you have claimed.',
            ], 403);
        }

        if ($result === PickupRequest::COMPLETE_RESULT_INVALID_STATE) {
            return response()->json([
                'data' => null,
                'message' => 'Pickup cannot be completed in its current state.',
            ], 409);
        }

        $pickupRequest->load('bin.currentAssignment.outlet');

        return PickupRequestResource::make($pickupRequest)
            ->additional(['message' => 'Pickup completed successfully.'])
            ->response();
    }
}
