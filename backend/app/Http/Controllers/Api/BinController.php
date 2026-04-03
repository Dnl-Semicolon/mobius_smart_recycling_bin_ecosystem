<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBinRequest;
use App\Http\Requests\UpdateBinRequest;
use App\Http\Resources\BinResource;
use App\Models\Bin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BinController extends Controller
{
    public function index(): JsonResponse
    {
        $bins = Bin::query()
            ->with(['outlet.brand'])
            ->latest()
            ->paginate();

        return BinResource::collection($bins)
            ->additional(['message' => 'Bins retrieved successfully.'])
            ->response();
    }

    public function store(StoreBinRequest $request): JsonResponse
    {
        $bin = Bin::create($request->validated());

        return BinResource::make($bin)
            ->additional(['message' => 'Bin created successfully.'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Bin $bin): JsonResponse
    {
        $bin->load(['outlet.brand']);

        return response()->json([
            'data' => [
                'id' => $bin->id,
                'serial_number' => $bin->serial_number,
                'status' => $bin->status?->value,
                'fill_level' => $bin->fill_level,
                'weight_grams' => $bin->weight_grams,
                'capacity_liters' => $bin->capacity_liters,
                'sensor_levels' => $bin->sensor_levels,
                'latitude' => $bin->latitude,
                'longitude' => $bin->longitude,
                'paired_at' => $bin->paired_at?->toIso8601String(),
                'last_pickup_at' => $bin->last_pickup_at?->toIso8601String(),
                'outlet' => $bin->outlet,
                'is_ready_for_pickup' => $bin->isReadyForPickup(),
                'created_at' => $bin->created_at?->toIso8601String(),
                'updated_at' => $bin->updated_at?->toIso8601String(),
            ],
            'message' => 'Bin retrieved successfully.',
        ]);
    }

    public function update(UpdateBinRequest $request, Bin $bin): JsonResponse
    {
        $bin->update($request->validated());

        return BinResource::make($bin)
            ->additional(['message' => 'Bin updated successfully.'])
            ->response();
    }

    public function destroy(Bin $bin): JsonResponse
    {
        $bin->delete();

        return response()->json([
            'data' => null,
            'message' => 'Bin deleted successfully.',
        ]);
    }

    /**
     * Pair a bin with an outlet, generating an API token for bin authentication.
     */
    public function pair(Request $request, Bin $bin): JsonResponse
    {
        $validated = $request->validate([
            'outlet_id' => ['required', 'integer', 'exists:outlets,id'],
        ]);

        $bin->update([
            'outlet_id' => $validated['outlet_id'],
            'api_token' => bin2hex(random_bytes(32)),
            'status' => 'active',
            'paired_at' => now(),
        ]);

        $bin->load('outlet.brand');

        return response()->json([
            'data' => [
                'id' => $bin->id,
                'serial_number' => $bin->serial_number,
                'api_token' => $bin->api_token,
                'status' => $bin->status?->value,
                'paired_at' => $bin->paired_at?->toIso8601String(),
                'outlet' => $bin->outlet,
            ],
            'message' => 'Bin paired with outlet successfully.',
        ]);
    }

    /**
     * Public endpoint to generate a QR code SVG for a bin's serial number.
     */
    public function qrCode(Bin $bin): Response
    {
        $size = min(max(request()->integer('size', 300), 100), 1000);

        $svg = QrCode::format('svg')
            ->size($size)
            ->errorCorrection('M')
            ->generate($bin->serial_number);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Public endpoint for IoT bins to resolve their serial number to a bin ID.
     */
    public function resolve(string $serial): JsonResponse
    {
        $bin = Bin::where('serial_number', $serial)->first();

        if (! $bin) {
            return response()->json([
                'data' => null,
                'message' => 'Bin not found.',
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $bin->id,
                'serial_number' => $bin->serial_number,
            ],
            'message' => 'Bin resolved.',
        ]);
    }

    /**
     * Public heartbeat endpoint for IoT bins to report their status.
     */
    public function heartbeat(Request $request, Bin $bin): JsonResponse
    {
        $validated = $request->validate([
            'fill_level' => ['required', 'integer', 'min:0', 'max:100'],
            'weight_grams' => ['sometimes', 'integer', 'min:0'],
            'sensor_levels' => ['sometimes', 'array'],
            'sensor_levels.level_25' => ['sometimes', 'boolean'],
            'sensor_levels.level_50' => ['sometimes', 'boolean'],
            'sensor_levels.level_75' => ['sometimes', 'boolean'],
            'sensor_levels.level_100' => ['sometimes', 'boolean'],
        ]);

        $updateData = [
            'fill_level' => $validated['fill_level'],
        ];

        if (isset($validated['weight_grams'])) {
            $updateData['weight_grams'] = $validated['weight_grams'];
        }

        if (isset($validated['sensor_levels'])) {
            $updateData['sensor_levels'] = $validated['sensor_levels'];
        }

        $bin->update($updateData);

        return response()->json([
            'data' => [
                'id' => $bin->id,
                'fill_level' => $bin->fill_level,
                'weight_grams' => $bin->weight_grams,
                'sensor_levels' => $bin->sensor_levels,
            ],
            'message' => 'Heartbeat received.',
        ]);
    }
}
