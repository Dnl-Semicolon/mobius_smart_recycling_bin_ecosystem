<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Bin;
use App\Models\Outlet;
use App\Models\PickupRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BinController extends Controller
{
    public function index(): Response
    {
        $outletIds = Outlet::where('user_id', auth()->id())->pluck('id');

        $bins = Bin::whereIn('outlet_id', $outletIds)
            ->with([
                'outlet:id,name',
                'pickupRequests' => fn ($q) => $q->where('status', 'pending')->select('id', 'bin_id', 'status'),
            ])
            ->orderByDesc('fill_level')
            ->paginate(15)
            ->through(fn (Bin $bin) => [
                'id' => $bin->id,
                'serial_number' => $bin->serial_number,
                'outlet' => $bin->outlet?->name ?? '-',
                'status' => $bin->status,
                'fill_level' => $bin->fill_level,
                'has_pending_pickup' => $bin->pickupRequests->isNotEmpty(),
            ]);

        return Inertia::render('Store/Bins/Index', ['bins' => $bins]);
    }

    public function requestPickup(Bin $bin): RedirectResponse
    {
        $outletIds = Outlet::where('user_id', auth()->id())->pluck('id');

        abort_unless($outletIds->contains($bin->outlet_id), 403);

        $alreadyPending = $bin->pickupRequests()->where('status', 'pending')->exists();

        if ($alreadyPending) {
            return back()->withErrors(['pickup' => 'A pickup request is already pending for this bin.']);
        }

        PickupRequest::create([
            'bin_id' => $bin->id,
            'request_type' => 'emergency',
            'requested_by' => auth()->id(),
            'reason' => "Requested by store owner at {$bin->fill_level}% fill level.",
            'status' => 'pending',
        ]);

        return back();
    }
}
