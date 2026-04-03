<?php

namespace App\Http\Controllers\Admin;

use App\Enums\WasteType;
use App\Http\Controllers\Controller;
use App\Http\Requests\SimulateDetectionRequest;
use App\Models\Bin;
use App\Models\BinSession;
use App\Models\DetectionEvent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DetectionEventController extends Controller
{
    public function index(Request $request): View
    {
        $query = DetectionEvent::query()->with(['binSession.bin.outlet.brand', 'detectedBrand']);

        // Filter by bin
        if ($request->filled('bin')) {
            $query->whereHas('binSession', fn ($q) => $q->where('bin_id', $request->input('bin')));
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
            $query->whereDate('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        $events = $query->latest()
            ->paginate(15)
            ->withQueryString();

        $bins = Bin::orderBy('serial_number')->get(['id', 'serial_number']);
        $wasteTypes = WasteType::cases();

        return view('admin.detection-events.index', compact('events', 'bins', 'wasteTypes'));
    }

    public function show(DetectionEvent $detectionEvent): View
    {
        $detectionEvent->load(['binSession.bin.outlet.brand', 'detectedBrand']);

        return view('admin.detection-events.show', compact('detectionEvent'));
    }

    /**
     * Simulate a detection event — for demo purposes.
     */
    public function simulate(SimulateDetectionRequest $request): RedirectResponse
    {
        $bin = Bin::findOrFail($request->input('bin_id'));
        $confidence = $request->input('confidence') ?? rand(75, 99);

        // Create or reuse an active session for this bin
        $session = BinSession::firstOrCreate(
            ['bin_id' => $bin->id, 'status' => 'active'],
            ['started_at' => now(), 'cup_rinsed' => false],
        );

        DetectionEvent::create([
            'bin_session_id' => $session->id,
            'waste_type' => $request->input('waste_type'),
            'confidence' => $confidence,
            'input_method' => 'general_intake',
            'image_path' => '',
            'ai_output' => ['source' => 'admin_simulation'],
        ]);

        return redirect()->route('admin.detection-events.index')
            ->with('success', "Simulated detection: {$request->input('waste_type')} in {$bin->serial_number} (fill now {$bin->fresh()->fill_level}%)");
    }
}
