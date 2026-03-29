<?php

namespace App\Http\Controllers\Collector;

use App\Enums\PickupStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\PickupRequest;
use App\Models\User;
use App\Notifications\PickupCompleted;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;

class DashboardController extends Controller
{
    public function index(): View
    {
        $collectorId = auth()->id();

        $availablePickups = PickupRequest::query()
            ->pending()
            ->with(['bin.currentAssignment.outlet'])
            ->latest()
            ->get();

        $myActivePickups = PickupRequest::query()
            ->claimed()
            ->where('claimed_by', $collectorId)
            ->with(['bin.currentAssignment.outlet'])
            ->latest('claimed_at')
            ->get();

        $myHistory = PickupRequest::query()
            ->where('status', PickupStatus::Completed)
            ->where('claimed_by', $collectorId)
            ->with(['bin.currentAssignment.outlet'])
            ->latest('completed_at')
            ->limit(20)
            ->get();

        $stats = [
            'total_completed' => PickupRequest::query()
                ->where('status', PickupStatus::Completed)
                ->where('claimed_by', $collectorId)
                ->count(),
            'this_week' => PickupRequest::query()
                ->where('status', PickupStatus::Completed)
                ->where('claimed_by', $collectorId)
                ->where('completed_at', '>=', now()->startOfWeek())
                ->count(),
            'avg_minutes' => PickupRequest::avgResponseMinutes($collectorId),
            'active_now' => $myActivePickups->count(),
        ];

        return view('collector.dashboard', compact('availablePickups', 'myActivePickups', 'myHistory', 'stats'));
    }

    public function claim(PickupRequest $pickupRequest): RedirectResponse
    {
        if (! $pickupRequest->claimByCollector((int) auth()->id())) {
            return redirect()->route('collector.dashboard')
                ->with('error', 'This pickup is no longer available for claiming.');
        }

        $bin = $pickupRequest->bin;

        return redirect()->route('collector.dashboard')
            ->with('success', "Claimed pickup for bin {$bin?->serial_number}!");
    }

    public function complete(PickupRequest $pickupRequest): RedirectResponse
    {
        $result = $pickupRequest->completeByCollector((int) auth()->id());

        if ($result === PickupRequest::COMPLETE_RESULT_FORBIDDEN) {
            return redirect()->route('collector.dashboard')
                ->with('error', 'You can only complete pickups you have claimed.');
        }

        if ($result === PickupRequest::COMPLETE_RESULT_INVALID_STATE) {
            return redirect()->route('collector.dashboard')
                ->with('error', 'This pickup cannot be completed.');
        }

        $bin = $pickupRequest->bin;

        $admins = User::whereJsonContains('roles', UserRole::Admin->value)->get();
        Notification::send($admins, new PickupCompleted($pickupRequest));

        return redirect()->route('collector.dashboard')
            ->with('success', "Completed pickup for bin {$bin->serial_number}! Fill level reset to 0%.");
    }
}
