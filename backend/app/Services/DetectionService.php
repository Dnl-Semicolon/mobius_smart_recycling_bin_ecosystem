<?php

namespace App\Services;

use App\Enums\WasteType;
use App\Models\Bin;
use App\Models\Brand;
use App\Models\DetectionEvent;
use App\Models\PickupRequest;
use App\Models\RecyclingTransaction;
use App\Models\User;
use App\Notifications\BinReadyForPickup;
use Illuminate\Support\Facades\Notification;

class DetectionService
{
    /**
     * Base points per waste type.
     * Reflects environmental impact, processing value, and behavioral incentive.
     */
    public const POINTS_PER_TYPE = [
        'paper_cup' => 15,
        'plastic_cup' => 12,
        'lid' => 5,
        'straw' => 3,
        'napkin' => 2,
        'liquid_waste' => 8,
    ];

    public function __construct(private NotificationService $notificationService) {}

    /**
     * Process a detection event: update bin fill level, create pickup if needed, award points.
     * Called by the observer on create (when waste_type is set) and by the classify endpoint.
     */
    public function processDetection(DetectionEvent $event): void
    {
        if ($event->waste_type === null) {
            return;
        }

        $this->updateBinFillLevel($event);

        if ($event->user_id) {
            $this->awardPoints($event);
        }
    }

    /**
     * Calculate points for a waste type at a specific bin.
     *
     * Option B "Hard Deterrent" logic for cups:
     * - Cup matches bin brand → full brand multiplier
     * - Cup brand not detected → base points only
     * - Competitor cup at branded bin → brand_multiplier × 0.3 (below base = deterrent)
     * - Any cup at unbranded bin → base points only
     * - Non-cup items → normal brand multiplier (brand irrelevant)
     */
    public static function calculatePoints(WasteType $wasteType, ?Bin $bin = null, ?Brand $detectedBrand = null): int
    {
        $base = self::POINTS_PER_TYPE[$wasteType->value] ?? 0;

        // Resolve the bin's brand through outlet assignment
        $binBrand = null;
        $brandMultiplier = 1.0;
        if ($bin) {
            $outlet = $bin->currentAssignment?->outlet;
            if ($outlet?->brand?->active && $outlet->brand->points_multiplier > 0) {
                $binBrand = $outlet->brand;
                $brandMultiplier = (float) $binBrand->points_multiplier;
            }
        }

        // Non-cup items: brand irrelevant, apply bin multiplier as normal
        if (! $wasteType->isCup()) {
            return (int) round($base * $brandMultiplier);
        }

        // Cup items: Option B hard deterrent logic
        if (! $binBrand) {
            return $base; // Unbranded bin — base points only
        }

        if (! $detectedBrand) {
            return $base; // Cup brand not detected — base points only
        }

        if ($detectedBrand->id === $binBrand->id) {
            return (int) round($base * $brandMultiplier); // Brand match — full reward
        }

        // Competitor cup at branded bin — hard deterrent (e.g. 1.5 × 0.3 = 0.45x)
        return (int) round($base * $brandMultiplier * 0.3);
    }

    private function updateBinFillLevel(DetectionEvent $event): void
    {
        $increment = match ($event->waste_type) {
            WasteType::PaperCup, WasteType::PlasticCup => 5,
            WasteType::LiquidWaste => 3,
            WasteType::Lid => 2,
            WasteType::Straw, WasteType::Napkin => 1,
        };

        $bin = $event->bin;
        $previousLevel = $bin->fill_level;
        $bin->fill_level = min(100, $bin->fill_level + $increment);
        $bin->save();

        if ($bin->fill_level >= 80 && $previousLevel < 80) {
            $this->createPickupRequest($bin);
        }
    }

    private function awardPoints(DetectionEvent $event): void
    {
        $event->loadMissing('detectedBrand');
        $points = self::calculatePoints($event->waste_type, $event->bin, $event->detectedBrand);

        $basePoints = self::POINTS_PER_TYPE[$event->waste_type->value] ?? 0;
        $description = "Recycled {$event->waste_type->value}";
        if ($points > $basePoints) {
            $description .= ' (brand bonus)';
        } elseif ($event->waste_type->isCup() && $event->detectedBrand && $points < $basePoints) {
            $description .= ' (competitor penalty)';
        }

        RecyclingTransaction::create([
            'user_id' => $event->user_id,
            'detection_event_id' => $event->id,
            'points_earned' => $points,
            'type' => 'earned',
            'description' => $description,
        ]);

        $user = $event->user;
        $user->increment('points_balance', $points);

        $now = now();
        if ($user->last_recycled_at && $user->last_recycled_at->isYesterday()) {
            $user->increment('current_streak');
            if ($user->current_streak > $user->longest_streak) {
                $user->update(['longest_streak' => $user->current_streak]);
            }
        } elseif (! $user->last_recycled_at || ! $user->last_recycled_at->isToday()) {
            $user->update(['current_streak' => 1]);
            if ($user->longest_streak === 0) {
                $user->update(['longest_streak' => 1]);
            }
        }

        $user->update(['last_recycled_at' => $now]);

        // Fire notifications
        $this->notificationService->notifyPointsEarned($user, $points, $event->waste_type);
        $this->notificationService->notifyStreakMilestone($user, $user->fresh()->current_streak);
    }

    private function createPickupRequest(Bin $bin): void
    {
        if ($bin->activePickupRequest()->exists()) {
            return;
        }

        $pickupRequest = PickupRequest::create([
            'bin_id' => $bin->id,
        ]);

        $collectors = User::whereJsonContains('roles', 'collector')->get();

        if ($collectors->isNotEmpty()) {
            Notification::send($collectors, new BinReadyForPickup($pickupRequest));
        }
    }
}
