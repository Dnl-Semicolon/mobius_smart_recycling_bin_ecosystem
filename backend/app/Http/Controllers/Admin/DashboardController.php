<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BinStatus;
use App\Http\Controllers\Controller;
use App\Models\Bin;
use App\Models\DetectionEvent;
use App\Models\Outlet;
use App\Models\PickupRequest;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'outlets' => [
                'total' => Outlet::count(),
                'active' => Outlet::where('is_active', true)->count(),
                'inactive' => Outlet::where('is_active', false)->count(),
                'pending' => 0,
            ],
            'bins' => [
                'total' => Bin::count(),
                'active' => Bin::where('status', BinStatus::Active)->count(),
                'inactive' => Bin::where('status', BinStatus::Offline)->count(),
                'maintenance' => Bin::where('status', BinStatus::Maintenance)->count(),
                'ready_for_pickup' => Bin::where('status', BinStatus::Active)
                    ->where('fill_level', '>=', 80)
                    ->count(),
                'assigned' => Bin::whereNotNull('outlet_id')->count(),
                'unassigned' => Bin::whereNull('outlet_id')->count(),
            ],
            'detections' => [
                'total' => DetectionEvent::count(),
                'today' => DetectionEvent::whereDate('created_at', today())->count(),
                'this_week' => DetectionEvent::where('created_at', '>=', now()->startOfWeek())->count(),
            ],
        ];

        $binsNeedingPickup = Bin::where('status', BinStatus::Active)
            ->where('fill_level', '>=', 80)
            ->with('outlet.brand')
            ->orderByDesc('fill_level')
            ->limit(5)
            ->get();

        $recentDetections = DetectionEvent::with('binSession.bin.outlet')
            ->latest()
            ->limit(10)
            ->get();

        // Chart data
        $chartData = [
            'wasteTypes' => DetectionEvent::query()
                ->selectRaw('waste_type, count(*) as count')
                ->whereNotNull('waste_type')
                ->groupBy('waste_type')
                ->pluck('count', 'waste_type')
                ->toArray(),
            'weeklyDetections' => DetectionEvent::weeklyChart(),
            'pickupStatuses' => PickupRequest::query()
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
        ];

        return view('admin.dashboard', compact('stats', 'binsNeedingPickup', 'recentDetections', 'chartData'));
    }
}
