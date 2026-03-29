<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;

class ZoneSeeder extends Seeder
{
    /**
     * Seed Penang collection zones with real coordinates.
     */
    public function run(): void
    {
        $jelutongDepot = [
            'depot_latitude' => 5.3784,
            'depot_longitude' => 100.3354,
            'depot_name' => 'Mobius Recycling Hub Jelutong',
        ];

        $bayanLepasDepot = [
            'depot_latitude' => 5.3050,
            'depot_longitude' => 100.2790,
            'depot_name' => 'Mobius Recycling Hub Bayan Lepas',
        ];

        $zones = [
            [
                'name' => 'George Town North',
                'slug' => 'george-town-north',
                'region' => 'Penang Island',
                'boundary' => [
                    [5.4700, 100.2780], // Tanjung Bungah northwest
                    [5.4700, 100.3350], // Northeast towards Komtar
                    [5.4100, 100.3350], // Komtar south edge
                    [5.4100, 100.2780], // Southwest back
                ],
                'min_bins_for_dispatch' => 3,
                ...$jelutongDepot,
            ],
            [
                'name' => 'George Town Heritage',
                'slug' => 'george-town-heritage',
                'region' => 'Penang Island',
                'boundary' => [
                    [5.4200, 100.3300], // North (near Komtar)
                    [5.4200, 100.3500], // Northeast (waterfront)
                    [5.4050, 100.3500], // Southeast
                    [5.4050, 100.3300], // Southwest
                ],
                'min_bins_for_dispatch' => 1, // Only 1 bin (Tealive Komtar) in this zone
                ...$jelutongDepot,
            ],
            [
                'name' => 'Tanjung Bungah Corridor',
                'slug' => 'tanjung-bungah-corridor',
                'region' => 'Penang Island',
                'boundary' => [
                    [5.4750, 100.2500], // Teluk Bahang
                    [5.4750, 100.2900], // Towards Tanjung Bungah
                    [5.4550, 100.2900], // Tanjung Bungah south
                    [5.4550, 100.2500], // Back west
                ],
                'min_bins_for_dispatch' => 2,
                ...$jelutongDepot,
            ],
            [
                'name' => 'Jelutong–Gelugor',
                'slug' => 'jelutong-gelugor',
                'region' => 'Penang Island',
                'boundary' => [
                    [5.3950, 100.3100], // North
                    [5.3950, 100.3500], // Northeast
                    [5.3600, 100.3500], // Southeast (USM)
                    [5.3600, 100.3100], // Southwest
                ],
                'min_bins_for_dispatch' => 3,
                ...$jelutongDepot,
            ],
            [
                'name' => 'Bayan Lepas',
                'slug' => 'bayan-lepas',
                'region' => 'Penang Island',
                'boundary' => [
                    [5.3400, 100.2600], // North (Bukit Jambul)
                    [5.3400, 100.3100], // Northeast
                    [5.2800, 100.3100], // Southeast (FTZ)
                    [5.2800, 100.2600], // Southwest
                ],
                'min_bins_for_dispatch' => 3,
                ...$bayanLepasDepot,
            ],
            [
                'name' => 'Balik Pulau',
                'slug' => 'balik-pulau',
                'region' => 'Penang Island',
                'boundary' => [
                    [5.3700, 100.2100], // North
                    [5.3700, 100.2500], // Northeast
                    [5.3200, 100.2500], // Southeast
                    [5.3200, 100.2100], // Southwest
                ],
                'min_bins_for_dispatch' => 2,
                ...$jelutongDepot,
            ],
        ];

        foreach ($zones as $zoneData) {
            Zone::create(array_merge($zoneData, ['is_active' => true]));
        }

        // Assign the default collector to George Town North
        $collector = User::where('email', 'collector@mobius.test')->first();
        if ($collector) {
            $georgeNorth = Zone::where('slug', 'george-town-north')->first();
            $heritage = Zone::where('slug', 'george-town-heritage')->first();

            if ($georgeNorth) {
                $georgeNorth->collectors()->attach($collector->id, ['is_primary' => true]);
            }
            if ($heritage) {
                $heritage->collectors()->attach($collector->id, ['is_primary' => true]);
            }
        }

        // Also assign Daniel (multi-role) to a zone
        $daniel = User::where('email', 'daniel@mobius.test')->first();
        if ($daniel) {
            $georgeNorth = Zone::where('slug', 'george-town-north')->first();
            if ($georgeNorth) {
                $georgeNorth->collectors()->attach($daniel->id, ['is_primary' => false]);
            }
        }
    }
}
