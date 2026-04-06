<?php

namespace App\Http\Controllers\Brand;

use App\Http\Controllers\Admin\DetectionController as AdminDetectionController;
use App\Http\Controllers\Controller;
use App\Models\DetectionEvent;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DetectionController extends Controller
{
    public function index(Request $request): Response
    {
        $user = auth()->user();
        $brand = $user->organization?->brands()->first();

        $outletIds = $brand
            ? Outlet::where('brand_id', $brand->id)->pluck('id')
            : collect();

        $query = DetectionEvent::with([
            'binSession.bin.outlet.brand',
            'binSession.user:id,name,email',
            'detectedBrand:id,name',
        ])
            ->whereHas('binSession.bin', fn ($q) => $q->whereIn('outlet_id', $outletIds))
            ->latest();

        if ($request->filled('waste_type')) {
            $query->where('waste_type', $request->waste_type);
        }

        $events = $query->paginate(20)->through(fn (DetectionEvent $e) => AdminDetectionController::formatEvent($e));

        return Inertia::render('Brand/Detections/Index', [
            'events' => $events,
            'filters' => $request->only(['waste_type']),
            'brandName' => $brand?->name,
        ]);
    }
}
