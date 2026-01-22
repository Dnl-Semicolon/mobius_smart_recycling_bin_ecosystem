<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBinRequest;
use App\Http\Requests\UpdateBinRequest;
use App\Http\Resources\BinResource;
use App\Models\Bin;
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
}
