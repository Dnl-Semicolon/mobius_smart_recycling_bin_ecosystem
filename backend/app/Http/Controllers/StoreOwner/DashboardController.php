<?php

namespace App\Http\Controllers\StoreOwner;

use App\Enums\WasteType;
use App\Http\Controllers\Controller;
use App\Models\DetectionEvent;
use App\Models\Redemption;
use App\Services\StoreOwnerContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private StoreOwnerContext $context) {}

    public function index(Request $request): View
    {
        $ctx = $this->context->resolve(
            $request->user(),
            $request->integer('outlet') ?: null,
        );

        $brand = $ctx->brand;
        $binIds = $ctx->binIds;
        $outlets = $ctx->outlets;
        $isHQ = $ctx->isHQ;
        $selectedOutlet = $ctx->selectedOutlet;

        // Backward compat: view expects $outlet for header display
        $outlet = $selectedOutlet ?? $outlets->first();

        // Stats across scoped bins
        $todayDetections = DetectionEvent::whereIn('bin_id', $binIds)
            ->whereDate('detected_at', today())
            ->count();

        $weekDetections = DetectionEvent::whereIn('bin_id', $binIds)
            ->where('detected_at', '>=', now()->startOfWeek())
            ->count();

        $monthDetections = DetectionEvent::whereIn('bin_id', $binIds)
            ->where('detected_at', '>=', now()->startOfMonth())
            ->count();

        // Waste breakdown this month
        $wasteBreakdown = DetectionEvent::whereIn('bin_id', $binIds)
            ->where('detected_at', '>=', now()->startOfMonth())
            ->whereNotNull('waste_type')
            ->selectRaw('waste_type, count(*) as total')
            ->groupBy('waste_type')
            ->pluck('total', 'waste_type')
            ->toArray();

        // Unique recyclers this month
        $uniqueRecyclers = DetectionEvent::whereIn('bin_id', $binIds)
            ->where('detected_at', '>=', now()->startOfMonth())
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        // Brand loyalty stats (cups only)
        $brandMatchCups = 0;
        $competitorCups = 0;
        $unidentifiedCups = 0;
        if ($brand) {
            $cupTypes = [WasteType::PaperCup->value, WasteType::PlasticCup->value];
            $cupsQuery = DetectionEvent::whereIn('bin_id', $binIds)
                ->where('detected_at', '>=', now()->startOfMonth())
                ->whereIn('waste_type', $cupTypes);

            $brandMatchCups = (clone $cupsQuery)->where('detected_brand_id', $brand->id)->count();
            $competitorCups = (clone $cupsQuery)->whereNotNull('detected_brand_id')->where('detected_brand_id', '!=', $brand->id)->count();
            $unidentifiedCups = (clone $cupsQuery)->whereNull('detected_brand_id')->count();
        }

        // Reward redemptions for this brand
        $redemptionCount = 0;
        $activeRewards = 0;
        if ($brand) {
            $rewardIds = $brand->rewards()->pluck('id');
            $redemptionCount = Redemption::whereIn('reward_id', $rewardIds)
                ->where('redeemed_at', '>=', now()->startOfMonth())
                ->count();
            $activeRewards = $brand->rewards()->where('active', true)->count();
        }

        return view('store-owner.dashboard', compact(
            'outlet',
            'outlets',
            'selectedOutlet',
            'isHQ',
            'brand',
            'todayDetections',
            'weekDetections',
            'monthDetections',
            'wasteBreakdown',
            'uniqueRecyclers',
            'brandMatchCups',
            'competitorCups',
            'unidentifiedCups',
            'redemptionCount',
            'activeRewards',
        ));
    }

    public function analytics(Request $request): View
    {
        $ctx = $this->context->resolve(
            $request->user(),
            $request->integer('outlet') ?: null,
        );

        $binIds = $ctx->binIds;
        $outlets = $ctx->outlets;
        $isHQ = $ctx->isHQ;
        $selectedOutlet = $ctx->selectedOutlet;

        // Backward compat: view expects $outlet
        $outlet = $selectedOutlet ?? $outlets->first();

        // Daily detections for last 14 days
        $dailyDetections = DetectionEvent::whereIn('bin_id', $binIds)
            ->where('detected_at', '>=', now()->subDays(14))
            ->selectRaw('DATE(detected_at) as date, count(*) as total')
            ->groupByRaw('DATE(detected_at)')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        // Waste type breakdown
        $wasteBreakdown = DetectionEvent::whereIn('bin_id', $binIds)
            ->where('detected_at', '>=', now()->subDays(30))
            ->whereNotNull('waste_type')
            ->selectRaw('waste_type, count(*) as total')
            ->groupBy('waste_type')
            ->pluck('total', 'waste_type')
            ->toArray();

        // Hourly distribution (peak hours)
        $hourlyDistribution = DetectionEvent::whereIn('bin_id', $binIds)
            ->where('detected_at', '>=', now()->subDays(30))
            ->selectRaw("strftime('%H', detected_at) as hour, count(*) as total")
            ->groupByRaw("strftime('%H', detected_at)")
            ->orderBy('hour')
            ->pluck('total', 'hour')
            ->toArray();

        return view('store-owner.analytics', compact(
            'outlet',
            'outlets',
            'selectedOutlet',
            'isHQ',
            'dailyDetections',
            'wasteBreakdown',
            'hourlyDistribution',
        ));
    }
}
