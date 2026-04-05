<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bin;
use App\Models\Outlet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BinController extends Controller
{
    public function index(): Response
    {
        $bins = Bin::query()
            ->with('outlet:id,name')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->through(fn (Bin $bin) => [
                'id' => $bin->id,
                'serial_number' => $bin->serial_number,
                'outlet' => $bin->outlet?->name ?? 'Unpaired',
                'status' => $bin->status,
                'fill_level' => $bin->fill_level,
                'paired_at' => $bin->paired_at?->format('Y-m-d'),
            ]);

        return Inertia::render('Admin/Bins/Index', [
            'bins' => $bins,
        ]);
    }

    public function create(): Response
    {
        $outlets = Outlet::with('brand:id,name')
            ->where('is_active', true)
            ->get()
            ->map(fn (Outlet $o) => [
                'id' => $o->id,
                'label' => $o->brand->name.' — '.$o->name,
            ]);

        return Inertia::render('Admin/Bins/Create', [
            'outlets' => $outlets,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'outlet_id' => ['required', 'exists:outlets,id'],
        ]);

        $outlet = Outlet::find($validated['outlet_id']);

        $bin = Bin::create([
            'outlet_id' => $validated['outlet_id'],
            'serial_number' => 'MBS-TEMP',
            'api_token' => bin2hex(random_bytes(32)),
            'status' => 'active',
            'fill_level' => 0,
            'weight_grams' => 0,
            'capacity_liters' => 20.00,
            'sensor_levels' => ['level_25' => false, 'level_50' => false, 'level_75' => false, 'level_100' => false],
            'latitude' => $outlet->latitude,
            'longitude' => $outlet->longitude,
            'paired_at' => now(),
        ]);

        $bin->update(['serial_number' => 'MBS-'.str_pad((string) $bin->id, 4, '0', STR_PAD_LEFT)]);

        return redirect()->route('admin.bins.index');
    }
}
