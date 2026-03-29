<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignBinRequest;
use App\Http\Requests\StoreBinRequest;
use App\Http\Requests\UpdateBinRequest;
use App\Http\Resources\BinResource;
use App\Models\Bin;
use App\Models\BinAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BinController extends Controller
{
    public function index(): JsonResponse
    {
        $bins = Bin::query()
            ->with(['currentAssignment.outlet'])
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
        $bin->load([
            'currentAssignment.outlet',
            'assignments' => fn ($q) => $q->with('outlet')->latest('assigned_at'),
            'detectionEvents' => fn ($q) => $q->latest('detected_at')->limit(10),
        ]);

        return BinResource::make($bin)
            ->additional(['message' => 'Bin retrieved successfully.'])
            ->response();
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

    public function assign(AssignBinRequest $request, Bin $bin): JsonResponse
    {
        // End any current assignment first
        $bin->currentAssignment?->update(['unassigned_at' => now()]);

        // Create new assignment
        BinAssignment::create([
            'bin_id' => $bin->id,
            'outlet_id' => $request->validated('outlet_id'),
            'assigned_at' => now(),
            'unassigned_at' => null,
        ]);

        $bin->load('currentAssignment.outlet');

        return BinResource::make($bin)
            ->additional(['message' => 'Bin assigned to outlet successfully.'])
            ->response();
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
                'name' => $bin->name,
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
            'compartments' => ['nullable', 'array'],
            'ip_address' => ['nullable', 'string', 'max:45'],
        ]);

        $bin->update([
            'fill_level' => $validated['fill_level'],
            'compartments' => $validated['compartments'] ?? $bin->compartments,
            'ip_address' => $validated['ip_address'] ?? $request->ip(),
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'data' => [
                'id' => $bin->id,
                'fill_level' => $bin->fill_level,
                'last_seen_at' => $bin->last_seen_at->toIso8601String(),
            ],
            'message' => 'Heartbeat received.',
        ]);
    }

    public function unassign(Bin $bin): JsonResponse
    {
        if (! $bin->currentAssignment) {
            return response()->json([
                'data' => null,
                'message' => 'Bin is not currently assigned to any outlet.',
            ], 422);
        }

        $bin->currentAssignment->update(['unassigned_at' => now()]);

        return BinResource::make($bin->fresh())
            ->additional(['message' => 'Bin unassigned from outlet successfully.'])
            ->response();
    }
}
