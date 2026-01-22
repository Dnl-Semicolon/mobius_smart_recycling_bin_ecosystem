<?php

namespace App\Http\Controllers\Admin;

use App\Enums\WasteType;
use App\Http\Controllers\Controller;
use App\Models\Bin;
use App\Models\DetectionEvent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DetectionEventController extends Controller
{
    public function index(Request $request): View
    {
        $query = DetectionEvent::query()->with(['bin.currentAssignment.outlet']);

        // Filter by bin_id
        if ($request->filled('bin')) {
            $query->where('bin_id', $request->input('bin'));
        }

        // Filter by waste_type
        if ($request->filled('waste_type')) {
            $query->where('waste_type', $request->input('waste_type'));
        }

        // Filter by confidence threshold
        if ($request->filled('min_confidence')) {
            $query->where('confidence', '>=', $request->input('min_confidence'));
        }

        // Filter by date range
        if ($request->filled('from')) {
            $query->whereDate('detected_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('detected_at', '<=', $request->input('to'));
        }

        $events = $query->latest('detected_at')
            ->paginate(15)
            ->withQueryString();

        $bins = Bin::orderBy('serial_number')->get(['id', 'serial_number']);
        $wasteTypes = WasteType::cases();

        return view('admin.detection-events.index', compact('events', 'bins', 'wasteTypes'));
    }

    public function show(DetectionEvent $detectionEvent): View
    {
        $detectionEvent->load('bin.currentAssignment.outlet');

        return view('admin.detection-events.show', compact('detectionEvent'));
    }
}
