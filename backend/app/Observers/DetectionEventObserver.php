<?php

namespace App\Observers;

use App\Enums\UserRole;
use App\Enums\WasteType;
use App\Models\DetectionEvent;
use App\Models\PickupRequest;
use App\Models\User;
use App\Notifications\BinReadyForPickup;
use Illuminate\Support\Facades\Notification;

class DetectionEventObserver
{
    /**
     * When a detection event is created with a waste_type, bump the
     * bin's fill_level by an amount based on the waste size.
     *
     * Cups are bulky (+5), liquid has volume (+3), lids are small (+2),
     * straws and napkins are tiny (+1). Capped at 100.
     *
     * If the bin crosses ≥80% fill and has no active pickup request,
     * auto-create one and notify all collectors.
     */
    public function created(DetectionEvent $detectionEvent): void
    {
        if ($detectionEvent->waste_type === null) {
            return;
        }

        $increment = match ($detectionEvent->waste_type) {
            WasteType::PaperCup, WasteType::PlasticCup => 5,
            WasteType::LiquidWaste => 3,
            WasteType::Lid => 2,
            WasteType::Straw, WasteType::Napkin => 1,
        };

        $bin = $detectionEvent->bin;
        $previousLevel = $bin->fill_level;
        $bin->fill_level = min(100, $bin->fill_level + $increment);
        $bin->save();

        // Auto-create pickup request when bin crosses the 80% threshold
        if ($bin->fill_level >= 80 && $previousLevel < 80) {
            $this->createPickupRequest($bin);
        }
    }

    private function createPickupRequest(\App\Models\Bin $bin): void
    {
        // Only create if no active pickup request exists for this bin
        if ($bin->activePickupRequest()->exists()) {
            return;
        }

        $pickupRequest = PickupRequest::create([
            'bin_id' => $bin->id,
        ]);

        // Notify all collectors
        $collectors = User::where('role', UserRole::Collector)->get();

        if ($collectors->isNotEmpty()) {
            Notification::send($collectors, new BinReadyForPickup($pickupRequest));
        }
    }
}
