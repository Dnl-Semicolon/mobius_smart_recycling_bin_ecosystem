<?php

namespace App\Http\Controllers\Public;

use App\Enums\PickupStatus;
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
        $impactStats = [
            'total_detections' => DetectionEvent::count(),
            'pickups_completed' => PickupRequest::where('status', PickupStatus::Completed)->count(),
            'active_bins' => Bin::where('status', 'active')->count(),
            'outlets_served' => Outlet::count(),
        ];

        $recentActivity = DetectionEvent::query()
            ->with('bin.currentAssignment.outlet')
            ->whereNotNull('waste_type')
            ->latest('detected_at')
            ->limit(10)
            ->get()
            ->map(fn ($event) => [
                'waste_type' => str_replace('_', ' ', $event->waste_type->value),
                'outlet' => $event->bin?->currentAssignment?->outlet?->name ?? 'Unknown',
                'time' => $event->detected_at->diffForHumans(),
            ]);

        $wasteTypeDistribution = DetectionEvent::query()
            ->selectRaw('waste_type, count(*) as count')
            ->whereNotNull('waste_type')
            ->groupBy('waste_type')
            ->pluck('count', 'waste_type')
            ->toArray();

        return view('public.dashboard', compact('impactStats', 'recentActivity', 'wasteTypeDistribution'));
    }
}
