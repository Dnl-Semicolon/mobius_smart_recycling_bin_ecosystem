<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\DetectionEvent;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrandMonitoringController extends Controller
{
    public function index(Request $request): View
    {
        $brands = Brand::query()
            ->withCount('outlets')
            ->when($request->input('status'), fn ($q, $status) => $q->where('is_active', $status === 'active'))
            ->orderBy('name')
            ->paginate(20);

        return view('admin.brands.index', compact('brands'));
    }

    public function show(Brand $brand): View
    {
        $brand->load(['outlets.bins']);

        $binIds = $brand->outlets->flatMap(fn ($o) => $o->bins->pluck('id'))->all();

        $stats = [
            'total_outlets' => $brand->outlets->count(),
            'total_bins' => count($binIds),
            'total_staff' => 0,
            'today_detections' => $binIds ? DetectionEvent::whereHas('binSession', fn ($q) => $q->whereIn('bin_id', $binIds))
                ->whereDate('created_at', today())->count() : 0,
            'month_detections' => $binIds ? DetectionEvent::whereHas('binSession', fn ($q) => $q->whereIn('bin_id', $binIds))
                ->where('created_at', '>=', now()->startOfMonth())->count() : 0,
            'active_rewards' => 0,
        ];

        return view('admin.brands.show', compact('brand', 'stats'));
    }
}
