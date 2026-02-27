<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PickupStatus;
use App\Http\Controllers\Controller;
use App\Models\PickupRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PickupRequestController extends Controller
{
    public function index(Request $request): View
    {
        $pickupRequests = PickupRequest::query()
            ->with(['bin.currentAssignment.outlet', 'claimedBy'])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->when($request->filled('bin'), function ($query) use ($request) {
                $query->where('bin_id', $request->input('bin'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statuses = PickupStatus::cases();

        return view('admin.pickup-requests.index', compact('pickupRequests', 'statuses'));
    }

    public function show(PickupRequest $pickupRequest): View
    {
        $pickupRequest->load(['bin.currentAssignment.outlet', 'claimedBy']);

        return view('admin.pickup-requests.show', compact('pickupRequest'));
    }

    public function cancel(PickupRequest $pickupRequest): RedirectResponse
    {
        if (! $pickupRequest->cancel()) {
            return redirect()->route('admin.pickup-requests.show', $pickupRequest)
                ->with('error', 'This pickup cannot be cancelled.');
        }

        return redirect()->route('admin.pickup-requests.show', $pickupRequest)
            ->with('success', 'Pickup request has been cancelled.');
    }

    public function unclaim(PickupRequest $pickupRequest): RedirectResponse
    {
        if (! $pickupRequest->unclaim()) {
            return redirect()->route('admin.pickup-requests.show', $pickupRequest)
                ->with('error', 'This pickup cannot be returned to the queue.');
        }

        return redirect()->route('admin.pickup-requests.show', $pickupRequest)
            ->with('success', 'Pickup request returned to the pending queue.');
    }
}
