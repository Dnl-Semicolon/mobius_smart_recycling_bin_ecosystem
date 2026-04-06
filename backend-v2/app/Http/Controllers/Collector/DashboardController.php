<?php

namespace App\Http\Controllers\Collector;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        $pendingPickups = DB::table('pickup_requests')->where('status', 'pending')->count();
        $assignedPickups = DB::table('pickup_requests')->where('assigned_to', $user->id)->where('status', 'assigned')->count();
        $completedRoutes = DB::table('collection_routes')->where('collector_id', $user->id)->where('status', 'completed')->count();
        $activeRoutes = DB::table('collection_routes')->where('collector_id', $user->id)->whereIn('status', ['pending', 'accepted', 'in_progress'])->count();

        return Inertia::render('Collector/Dashboard', [
            'stats' => [
                'pending_pickups' => $pendingPickups,
                'assigned_pickups' => $assignedPickups,
                'active_routes' => $activeRoutes,
                'completed_routes' => $completedRoutes,
            ],
        ]);
    }
}
