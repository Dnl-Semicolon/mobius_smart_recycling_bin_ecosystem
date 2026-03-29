<?php

namespace App\Http\Controllers\Agency;

use App\Enums\RouteStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\CollectionRoute;
use App\Models\CollectorAgency;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class CollectorController extends Controller
{
    public function index(): View
    {
        $agency = CollectorAgency::where('user_id', auth()->id())->firstOrFail();

        $collectors = $agency->collectors()
            ->wherePivot('status', '!=', 'removed')
            ->withCount(['collectionRoutes as routes_completed' => function ($q) {
                $q->where('status', RouteStatus::Completed);
            }])
            ->get();

        return view('agency.collectors.index', compact('agency', 'collectors'));
    }

    public function invite(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $agency = CollectorAgency::where('user_id', auth()->id())->firstOrFail();

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            $user = User::create([
                'name' => explode('@', $request->email)[0],
                'email' => $request->email,
                'password' => 'temporary-'.str()->random(16),
                'roles' => ['collector'],
            ]);
        } elseif (! $user->hasRole(UserRole::Collector)) {
            $user->addRole(UserRole::Collector);
        }

        if ($agency->collectors()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'This user is already in your agency.');
        }

        $agency->collectors()->attach($user->id, [
            'status' => 'invited',
            'invited_at' => now(),
        ]);

        Log::info('Agency collector invitation — email would be sent', [
            'agency_id' => $agency->id,
            'invited_email' => $request->email,
        ]);

        return back()->with('success', "Invitation sent to {$request->email}.");
    }

    public function show(User $user): View
    {
        $agency = CollectorAgency::where('user_id', auth()->id())->firstOrFail();

        abort_unless(
            $agency->collectors()->where('user_id', $user->id)->exists(),
            404
        );

        $collectorIds = [$user->id];

        $routeStats = [
            'completed' => CollectionRoute::where('collector_id', $user->id)
                ->where('status', RouteStatus::Completed)->count(),
            'active' => CollectionRoute::where('collector_id', $user->id)
                ->where('status', RouteStatus::Active)->count(),
        ];

        $recentRoutes = CollectionRoute::where('collector_id', $user->id)
            ->latest()
            ->limit(20)
            ->get();

        return view('agency.collectors.show', compact('agency', 'user', 'routeStats', 'recentRoutes'));
    }

    public function remove(User $user): RedirectResponse
    {
        $agency = CollectorAgency::where('user_id', auth()->id())->firstOrFail();

        $agency->collectors()->updateExistingPivot($user->id, [
            'status' => 'removed',
        ]);

        return back()->with('success', "{$user->name} has been removed from the agency.");
    }
}
