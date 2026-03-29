<?php

namespace App\Http\Controllers\Agency;

use App\Enums\RouteStatus;
use App\Http\Controllers\Controller;
use App\Models\CollectionRoute;
use App\Models\CollectorAgency;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $agency = CollectorAgency::where('user_id', auth()->id())->first();
        abort_if(! $agency, 403, 'No agency assigned to your account.');

        $collectorIds = $agency->collectors()->pluck('users.id')->toArray();

        $totalCollectors = count($collectorIds);

        $activeRoutes = $collectorIds
            ? CollectionRoute::whereIn('collector_id', $collectorIds)
                ->where('status', RouteStatus::Active)
                ->count()
            : 0;

        $completedThisWeek = $collectorIds
            ? CollectionRoute::whereIn('collector_id', $collectorIds)
                ->where('status', RouteStatus::Completed)
                ->where('updated_at', '>=', now()->startOfWeek())
                ->count()
            : 0;

        $totalCompleted = $collectorIds
            ? CollectionRoute::whereIn('collector_id', $collectorIds)
                ->where('status', RouteStatus::Completed)
                ->count()
            : 0;

        $recentRoutes = $collectorIds
            ? CollectionRoute::whereIn('collector_id', $collectorIds)
                ->with('collector')
                ->latest()
                ->limit(10)
                ->get()
            : collect();

        return view('agency.dashboard', compact(
            'agency',
            'totalCollectors',
            'activeRoutes',
            'completedThisWeek',
            'totalCompleted',
            'recentRoutes',
        ));
    }

    public function analytics(): View
    {
        $agency = CollectorAgency::where('user_id', auth()->id())->first();
        abort_if(! $agency, 403, 'No agency assigned to your account.');

        $collectorIds = $agency->collectors()->pluck('users.id')->toArray();

        $dailyRoutes = $collectorIds
            ? CollectionRoute::whereIn('collector_id', $collectorIds)
                ->where('status', RouteStatus::Completed)
                ->where('updated_at', '>=', now()->subDays(14))
                ->selectRaw('DATE(updated_at) as date, COUNT(*) as count')
                ->groupByRaw('DATE(updated_at)')
                ->orderBy('date')
                ->pluck('count', 'date')
            : collect();

        $collectorStats = $collectorIds
            ? CollectionRoute::whereIn('collector_id', $collectorIds)
                ->where('status', RouteStatus::Completed)
                ->with('collector')
                ->selectRaw('collector_id, COUNT(*) as routes_completed')
                ->groupBy('collector_id')
                ->get()
            : collect();

        return view('agency.analytics', compact('agency', 'dailyRoutes', 'collectorStats'));
    }
}
