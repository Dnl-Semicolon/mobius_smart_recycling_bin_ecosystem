<?php

namespace App\Http\Controllers\Api\StoreOwner;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Services\StoreOwnerContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RewardController extends Controller
{
    public function __construct(private StoreOwnerContext $context) {}

    public function index(Request $request): JsonResponse
    {
        $ctx = $this->context->resolve($request->user());

        $rewards = $ctx->brand->rewards()
            ->withCount('redemptions')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Reward $reward) => [
                'id' => $reward->id,
                'name' => $reward->name,
                'description' => $reward->description,
                'points_cost' => $reward->points_cost,
                'stock' => $reward->stock,
                'active' => $reward->active,
                'sort_order' => $reward->sort_order,
                'expires_at' => $reward->expires_at?->toIso8601String(),
                'redemptions_count' => $reward->redemptions_count,
            ]);

        return response()->json(['data' => $rewards]);
    }

    public function store(Request $request): JsonResponse
    {
        $ctx = $this->context->resolve($request->user());
        abort_unless($ctx->isHQ, 403, 'Only HQ can manage rewards.');

        $validated = $request->validate([
            'name' => ['required', 'string'],
            'points_cost' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $reward = $ctx->brand->rewards()->create($validated);

        return response()->json([
            'data' => [
                'id' => $reward->id,
                'name' => $reward->name,
                'description' => $reward->description,
                'points_cost' => $reward->points_cost,
                'stock' => $reward->stock,
                'active' => $reward->active,
                'sort_order' => $reward->sort_order,
                'expires_at' => $reward->expires_at?->toIso8601String(),
            ],
        ], 201);
    }

    public function update(Request $request, Reward $reward): JsonResponse
    {
        $ctx = $this->context->resolve($request->user());
        abort_unless($ctx->isHQ, 403, 'Only HQ can manage rewards.');
        abort_unless($reward->brand_id === $ctx->brand->id, 403, 'Reward does not belong to your brand.');

        $validated = $request->validate([
            'name' => ['required', 'string'],
            'points_cost' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'active' => ['boolean'],
            'expires_at' => ['nullable', 'date'],
        ]);

        $reward->update($validated);

        return response()->json([
            'data' => [
                'id' => $reward->id,
                'name' => $reward->name,
                'description' => $reward->description,
                'points_cost' => $reward->points_cost,
                'stock' => $reward->stock,
                'active' => $reward->active,
                'sort_order' => $reward->sort_order,
                'expires_at' => $reward->expires_at?->toIso8601String(),
            ],
        ]);
    }

    public function destroy(Request $request, Reward $reward): JsonResponse
    {
        $ctx = $this->context->resolve($request->user());
        abort_unless($ctx->isHQ, 403, 'Only HQ can manage rewards.');
        abort_unless($reward->brand_id === $ctx->brand->id, 403, 'Reward does not belong to your brand.');

        $reward->delete();

        return response()->json(['message' => 'Reward deleted.']);
    }
}
