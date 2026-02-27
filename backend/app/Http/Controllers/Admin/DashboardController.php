<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BinStatus;
use App\Enums\ContractStatus;
use App\Http\Controllers\Controller;
use App\Models\Bin;
use App\Models\DetectionEvent;
use App\Models\Outlet;
use App\Models\PickupRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(): View
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
            ],
        ];

        $binsNeedingPickup = Bin::where('status', BinStatus::Active)
            ->where('fill_level', '>=', 80)
            ->with('currentAssignment.outlet')
            ->orderByDesc('fill_level')
            ->limit(5)
            ->get();

        $recentDetections = DetectionEvent::with('bin')
            ->orderByDesc('detected_at')
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
            'weeklyDetections' => $this->getWeeklyDetections(),
            'pickupStatuses' => PickupRequest::query()
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
        ];

        return view('admin.dashboard', compact('stats', 'binsNeedingPickup', 'recentDetections', 'chartData'));
    }

    /**
     * @return array{labels: list<string>, data: list<int>}
     */
    private function getWeeklyDetections(): array
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $labels[] = $date->format('D');
            $data[] = DetectionEvent::whereDate('detected_at', $date)->count();
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
