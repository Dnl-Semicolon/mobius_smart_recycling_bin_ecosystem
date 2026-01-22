<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContractStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOutletRequest;
use App\Http\Requests\UpdateOutletRequest;
use App\Models\Outlet;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OutletController extends Controller
{
    public function index(Request $request): View
    {
        $outlets = Outlet::query()
            ->withCount(['currentBinAssignments as current_bins_count'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('contract_status', $request->input('status'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statuses = ContractStatus::cases();

        return view('admin.outlets.index', compact('outlets', 'statuses'));
    }

    public function create(): View
    {
        $statuses = ContractStatus::cases();

        return view('admin.outlets.create', compact('statuses'));
    }

    public function store(StoreOutletRequest $request): RedirectResponse
    {
        $outlet = Outlet::create($request->validated());

        return redirect()
            ->route('admin.outlets.show', $outlet)
            ->with('success', 'Outlet created successfully.');
    }

    public function show(Outlet $outlet): View
    {
        $outlet->loadCount(['currentBinAssignments as current_bins_count']);
        $outlet->load(['bins' => function ($query) {
            $query->with('currentAssignment')->latest();
        }]);

        return view('admin.outlets.show', compact('outlet'));
    }

    public function edit(Outlet $outlet): View
    {
        $statuses = ContractStatus::cases();

        return view('admin.outlets.edit', compact('outlet', 'statuses'));
    }

    public function update(UpdateOutletRequest $request, Outlet $outlet): RedirectResponse
    {
        $outlet->update($request->validated());

        return redirect()
            ->route('admin.outlets.show', $outlet)
            ->with('success', 'Outlet updated successfully.');
    }

    public function destroy(Outlet $outlet): RedirectResponse
    {
        $outlet->delete();

        return redirect()
            ->route('admin.outlets.index')
            ->with('success', 'Outlet deleted successfully.');
    }
}
