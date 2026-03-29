<?php

namespace App\Http\Controllers\Api;

use App\Enums\BinStatus;
use App\Enums\PickupStatus;
use App\Http\Controllers\Controller;
use App\Models\Bin;
use App\Models\DetectionEvent;
use App\Models\Outlet;
use App\Models\PickupRequest;
use Illuminate\Http\JsonResponse;

class PublicStatsController extends Controller
{
    public function index(): JsonResponse
    {
        $wasteTypeDistribution = DetectionEvent::query()
            ->selectRaw('waste_type, count(*) as count')
            ->whereNotNull('waste_type')
            ->groupBy('waste_type')
            ->pluck('count', 'waste_type')
            ->toArray();

        return response()->json([
            'data' => [
                'total_detections' => DetectionEvent::count(),
                'pickups_completed' => PickupRequest::where('status', PickupStatus::Completed)->count(),
                'active_bins' => Bin::where('status', BinStatus::Active)->count(),
                'outlets_served' => Outlet::count(),
                'waste_type_distribution' => $wasteTypeDistribution,
            ],
            'message' => 'Community stats retrieved successfully.',
        ]);
    }
}
