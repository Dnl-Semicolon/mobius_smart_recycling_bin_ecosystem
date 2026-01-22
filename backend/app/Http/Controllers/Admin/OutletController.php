<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContractStatus;
use App\Http\Controllers\Controller;
use App\Models\Outlet;
use Illuminate\Contracts\View\View;
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

    public function show(Outlet $outlet): View
    {
        $outlet->loadCount(['currentBinAssignments as current_bins_count']);
        $outlet->load(['bins' => function ($query) {
            $query->with('currentAssignment')->latest();
        }]);

        return view('admin.outlets.show', compact('outlet'));
    }
}
