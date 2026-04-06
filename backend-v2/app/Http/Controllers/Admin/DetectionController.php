<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DetectionEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DetectionController extends Controller
{
    public function index(Request $request): Response
    {
        $query = DetectionEvent::with([
            'binSession.bin.outlet.brand',
            'binSession.user:id,name,email',
            'detectedBrand:id,name',
        ])->latest();

        if ($request->filled('waste_type')) {
            $query->where('waste_type', $request->waste_type);
        }

        if ($request->filled('brand_id')) {
            $query->where('detected_brand_id', $request->brand_id);
        }

        $events = $query->paginate(20)->through(fn (DetectionEvent $e) => self::formatEvent($e));

        return Inertia::render('Admin/Detections/Index', [
            'events' => $events,
            'filters' => $request->only(['waste_type', 'brand_id']),
        ]);
    }

    public static function formatEvent(DetectionEvent $e): array
    {
        return [
            'id' => $e->id,
            'waste_type' => $e->waste_type?->value,
            'confidence' => $e->confidence,
            'input_method' => $e->input_method,
            'detected_brand' => $e->detectedBrand?->name,
            'bin_serial' => $e->bin?->serial_number,
            'outlet' => $e->bin?->outlet?->name,
            'brand' => $e->bin?->outlet?->brand?->name,
            'user' => $e->binSession?->user?->name ?? 'Anonymous',
            'user_email' => $e->binSession?->user?->email,
            'image_path' => $e->image_path,
            'created_at' => $e->created_at?->format('Y-m-d H:i'),
        ];
    }
}
