<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PickupRequestResource;
use App\Models\PickupRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PickupRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $pickupRequests = PickupRequest::query()
            ->with(['bin.currentAssignment.outlet', 'claimedBy'])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->latest()
            ->paginate();

        return PickupRequestResource::collection($pickupRequests)
            ->additional(['message' => 'Pickup requests retrieved successfully.'])
            ->response();
    }

    public function show(PickupRequest $pickupRequest): JsonResponse
    {
        $pickupRequest->load(['bin.currentAssignment.outlet', 'claimedBy']);

        return PickupRequestResource::make($pickupRequest)
            ->additional(['message' => 'Pickup request retrieved successfully.'])
            ->response();
    }

    public function cancel(PickupRequest $pickupRequest): JsonResponse
    {
        if (! $pickupRequest->cancel()) {
            return response()->json([
                'data' => null,
                'message' => 'This pickup cannot be cancelled.',
            ], 422);
        }

        $pickupRequest->load(['bin.currentAssignment.outlet', 'claimedBy']);

        return PickupRequestResource::make($pickupRequest)
            ->additional(['message' => 'Pickup request cancelled successfully.'])
            ->response();
    }

    public function unclaim(PickupRequest $pickupRequest): JsonResponse
    {
        if (! $pickupRequest->unclaim()) {
            return response()->json([
                'data' => null,
                'message' => 'This pickup cannot be returned to the queue.',
            ], 422);
        }

        $pickupRequest->load(['bin.currentAssignment.outlet']);

        return PickupRequestResource::make($pickupRequest)
            ->additional(['message' => 'Pickup request returned to pending queue.'])
            ->response();
    }
}
