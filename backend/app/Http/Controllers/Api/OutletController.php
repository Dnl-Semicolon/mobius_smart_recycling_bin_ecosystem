<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOutletRequest;
use App\Http\Requests\UpdateOutletRequest;
use App\Http\Resources\OutletResource;
use App\Models\Outlet;
use Illuminate\Http\JsonResponse;

class OutletController extends Controller
{
    public function index(): JsonResponse
    {
        $outlets = Outlet::query()
            ->withCount(['currentBinAssignments as current_bins_count'])
            ->latest()
            ->paginate();

        return OutletResource::collection($outlets)
            ->additional(['message' => 'Outlets retrieved successfully.'])
            ->response();
    }

    public function store(StoreOutletRequest $request): JsonResponse
    {
        $outlet = Outlet::create($request->validated());

        return OutletResource::make($outlet)
            ->additional(['message' => 'Outlet created successfully.'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Outlet $outlet): JsonResponse
    {
        $outlet->loadCount(['currentBinAssignments as current_bins_count']);
        $outlet->load(['bins']);

        return OutletResource::make($outlet)
            ->additional(['message' => 'Outlet retrieved successfully.'])
            ->response();
    }

    public function update(UpdateOutletRequest $request, Outlet $outlet): JsonResponse
    {
        $outlet->update($request->validated());

        return OutletResource::make($outlet)
            ->additional(['message' => 'Outlet updated successfully.'])
            ->response();
    }

    public function destroy(Outlet $outlet): JsonResponse
    {
        $outlet->delete();

        return response()->json([
            'data' => null,
            'message' => 'Outlet deleted successfully.',
        ]);
    }
}
