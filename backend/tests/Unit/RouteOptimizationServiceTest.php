<?php

use App\Models\Zone;
use App\Services\RouteOptimizationService;

it('calculates priority correctly for all fill level ranges', function (int $fillLevel, int $expectedPriority) {
    $service = new RouteOptimizationService;

    expect($service->calculatePriority($fillLevel))->toBe($expectedPriority);
})->with([
    '100% → 10' => [100, 10],
    '95% → 10' => [95, 10],
    '93% → 7' => [93, 7],
    '90% → 7' => [90, 7],
    '88% → 5' => [88, 5],
    '85% → 5' => [85, 5],
    '82% → 3' => [82, 3],
    '80% → 3' => [80, 3],
    '70% → 1' => [70, 1],
    '50% → 1' => [50, 1],
    '0% → 1' => [0, 1],
]);

it('checks point-in-polygon correctly', function () {
    // Square polygon around Gurney area
    $polygon = [
        [5.43, 100.30],
        [5.44, 100.30],
        [5.44, 100.32],
        [5.43, 100.32],
    ];

    // Point inside
    expect(Zone::pointInPolygon(5.435, 100.310, $polygon))->toBeTrue();

    // Point outside (south)
    expect(Zone::pointInPolygon(5.420, 100.310, $polygon))->toBeFalse();

    // Point outside (east)
    expect(Zone::pointInPolygon(5.435, 100.330, $polygon))->toBeFalse();
});

it('handles empty polygon boundary', function () {
    $zone = new Zone;
    $zone->boundary = [];

    expect($zone->containsPoint(5.43, 100.31))->toBeFalse();
});
