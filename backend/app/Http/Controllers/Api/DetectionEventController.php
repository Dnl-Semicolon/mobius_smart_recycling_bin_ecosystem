<?php

namespace App\Http\Controllers\Api;

use App\Enums\WasteType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDetectionRequest;
use App\Http\Resources\DetectionEventResource;
use App\Models\Brand;
use App\Models\DetectionEvent;
use App\Services\DetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class DetectionEventController extends Controller
{
    public function __construct(private DetectionService $detectionService) {}

    public function index(Request $request): JsonResponse
    {
        $query = DetectionEvent::query()->with(['bin', 'detectedBrand']);

        if ($request->has('bin_id')) {
            $query->where('bin_id', $request->input('bin_id'));
        }

        if ($request->has('waste_type')) {
            $query->where('waste_type', $request->input('waste_type'));
        }

        if ($request->has('min_confidence')) {
            $query->where('confidence', '>=', $request->input('min_confidence'));
        }

        if ($request->has('from')) {
            $query->where('detected_at', '>=', $request->input('from'));
        }

        if ($request->has('to')) {
            $query->where('detected_at', '<=', $request->input('to'));
        }

        $events = $query->latest('detected_at')->paginate();

        return DetectionEventResource::collection($events)
            ->additional(['message' => 'Detection events retrieved successfully.'])
            ->response();
    }

    public function store(StoreDetectionRequest $request): JsonResponse
    {
        $imagePath = null;
        if ($request->hasFile('image')) {
            $datePath = now()->format('Y/m/d');
            $imagePath = $request->file('image')->store("detection_images/{$datePath}", 'public');
        }

        $detectedAt = $request->input('detected_at', now()->toIso8601String());

        // Auto-attach user from bin session if not explicitly provided
        $userId = $request->input('user_id');
        if (! $userId) {
            $userId = Cache::pull("bin_session:{$request->input('bin_id')}");
        }

        // Resolve detected brand: accept either ID or slug/name string
        $detectedBrandId = $request->input('detected_brand_id');
        if (! $detectedBrandId && $request->filled('detected_brand')) {
            $detectedBrandId = Brand::query()
                ->where('slug', $request->input('detected_brand'))
                ->orWhere('name', $request->input('detected_brand'))
                ->value('id');
        }

        $event = DetectionEvent::create([
            'bin_id' => $request->input('bin_id'),
            'user_id' => $userId,
            'waste_type' => $request->input('waste_type'),
            'confidence' => $request->input('confidence'),
            'weight_g' => $request->input('weight_g'),
            'detected_brand_id' => $detectedBrandId,
            'image_path' => $imagePath,
            'detected_at' => $detectedAt,
        ]);

        $detections = [];
        if ($event->waste_type) {
            $detections[] = [
                'waste_type' => $event->waste_type->value,
                'confidence' => $event->confidence,
            ];
        }

        return response()->json([
            'data' => [
                'id' => $event->id,
                'bin_id' => $event->bin_id,
                'user_id' => $event->user_id,
                'detections' => $detections,
                'image_path' => $event->image_path,
                'detected_at' => $event->detected_at->toIso8601String(),
            ],
            'message' => 'Detection event created successfully.',
        ], 201);
    }

    /**
     * Classify a previously created detection event (server-side ML pipeline).
     * Updates waste_type + confidence and triggers fill/points processing.
     */
    public function classify(Request $request, DetectionEvent $detectionEvent): JsonResponse
    {
        $validated = $request->validate([
            'waste_type' => ['required', Rule::enum(WasteType::class)],
            'confidence' => ['required', 'integer', 'min:0', 'max:100'],
            'detected_brand_id' => ['nullable', 'integer', 'exists:brands,id'],
        ]);

        if ($detectionEvent->waste_type !== null) {
            return response()->json([
                'data' => null,
                'message' => 'Detection event has already been classified.',
            ], 409);
        }

        $detectionEvent->update(array_filter([
            'waste_type' => $validated['waste_type'],
            'confidence' => $validated['confidence'],
            'detected_brand_id' => $validated['detected_brand_id'] ?? null,
        ], fn ($v) => $v !== null));

        $this->detectionService->processDetection($detectionEvent->fresh());

        return response()->json([
            'data' => [
                'id' => $detectionEvent->id,
                'bin_id' => $detectionEvent->bin_id,
                'waste_type' => $detectionEvent->waste_type->value,
                'confidence' => $detectionEvent->confidence,
            ],
            'message' => 'Detection event classified successfully.',
        ]);
    }

    public function feedback(Request $request, DetectionEvent $detectionEvent): JsonResponse
    {
        $validated = $request->validate([
            'accurate' => ['required', 'boolean'],
        ]);

        $detectionEvent->update(['feedback_accurate' => $validated['accurate']]);

        return response()->json([
            'data' => [
                'id' => $detectionEvent->id,
                'feedback_accurate' => $detectionEvent->feedback_accurate,
            ],
            'message' => 'Feedback recorded successfully.',
        ]);
    }

    public function show(DetectionEvent $detectionEvent): JsonResponse
    {
        $detectionEvent->load(['bin.currentAssignment.outlet', 'detectedBrand']);

        return DetectionEventResource::make($detectionEvent)
            ->additional(['message' => 'Detection event retrieved successfully.'])
            ->response();
    }
}
