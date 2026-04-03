<?php

namespace App\Services;

use App\Models\BinSession;

class PointsCalculatorService
{
    private const BASE_POINTS = [
        'paper_cup' => 15,
        'plastic_cup' => 12,
        'lid' => 5,
        'straw' => 3,
        'napkin' => 2,
        'liquid_waste' => 8,
    ];

    private const PROPER_SLOT = [
        'paper_cup' => 'cup_slot',
        'plastic_cup' => 'cup_slot',
        'lid' => 'lid_slot',
        'straw' => 'straw_slot',
        'napkin' => 'general_intake',
        'liquid_waste' => 'general_intake',
    ];

    public function calculate(BinSession $session): int
    {
        $events = $session->detectionEvents()->with('detectedBrand')->get();

        if ($events->isEmpty()) {
            return 0;
        }

        // Base points
        $basePoints = $events->sum(fn ($e) => self::BASE_POINTS[$e->waste_type] ?? 0);

        // Behavior multiplier
        $behaviorMultiplier = $this->calculateBehaviorMultiplier($session, $events);

        // Brand multiplier
        $brandMultiplier = $this->calculateBrandMultiplier($session, $events);

        return (int) round($basePoints * $behaviorMultiplier * $brandMultiplier);
    }

    private function calculateBehaviorMultiplier(BinSession $session, $events): float
    {
        $properCount = 0;
        $totalCount = $events->count();

        foreach ($events as $event) {
            $expectedSlot = self::PROPER_SLOT[$event->waste_type] ?? 'general_intake';
            if ($event->input_method === $expectedSlot) {
                $properCount++;
            }
        }

        if ($properCount === $totalCount && $session->cup_rinsed) {
            return 2.0;
        }
        if ($properCount === $totalCount) {
            return 1.5;
        }
        if ($properCount > 0) {
            return 1.2;
        }

        return 1.0;
    }

    private function calculateBrandMultiplier(BinSession $session, $events): float
    {
        $bin = $session->bin()->with('outlet.brand')->first();

        if (! $bin?->outlet?->brand_id) {
            return 1.0;
        }

        $binBrandId = $bin->outlet->brand_id;
        $multipliers = [];

        foreach ($events as $event) {
            if ($event->detected_brand_id === $binBrandId) {
                $multipliers[] = $bin->outlet->brand->point_multiplier ?? 1.0;
            } else {
                $multipliers[] = 1.0;
            }
        }

        return empty($multipliers) ? 1.0 : array_sum($multipliers) / count($multipliers);
    }
}
