<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BinStatus;
use App\Enums\ContractStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignBinRequest;
use App\Http\Requests\StoreBinRequest;
use App\Http\Requests\UpdateBinRequest;
use App\Models\Bin;
use App\Models\BinAssignment;
use App\Models\Outlet;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BinController extends Controller
{
    public function index(Request $request): View
    {
        $bins = Bin::query()
            ->with(['currentAssignment.outlet'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('serial_number', 'like', '%'.$request->input('search').'%');
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->when($request->filled('outlet'), function ($query) use ($request) {
                $query->whereHas('currentAssignment', function ($q) use ($request) {
                    $q->where('outlet_id', $request->input('outlet'));
                });
            })
            ->when($request->boolean('ready_for_pickup'), function ($query) {
                $query->where('status', BinStatus::Active)
                    ->where('fill_level', '>=', 80);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statuses = BinStatus::cases();
        $outlets = Outlet::orderBy('name')->get(['id', 'name']);

        return view('admin.bins.index', compact('bins', 'statuses', 'outlets'));
    }

    public function create(): View
    {
        $statuses = BinStatus::cases();

        return view('admin.bins.create', compact('statuses'));
    }

    public function store(StoreBinRequest $request): RedirectResponse
    {
        $bin = Bin::create($request->validated());

        return redirect()
            ->route('admin.bins.show', $bin)
            ->with('success', 'Bin created successfully.');
    }

    public function show(Bin $bin): View
    {
        $bin->load([
            'currentAssignment.outlet',
            'assignments' => fn ($q) => $q->with('outlet')->latest('assigned_at'),
            'detectionEvents' => fn ($q) => $q->latest('detected_at')->limit(20),
        ]);

        $outlets = Outlet::where('contract_status', ContractStatus::Active)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.bins.show', compact('bin', 'outlets'));
    }

    public function edit(Bin $bin): View
    {
        $statuses = BinStatus::cases();

        return view('admin.bins.edit', compact('bin', 'statuses'));
    }

    public function update(UpdateBinRequest $request, Bin $bin): RedirectResponse
    {
        $bin->update($request->validated());

        return redirect()
            ->route('admin.bins.show', $bin)
            ->with('success', 'Bin updated successfully.');
    }

    public function destroy(Bin $bin): RedirectResponse
    {
        $bin->delete();

        return redirect()
            ->route('admin.bins.index')
            ->with('success', 'Bin deleted successfully.');
    }

    public function assign(AssignBinRequest $request, Bin $bin): RedirectResponse
    {
        // End any current assignment first
        $bin->currentAssignment?->update(['unassigned_at' => now()]);

        // Create new assignment
        BinAssignment::create([
            'bin_id' => $bin->id,
            'outlet_id' => $request->validated('outlet_id'),
            'assigned_at' => now(),
            'unassigned_at' => null,
        ]);

        $outlet = Outlet::find($request->validated('outlet_id'));

        return redirect()
            ->route('admin.bins.show', $bin)
            ->with('success', "Bin assigned to {$outlet->name} successfully.");
    }

    public function unassign(Bin $bin): RedirectResponse
    {
        if (! $bin->currentAssignment) {
            return redirect()
                ->route('admin.bins.show', $bin)
                ->with('error', 'Bin is not currently assigned to any outlet.');
        }

        $outletName = $bin->currentAssignment->outlet->name;
        $bin->currentAssignment->update(['unassigned_at' => now()]);

        return redirect()
            ->route('admin.bins.show', $bin)
            ->with('success', "Bin unassigned from {$outletName} successfully.");
    }
}
