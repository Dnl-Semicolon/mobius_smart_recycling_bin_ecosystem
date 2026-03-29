<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Redemption;
use App\Models\Reward;
use App\Services\RewardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RewardController extends Controller
{
    public function __construct(private RewardService $rewardService) {}

    /**
     * List available rewards, optionally filtered by brand.
     */
    public function index(Request $request): JsonResponse
    {
        $rewards = Reward::query()
            ->available()
            ->with('brand:id,name,slug,primary_color,logo_path')
            ->orderBy('sort_order')
            ->orderBy('points_cost')
            ->get()
            ->map(fn (Reward $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'description' => $r->description,
                'points_cost' => $r->points_cost,
                'stock' => $r->stock,
                'image_path' => $r->image_path,
                'expires_at' => $r->expires_at?->toIso8601String(),
                'brand' => $r->brand ? [
                    'id' => $r->brand->id,
                    'name' => $r->brand->name,
                    'slug' => $r->brand->slug,
                    'color' => $r->brand->primary_color,
                ] : null,
            ]);

        return response()->json(['data' => $rewards]);
    }

    /**
     * Redeem a reward.
     */
    public function redeem(Request $request, Reward $reward): JsonResponse
    {
        $result = $this->rewardService->redeem($request->user(), $reward);

        if (! $result['success']) {
            return response()->json(['message' => $result['error']], 422);
        }

        $redemption = $result['redemption'];

        return response()->json([
            'message' => 'Reward redeemed successfully!',
            'data' => [
                'voucher_code' => $redemption->voucher_code,
                'reward_name' => $reward->name,
                'points_spent' => $redemption->points_spent,
                'expires_at' => $redemption->expires_at?->toIso8601String(),
                'new_balance' => $request->user()->fresh()->points_balance,
            ],
        ], 201);
    }

    /**
     * User's redemption history.
     */
    public function myRedemptions(Request $request): JsonResponse
    {
        $redemptions = Redemption::where('user_id', $request->user()->id)
            ->with('reward:id,name,brand_id', 'reward.brand:id,name,slug')
            ->latest('redeemed_at')
            ->paginate(20);

        return response()->json($redemptions);
    }
}
