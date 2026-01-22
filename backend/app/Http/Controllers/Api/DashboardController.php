<?php

namespace App\Http\Controllers\Api;

use App\Enums\BinStatus;
use App\Enums\ContractStatus;
use App\Http\Controllers\Controller;
use App\Models\Bin;
use App\Models\DetectionEvent;
use App\Models\Outlet;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $stats = [
            'outlets' => [
                'total' => Outlet::count(),
                'active' => Outlet::where('contract_status', ContractStatus::Active)->count(),
                'inactive' => Outlet::where('contract_status', ContractStatus::Inactive)->count(),
                'pending' => Outlet::where('contract_status', ContractStatus::Pending)->count(),
            ],
            'bins' => [
                'total' => Bin::count(),
                'active' => Bin::where('status', BinStatus::Active)->count(),
                'inactive' => Bin::where('status', BinStatus::Inactive)->count(),
                'maintenance' => Bin::where('status', BinStatus::Maintenance)->count(),
                'ready_for_pickup' => Bin::where('status', BinStatus::Active)
                    ->where('fill_level', '>=', 80)
                    ->count(),
                'assigned' => Bin::whereHas('currentAssignment')->count(),
                'unassigned' => Bin::whereDoesntHave('currentAssignment')->count(),
            ],
            'detections' => [
                'total' => DetectionEvent::count(),
                'today' => DetectionEvent::whereDate('detected_at', today())->count(),
                'this_week' => DetectionEvent::where('detected_at', '>=', now()->startOfWeek())->count(),
                'by_waste_type' => $this->getDetectionsByWasteType(),
            ],
        ];

        return response()->json([
            'data' => $stats,
            'message' => 'Dashboard statistics retrieved successfully.',
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function getDetectionsByWasteType(): array
    {
        return DetectionEvent::query()
            ->selectRaw('waste_type, COUNT(*) as count')
            ->groupBy('waste_type')
            ->pluck('count', 'waste_type')
            ->toArray();
    }
}
