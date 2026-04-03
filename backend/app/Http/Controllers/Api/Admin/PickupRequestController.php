<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PickupRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PickupRequestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $pickupRequests = PickupRequest::query()
            ->with(['bin.outlet', 'requestedBy', 'assignedTo'])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->when($request->filled('request_type'), function ($query) use ($request) {
                $query->where('request_type', $request->input('request_type'));
            })
            ->latest()
            ->paginate();

        return response()->json([
            'data' => $pickupRequests->through(fn (PickupRequest $pr) => $this->formatPickupRequest($pr)),
            'message' => 'Pickup requests retrieved successfully.',
        ]);
    }

    public function show(PickupRequest $pickupRequest): JsonResponse
    {
        $pickupRequest->load(['bin.outlet', 'requestedBy', 'assignedTo']);

        return response()->json([
            'data' => $this->formatPickupRequest($pickupRequest),
            'message' => 'Pickup request retrieved successfully.',
        ]);
    }

    public function assign(Request $request, PickupRequest $pickupRequest): JsonResponse
    {
        $validated = $request->validate([
            'assigned_to' => ['required', 'integer', 'exists:users,id'],
        ]);

        $pickupRequest->update([
            'status' => 'assigned',
            'assigned_to' => $validated['assigned_to'],
            'assigned_at' => now(),
        ]);

        $pickupRequest->load(['bin.outlet', 'requestedBy', 'assignedTo']);

        return response()->json([
            'data' => $this->formatPickupRequest($pickupRequest),
            'message' => 'Pickup request assigned successfully.',
        ]);
    }

    public function complete(PickupRequest $pickupRequest): JsonResponse
    {
        $pickupRequest->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $pickupRequest->load(['bin.outlet', 'requestedBy', 'assignedTo']);

        return response()->json([
            'data' => $this->formatPickupRequest($pickupRequest),
            'message' => 'Pickup request completed.',
        ]);
    }

    public function cancel(PickupRequest $pickupRequest): JsonResponse
    {
        if ($pickupRequest->status === 'completed') {
            return response()->json([
                'data' => null,
                'message' => 'Completed pickup requests cannot be cancelled.',
            ], 422);
        }

        $pickupRequest->update(['status' => 'cancelled']);

        $pickupRequest->load(['bin.outlet', 'requestedBy', 'assignedTo']);

        return response()->json([
            'data' => $this->formatPickupRequest($pickupRequest),
            'message' => 'Pickup request cancelled successfully.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatPickupRequest(PickupRequest $pickupRequest): array
    {
        return [
            'id' => $pickupRequest->id,
            'bin_id' => $pickupRequest->bin_id,
            'request_type' => $pickupRequest->request_type,
            'reason' => $pickupRequest->reason,
            'status' => $pickupRequest->status,
            'requested_by' => $pickupRequest->requestedBy ? [
                'id' => $pickupRequest->requestedBy->id,
                'name' => $pickupRequest->requestedBy->name,
            ] : null,
            'assigned_to' => $pickupRequest->assignedTo ? [
                'id' => $pickupRequest->assignedTo->id,
                'name' => $pickupRequest->assignedTo->name,
            ] : null,
            'assigned_at' => $pickupRequest->assigned_at?->toIso8601String(),
            'completed_at' => $pickupRequest->completed_at?->toIso8601String(),
            'bin' => $pickupRequest->relationLoaded('bin') ? $pickupRequest->bin : null,
            'created_at' => $pickupRequest->created_at?->toIso8601String(),
            'updated_at' => $pickupRequest->updated_at?->toIso8601String(),
        ];
    }
}
