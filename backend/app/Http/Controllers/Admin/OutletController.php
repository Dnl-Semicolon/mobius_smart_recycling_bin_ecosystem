<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOutletRequest;
use App\Http\Requests\UpdateOutletRequest;
use App\Models\Outlet;
use App\Services\OutletPhotoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OutletController extends Controller
{
    public function __construct(private OutletPhotoService $photoService) {}

    public function index(Request $request): View
    {
        $outlets = Outlet::query()
            ->withCount(['bins as current_bins_count'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $isActive = $request->input('status') === 'active';
                $query->where('is_active', $isActive);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $statuses = [
            (object) ['value' => 'active', 'label' => 'Active'],
            (object) ['value' => 'inactive', 'label' => 'Inactive'],
        ];

        $statusCounts = [
            'active' => Outlet::where('is_active', true)->count(),
            'inactive' => Outlet::where('is_active', false)->count(),
        ];

        return view('admin.outlets.index', compact('outlets', 'statuses', 'statusCounts'));
    }

    public function create(): View
    {
        $statuses = [
            (object) ['value' => 'active', 'label' => 'Active'],
            (object) ['value' => 'inactive', 'label' => 'Inactive'],
        ];

        return view('admin.outlets.create', compact('statuses'));
    }

    public function store(StoreOutletRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('photo');

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $this->photoService->process($request->file('photo'));
        }

        $outlet = Outlet::create($data);

        return redirect()
            ->route('admin.outlets.show', $outlet)
            ->with('success', 'Outlet created successfully.');
    }

    public function show(Outlet $outlet): View
    {
        $outlet->loadCount(['bins as current_bins_count']);
        $outlet->load(['brand', 'bins' => function ($query) {
            $query->latest();
        }]);

        return view('admin.outlets.show', compact('outlet'));
    }

    public function edit(Outlet $outlet): View
    {
        $statuses = [
            (object) ['value' => 'active', 'label' => 'Active'],
            (object) ['value' => 'inactive', 'label' => 'Inactive'],
        ];

        return view('admin.outlets.edit', compact('outlet', 'statuses'));
    }

    public function update(UpdateOutletRequest $request, Outlet $outlet): RedirectResponse
    {
        $data = $request->safe()->except(['photo', 'remove_photo']);

        if ($request->hasFile('photo')) {
            $this->photoService->delete($outlet->photo_path);
            $data['photo_path'] = $this->photoService->process($request->file('photo'));
        } elseif ($request->boolean('remove_photo')) {
            $this->photoService->delete($outlet->photo_path);
            $data['photo_path'] = null;
        }

        $outlet->update($data);

        return redirect()
            ->route('admin.outlets.show', $outlet)
            ->with('success', 'Outlet updated successfully.');
    }

    public function destroy(Outlet $outlet): RedirectResponse
    {
        $this->photoService->delete($outlet->photo_path);
        $outlet->delete();

        return redirect()
            ->route('admin.outlets.index')
            ->with('success', 'Outlet deleted successfully.');
    }
}
