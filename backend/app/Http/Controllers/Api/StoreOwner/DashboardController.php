<?php

namespace App\Http\Controllers\Api\StoreOwner;

use App\Enums\WasteType;
use App\Http\Controllers\Controller;
use App\Models\DetectionEvent;
use App\Models\Redemption;
use App\Services\StoreOwnerContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private StoreOwnerContext $context) {}

    public function index(Request $request): JsonResponse
    {
        $ctx = $this->context->resolve(
            $request->user(),
            $request->integer('outlet') ?: null,
        );

        $brand = $ctx->brand;
        $binIds = $ctx->binIds;

        // Detection counts
        $todayDetections = DetectionEvent::whereIn('bin_id', $binIds)
            ->whereDate('detected_at', today())
            ->count();

        $weekDetections = DetectionEvent::whereIn('bin_id', $binIds)
            ->where('detected_at', '>=', now()->startOfWeek())
            ->count();

        $monthDetections = DetectionEvent::whereIn('bin_id', $binIds)
            ->where('detected_at', '>=', now()->startOfMonth())
            ->count();

        // Unique recyclers this month
        $uniqueRecyclers = DetectionEvent::whereIn('bin_id', $binIds)
            ->where('detected_at', '>=', now()->startOfMonth())
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        // Waste breakdown this month
        $wasteBreakdown = DetectionEvent::whereIn('bin_id', $binIds)
            ->where('detected_at', '>=', now()->startOfMonth())
            ->whereNotNull('waste_type')
            ->selectRaw('waste_type, count(*) as total')
            ->groupBy('waste_type')
            ->pluck('total', 'waste_type')
            ->toArray();

        // Brand loyalty stats (cups only)
        $brandMatchCups = 0;
        $competitorCups = 0;
        $unidentifiedCups = 0;

        $cupTypes = [WasteType::PaperCup->value, WasteType::PlasticCup->value];
        $cupsQuery = DetectionEvent::whereIn('bin_id', $binIds)
            ->where('detected_at', '>=', now()->startOfMonth())
            ->whereIn('waste_type', $cupTypes);

        $brandMatchCups = (clone $cupsQuery)->where('detected_brand_id', $brand->id)->count();
        $competitorCups = (clone $cupsQuery)->whereNotNull('detected_brand_id')->where('detected_brand_id', '!=', $brand->id)->count();
        $unidentifiedCups = (clone $cupsQuery)->whereNull('detected_brand_id')->count();

        // Reward stats
        $rewardIds = $brand->rewards()->pluck('id');
        $redemptionCount = Redemption::whereIn('reward_id', $rewardIds)
            ->where('redeemed_at', '>=', now()->startOfMonth())
            ->count();
        $activeRewards = $brand->rewards()->where('active', true)->count();

        return response()->json([
            'data' => [
                'today_detections' => $todayDetections,
                'week_detections' => $weekDetections,
                'month_detections' => $monthDetections,
                'unique_recyclers' => $uniqueRecyclers,
                'redemption_count' => $redemptionCount,
                'active_rewards' => $activeRewards,
                'brand_loyalty' => [
                    'match' => $brandMatchCups,
                    'competitor' => $competitorCups,
                    'unidentified' => $unidentifiedCups,
                ],
                'waste_breakdown' => $wasteBreakdown,
            ],
        ]);
    }

    public function brand(Request $request): JsonResponse
    {
        $ctx = $this->context->resolve($request->user());

        $brand = $ctx->brand;

        return response()->json([
            'data' => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'logo_path' => $brand->logo_path,
                'primary_color' => $brand->primary_color,
                'points_multiplier' => $brand->points_multiplier,
                'rewards_budget' => $brand->rewards_budget,
            ],
        ]);
    }

    public function analytics(Request $request): JsonResponse
    {
        $ctx = $this->context->resolve(
            $request->user(),
            $request->integer('outlet') ?: null,
        );

        $binIds = $ctx->binIds;

        // Daily detections for last 14 days
        $dailyDetections = DetectionEvent::whereIn('bin_id', $binIds)
            ->where('detected_at', '>=', now()->subDays(14))
            ->selectRaw('DATE(detected_at) as date, count(*) as total')
            ->groupByRaw('DATE(detected_at)')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        // Waste type breakdown (30 days)
        $wasteBreakdown = DetectionEvent::whereIn('bin_id', $binIds)
            ->where('detected_at', '>=', now()->subDays(30))
            ->whereNotNull('waste_type')
            ->selectRaw('waste_type, count(*) as total')
            ->groupBy('waste_type')
            ->pluck('total', 'waste_type')
            ->toArray();

        // Hourly distribution (30 days)
        $hourlyDistribution = DetectionEvent::whereIn('bin_id', $binIds)
            ->where('detected_at', '>=', now()->subDays(30))
            ->selectRaw("strftime('%H', detected_at) as hour, count(*) as total")
            ->groupByRaw("strftime('%H', detected_at)")
            ->orderBy('hour')
            ->pluck('total', 'hour')
            ->toArray();

        return response()->json([
            'data' => [
                'daily_detections' => $dailyDetections,
                'waste_breakdown' => $wasteBreakdown,
                'hourly_distribution' => $hourlyDistribution,
            ],
        ]);
    }
}
