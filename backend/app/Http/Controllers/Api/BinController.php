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
