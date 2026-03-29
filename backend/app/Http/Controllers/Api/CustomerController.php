<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bin;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CustomerController extends Controller
{
    /**
     * Get the authenticated user's recycling stats.
     */
    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'points_balance' => $user->points_balance,
            'current_streak' => $user->current_streak,
            'longest_streak' => $user->longest_streak,
            'total_detections' => $user->detectionEvents()->count(),
            'co2_saved_grams' => $user->detectionEvents()->count() * 25,
        ]);
    }

    /**
     * Get the authenticated user's recycling history (paginated).
     */
    public function history(Request $request): JsonResponse
    {
        $detections = $request->user()
            ->detectionEvents()
            ->with(['bin:id,serial_number', 'detectedBrand:id,name,slug', 'bin.currentAssignment.outlet.brand:id,name'])
            ->latest('detected_at')
            ->paginate(15)
            ->through(function ($event) {
                $binBrand = $event->bin?->currentAssignment?->outlet?->brand;
                $brandMatch = null;
                if ($event->waste_type?->isCup() && $event->detectedBrand) {
                    $brandMatch = ($binBrand && $binBrand->id === $event->detected_brand_id) ? 'match' : 'competitor';
                }

                return [
                    'id' => $event->id,
                    'waste_type' => $event->waste_type?->value,
                    'waste_type_label' => $event->waste_type?->label(),
                    'confidence' => $event->confidence,
                    'bin_serial' => $event->bin?->serial_number,
                    'detected_brand_name' => $event->detectedBrand?->name,
                    'brand_match' => $brandMatch,
                    'detected_at' => $event->detected_at?->toIso8601String(),
                    'points_earned' => $this->pointsForWasteType($event->waste_type),
                ];
            });

        return response()->json($detections);
    }

    /**
     * Get the top 10 users by points_balance.
     */
    public function leaderboard(): JsonResponse
    {
        $leaders = User::query()
            ->where('points_balance', '>', 0)
            ->orderByDesc('points_balance')
            ->limit(10)
            ->get(['id', 'name', 'points_balance', 'current_streak', 'longest_streak'])
            ->map(fn (User $user, int $index) => [
                'rank' => $index + 1,
                'name' => $user->name,
                'points' => $user->points_balance,
                'current_streak' => $user->current_streak,
                'longest_streak' => $user->longest_streak,
            ]);

        return response()->json(['data' => $leaders]);
    }

    /**
     * Start a session at a bin. The user scanned the bin's QR code.
     * For the next 60 seconds, any detection on this bin is attributed to this user.
     */
    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bin_serial' => ['required', 'string'],
        ]);

        $bin = Bin::where('serial_number', $validated['bin_serial'])->first();

        if (! $bin) {
            return response()->json([
                'data' => null,
                'message' => 'Bin not found.',
            ], 404);
        }

        $user = $request->user();
        $ttlSeconds = 60;

        Cache::put("bin_session:{$bin->id}", $user->id, now()->addSeconds($ttlSeconds));

        $bin->load('currentAssignment.outlet');
        $outlet = $bin->currentAssignment?->outlet;

        return response()->json([
            'data' => [
                'bin_id' => $bin->id,
                'bin_serial' => $bin->serial_number,
                'outlet_name' => $outlet?->name,
                'session_expires_in' => $ttlSeconds,
            ],
            'message' => 'Session started. Deposit your item now!',
        ]);
    }

    private function pointsForWasteType(?\App\Enums\WasteType $wasteType): int
    {
        if (! $wasteType) {
            return 0;
        }

        return match ($wasteType) {
            \App\Enums\WasteType::PaperCup, \App\Enums\WasteType::PlasticCup => 10,
            \App\Enums\WasteType::Lid => 5,
            \App\Enums\WasteType::Straw, \App\Enums\WasteType::Napkin => 3,
            \App\Enums\WasteType::LiquidWaste => 2,
        };
    }
}
