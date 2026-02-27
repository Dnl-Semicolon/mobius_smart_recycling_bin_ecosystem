<?php

namespace Database\Seeders;

use App\Enums\PickupStatus;
use App\Models\Bin;
use App\Models\PickupRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class PickupRequestSeeder extends Seeder
{
    public function run(): void
    {
        $collector = User::where('email', 'collector@mobius.test')->first();

        // Bins with fill >= 80 should have a pickup request
        $fullBins = Bin::where('fill_level', '>=', 80)->get();

        foreach ($fullBins as $index => $bin) {
            if ($index === 0 && $collector) {
                // First full bin: claimed by the collector (demo the claimed state)
                PickupRequest::create([
                    'bin_id' => $bin->id,
                    'status' => PickupStatus::Claimed,
                    'claimed_by' => $collector->id,
                    'claimed_at' => now()->subMinutes(30),
                ]);
            } else {
                // Other full bins: pending (available for claim)
                PickupRequest::create([
                    'bin_id' => $bin->id,
                    'status' => PickupStatus::Pending,
                ]);
            }
        }

        // Create a couple of completed pickup requests for history
        if ($collector) {
            $historyBins = Bin::where('fill_level', '<', 50)->limit(2)->get();
            foreach ($historyBins as $bin) {
                PickupRequest::create([
                    'bin_id' => $bin->id,
                    'status' => PickupStatus::Completed,
                    'claimed_by' => $collector->id,
                    'claimed_at' => now()->subDays(rand(1, 7)),
                    'completed_at' => now()->subDays(rand(0, 1))->subHours(rand(1, 12)),
                ]);
            }
        }
    }
}
