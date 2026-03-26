<?php

namespace App\Http\Controllers\Api\StoreOwner;

use App\Http\Controllers\Controller;
use App\Models\DetectionEvent;
use App\Models\Outlet;
use App\Services\StoreOwnerContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OutletController extends Controller
{
    public function __construct(private StoreOwnerContext $context) {}

    public function index(Request $request): JsonResponse
    {
        $ctx = $this->context->resolve($request->user());

        $data = $ctx->outlets->map(function (Outlet $outlet) {
            $outletBinIds = $outlet->bins->pluck('id')->all();
            $todayDetections = count($outletBinIds) > 0
                ? DetectionEvent::whereIn('bin_id', $outletBinIds)
                    ->whereDate('detected_at', today())
                    ->count()
                : 0;

            return [
                'id' => $outlet->id,
                'name' => $outlet->name,
                'address' => $outlet->address,
                'latitude' => $outlet->latitude,
                'longitude' => $outlet->longitude,
                'bin_count' => $outlet->bins->count(),
                'today_detections' => $todayDetections,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function show(Request $request, Outlet $outlet): JsonResponse
    {
        $ctx = $this->context->resolve($request->user());

        $inScope = $ctx->outlets->firstWhere('id', $outlet->id);
        abort_unless($inScope, 403, 'Outlet not in your scope.');

        $outlet->load('bins');

        $bins = $outlet->bins->map(fn ($bin) => [
            'id' => $bin->id,
            'serial_number' => $bin->serial_number,
            'fill_level' => $bin->fill_level,
            'status' => $bin->status->value,
            'last_seen_at' => $bin->last_seen_at?->toIso8601String(),
        ]);

        return response()->json([
            'data' => [
                'id' => $outlet->id,
                'name' => $outlet->name,
                'address' => $outlet->address,
                'latitude' => $outlet->latitude,
                'longitude' => $outlet->longitude,
                'bins' => $bins,
            ],
        ]);
    }

    public function bins(Request $request): JsonResponse
    {
        $ctx = $this->context->resolve($request->user());

        $data = $ctx->outlets->flatMap(function (Outlet $outlet) {
            return $outlet->bins->map(fn ($bin) => [
                'id' => $bin->id,
                'serial_number' => $bin->serial_number,
                'fill_level' => $bin->fill_level,
                'status' => $bin->status->value,
                'last_seen_at' => $bin->last_seen_at?->toIso8601String(),
                'outlet_name' => $outlet->name,
            ]);
        });

        return response()->json(['data' => $data->values()]);
    }
}
