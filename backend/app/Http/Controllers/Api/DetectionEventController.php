<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DetectionEventResource;
use App\Models\DetectionEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DetectionEventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DetectionEvent::query()->with('bin');

        // Filter by bin_id
        if ($request->has('bin_id')) {
            $query->where('bin_id', $request->input('bin_id'));
        }

        // Filter by waste_type
        if ($request->has('waste_type')) {
            $query->where('waste_type', $request->input('waste_type'));
        }

        // Filter by confidence threshold
        if ($request->has('min_confidence')) {
            $query->where('confidence', '>=', $request->input('min_confidence'));
        }

        // Filter by date range
        if ($request->has('from')) {
            $query->where('detected_at', '>=', $request->input('from'));
        }

        if ($request->has('to')) {
            $query->where('detected_at', '<=', $request->input('to'));
        }

        $events = $query->latest('detected_at')->paginate();

        return DetectionEventResource::collection($events)
            ->additional(['message' => 'Detection events retrieved successfully.'])
            ->response();
    }

    public function show(DetectionEvent $detectionEvent): JsonResponse
    {
        $detectionEvent->load('bin.currentAssignment.outlet');

        return DetectionEventResource::make($detectionEvent)
            ->additional(['message' => 'Detection event retrieved successfully.'])
            ->response();
    }
}
