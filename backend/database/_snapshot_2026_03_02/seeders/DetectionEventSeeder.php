<?php

namespace Database\Seeders;

use App\Enums\BinStatus;
use App\Enums\WasteType;
use App\Models\Bin;
use App\Models\DetectionEvent;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DetectionEventSeeder extends Seeder
{
    public function run(): void
    {
        $activeBins = Bin::query()
            ->where('status', BinStatus::Active)
            ->whereHas('currentAssignment')
            ->get();

        foreach ($activeBins as $bin) {
            $this->seedEventsForBin($bin);
        }
    }

    private function seedEventsForBin(Bin $bin): void
    {
        $eventCount = (int) ($bin->fill_level * 1.5) + rand(5, 15);

        $wasteTypes = [
            WasteType::PaperCup->value => 35,
            WasteType::PlasticCup->value => 30,
            WasteType::Lid->value => 15,
            WasteType::Straw->value => 10,
            WasteType::Napkin->value => 5,
            WasteType::LiquidWaste->value => 5,
        ];

        for ($i = 0; $i < $eventCount; $i++) {
            DetectionEvent::create([
                'bin_id' => $bin->id,
                'waste_type' => $this->weightedRandom($wasteTypes),
                'confidence' => rand(70, 99),
                'image_path' => null,
                'detected_at' => $this->randomRecentTimestamp(),
            ]);
        }
    }

    private function weightedRandom(array $weights): string
    {
        $total = array_sum($weights);
        $rand = rand(1, $total);
        $current = 0;

        foreach ($weights as $value => $weight) {
            $current += $weight;
            if ($rand <= $current) {
                return $value;
            }
        }

        return array_key_first($weights);
    }

    private function randomRecentTimestamp(): Carbon
    {
        return now()
            ->subDays(rand(0, 6))
            ->subHours(rand(0, 23))
            ->subMinutes(rand(0, 59));
    }
}
