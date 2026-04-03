<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BinStatus;
use App\Enums\PickupStatus;
use App\Enums\WasteType;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignBinRequest;
use App\Http\Requests\StoreBinRequest;
use App\Http\Requests\UpdateBinRequest;
use App\Models\Bin;
use App\Models\DetectionEvent;
use App\Models\Outlet;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BinController extends Controller
{
    public function index(Request $request): View
    {
        $bins = Bin::query()
            ->with(['outlet.brand'])
            ->withCount('binSessions')
            ->withMax('binSessions', 'created_at')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('serial_number', 'like', '%'.$request->input('search').'%');
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->when($request->filled('outlet'), function ($query) use ($request) {
                $query->where('outlet_id', $request->input('outlet'));
            })
            ->when($request->boolean('ready_for_pickup'), function ($query) {
                $query->where('status', BinStatus::Active)
                    ->where('fill_level', '>=', 80);
            })
            ->when($request->filled('sort'), function ($query) use ($request) {
                return match ($request->input('sort')) {
                    'fill_desc' => $query->orderByDesc('fill_level'),
                    'fill_asc' => $query->orderBy('fill_level'),
                    'serial' => $query->orderBy('serial_number'),
                    'recent' => $query->orderByDesc('updated_at'),
                    default => $query->orderByDesc('fill_level'),
                };
            }, fn ($q) => $q->orderByDesc('fill_level'))
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => Bin::count(),
            'active' => Bin::where('status', BinStatus::Active)->count(),
            'needing_pickup' => Bin::where('status', BinStatus::Active)->where('fill_level', '>=', 80)->count(),
            'avg_fill' => (int) Bin::where('status', BinStatus::Active)->avg('fill_level'),
        ];

        $statuses = BinStatus::cases();
        $outlets = Outlet::orderBy('name')->get(['id', 'name']);

        return view('admin.bins.index', compact('bins', 'summary', 'statuses', 'outlets'));
    }

    public function fleetStatus(): JsonResponse
    {
        $bins = Bin::all()->map(fn (Bin $bin) => [
            'id' => $bin->id,
            'fill_level' => $bin->fill_level,
            'status' => $bin->status->value,
        ]);

        return response()->json([
            'bins' => $bins->keyBy('id'),
            'summary' => [
                'total' => Bin::count(),
                'active' => Bin::where('status', BinStatus::Active)->count(),
                'needing_pickup' => Bin::where('status', BinStatus::Active)->where('fill_level', '>=', 80)->count(),
                'avg_fill' => (int) Bin::where('status', BinStatus::Active)->avg('fill_level'),
            ],
        ]);
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
        $bin->load(['outlet.brand']);

        $quickStats = [
            'today_detections' => DetectionEvent::whereHas('binSession', fn ($q) => $q->where('bin_id', $bin->id))
                ->whereDate('created_at', today())->count(),
            'week_detections' => DetectionEvent::whereHas('binSession', fn ($q) => $q->where('bin_id', $bin->id))
                ->where('created_at', '>=', now()->startOfWeek())->count(),
            'total_detections' => DetectionEvent::whereHas('binSession', fn ($q) => $q->where('bin_id', $bin->id))->count(),
            'total_pickups' => $bin->pickupRequests()->count(),
            'completed_pickups' => $bin->pickupRequests()->where('status', PickupStatus::Completed)->count(),
            'most_common_waste' => DetectionEvent::whereHas('binSession', fn ($q) => $q->where('bin_id', $bin->id))
                ->whereNotNull('waste_type')
                ->toBase()
                ->selectRaw('waste_type, count(*) as count')
                ->groupBy('waste_type')
                ->orderByDesc('count')
                ->value('waste_type'),
            'avg_confidence' => (int) DetectionEvent::whereHas('binSession', fn ($q) => $q->where('bin_id', $bin->id))
                ->whereNotNull('confidence')->avg('confidence'),
            'days_assigned' => $bin->paired_at
                ? $bin->paired_at->diffInDays(now())
                : null,
        ];

        $wasteTypeChart = DetectionEvent::whereHas('binSession', fn ($q) => $q->where('bin_id', $bin->id))
            ->whereNotNull('waste_type')
            ->selectRaw('waste_type, count(*) as count')
            ->groupBy('waste_type')
            ->pluck('count', 'waste_type')
            ->toArray();

        $recentActivity = DetectionEvent::whereHas('binSession', fn ($q) => $q->where('bin_id', $bin->id))
            ->with('binSession.bin')
            ->latest()
            ->limit(8)
            ->get();

        $telemetry = [
            'solids_fill' => $bin->fill_level,
            'solids_weight_g' => $bin->weight_grams,
            'solids_volume_ml' => 0,
            'solids_max_ml' => (int) ($bin->capacity_liters * 1000),
            'liquid_fill' => 0,
            'liquid_weight_g' => 0,
            'liquid_volume_ml' => 0,
            'liquid_max_ml' => 2000,
            'total_weight_g' => $bin->weight_grams,
            'fill_height_cm' => 0,
            'is_online' => true,
            'last_seen_at' => $bin->updated_at,
            'ip_address' => null,
            'total_weight_detections' => $bin->weight_grams,
        ];

        $outlets = Outlet::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.bins.show', compact('bin', 'outlets', 'quickStats', 'wasteTypeChart', 'recentActivity', 'telemetry'));
    }

    public function telemetry(Bin $bin): JsonResponse
    {
        $bin->refresh();

        return response()->json([
            'fill_level' => $bin->fill_level,
            'status' => $bin->status->value,
            'solids_fill' => $bin->fill_level,
            'solids_weight_g' => $bin->weight_grams,
            'total_weight_g' => $bin->weight_grams,
            'is_online' => true,
            'today_detections' => DetectionEvent::whereHas('binSession', fn ($q) => $q->where('bin_id', $bin->id))
                ->whereDate('created_at', today())->count(),
            'total_items' => DetectionEvent::whereHas('binSession', fn ($q) => $q->where('bin_id', $bin->id))->count(),
        ]);
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
        $outlet = Outlet::findOrFail($request->validated('outlet_id'));
        $bin->update(['outlet_id' => $outlet->id, 'paired_at' => now()]);

        return redirect()
            ->route('admin.bins.show', $bin)
            ->with('success', "Bin assigned to {$outlet->name} successfully.");
    }

    public function unassign(Bin $bin): RedirectResponse
    {
        if (! $bin->outlet_id) {
            return redirect()
                ->route('admin.bins.show', $bin)
                ->with('error', 'Bin is not currently assigned to any outlet.');
        }

        $outletName = $bin->outlet?->name ?? 'Unknown';
        $bin->update(['outlet_id' => null]);

        return redirect()
            ->route('admin.bins.show', $bin)
            ->with('success', "Bin unassigned from {$outletName} successfully.");
    }

    public function detections(Request $request, Bin $bin): View
    {
        $bin->load('outlet.brand');

        $detections = DetectionEvent::whereHas('binSession', fn ($q) => $q->where('bin_id', $bin->id))
            ->with('binSession.bin')
            ->when($request->filled('waste_type'), function ($query) use ($request) {
                $query->where('waste_type', $request->input('waste_type'));
            })
            ->when($request->filled('min_confidence'), function ($query) use ($request) {
                $query->where('confidence', '>=', $request->input('min_confidence'));
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->input('date_from'));
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->input('date_to'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $wasteTypes = WasteType::cases();

        return view('admin.bins.detections', compact('bin', 'detections', 'wasteTypes'));
    }

    public function pickups(Request $request, Bin $bin): View
    {
        $bin->load('outlet.brand');

        $pickups = $bin->pickupRequests()
            ->with('claimedBy')
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => $bin->pickupRequests()->count(),
            'completed' => $bin->pickupRequests()->where('status', PickupStatus::Completed)->count(),
            'avg_response_minutes' => 0,
            'last_collected' => $bin->pickupRequests()->where('status', PickupStatus::Completed)->max('completed_at'),
        ];

        return view('admin.bins.pickups', compact('bin', 'pickups', 'stats'));
    }

    public function analytics(Bin $bin): View
    {
        $bin->load('outlet.brand');

        $chartData = [
            'wasteTypes' => DetectionEvent::whereHas('binSession', fn ($q) => $q->where('bin_id', $bin->id))
                ->whereNotNull('waste_type')
                ->selectRaw('waste_type, count(*) as count')
                ->groupBy('waste_type')
                ->pluck('count', 'waste_type')
                ->toArray(),
            'weeklyDetections' => DetectionEvent::weeklyChart($bin),
            'confidenceDistribution' => $this->getConfidenceDistribution($bin),
        ];

        $stats = [
            'total_detections' => DetectionEvent::whereHas('binSession', fn ($q) => $q->where('bin_id', $bin->id))->count(),
            'unique_waste_types' => DetectionEvent::whereHas('binSession', fn ($q) => $q->where('bin_id', $bin->id))
                ->whereNotNull('waste_type')->distinct('waste_type')->count('waste_type'),
            'avg_confidence' => (int) DetectionEvent::whereHas('binSession', fn ($q) => $q->where('bin_id', $bin->id))
                ->whereNotNull('confidence')->avg('confidence'),
            'total_pickups' => $bin->pickupRequests()->count(),
            'completed_pickups' => $bin->pickupRequests()->where('status', PickupStatus::Completed)->count(),
        ];

        return view('admin.bins.analytics', compact('bin', 'chartData', 'stats'));
    }

    /**
     * @return array{labels: list<string>, data: list<int>}
     */
    private function getConfidenceDistribution(Bin $bin): array
    {
        $ranges = ['0-49' => [0, 49], '50-69' => [50, 69], '70-84' => [70, 84], '85-94' => [85, 94], '95-100' => [95, 100]];
        $labels = [];
        $data = [];

        foreach ($ranges as $label => [$min, $max]) {
            $labels[] = $label;
            $data[] = DetectionEvent::whereHas('binSession', fn ($q) => $q->where('bin_id', $bin->id))
                ->whereNotNull('confidence')
                ->whereBetween('confidence', [$min, $max])
                ->count();
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
