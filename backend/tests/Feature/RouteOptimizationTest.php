<?php

use App\Models\Bin;
use App\Models\BinAssignment;
use App\Models\Outlet;
use App\Models\User;
use App\Models\Zone;
use App\Services\RouteOptimizationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new RouteOptimizationService;

    $this->zone = Zone::factory()->create([
        'boundary' => [
            [5.42, 100.29],
            [5.45, 100.29],
            [5.45, 100.33],
            [5.42, 100.33],
        ],
        'depot_latitude' => 5.3784,
        'depot_longitude' => 100.3354,
        'min_bins_for_dispatch' => 2,
    ]);

    $this->collector = User::factory()->collector()->create();
    $this->zone->collectors()->attach($this->collector->id, ['is_primary' => true]);
});

it('builds a valid VROOM request from zone bins', function () {
    $outlet = Outlet::factory()->active()->create([
        'latitude' => 5.4370,
        'longitude' => 100.3100,
    ]);

    $bin = Bin::factory()->active()->full()->create();
    BinAssignment::create([
        'bin_id' => $bin->id,
        'outlet_id' => $outlet->id,
        'assigned_at' => now(),
    ]);

    $bins = collect([$bin->load('currentAssignment.outlet')]);
    $request = $this->service->buildVroomRequest($this->zone, $bins, $this->collector);

    expect($request)->toHaveKeys(['vehicles', 'jobs'])
        ->and($request['vehicles'])->toHaveCount(1)
        ->and($request['vehicles'][0]['id'])->toBe($this->collector->id)
        ->and($request['vehicles'][0]['start'])->toBe([(float) $this->zone->depot_longitude, (float) $this->zone->depot_latitude])
        ->and($request['jobs'])->toHaveCount(1)
        ->and($request['jobs'][0]['id'])->toBe($bin->id)
        ->and($request['jobs'][0]['location'])->toBe([(float) $outlet->longitude, (float) $outlet->latitude]);
});

it('calculates priority from fill level', function () {
    expect($this->service->calculatePriority(95))->toBe(10)
        ->and($this->service->calculatePriority(92))->toBe(7)
        ->and($this->service->calculatePriority(87))->toBe(5)
        ->and($this->service->calculatePriority(82))->toBe(3)
        ->and($this->service->calculatePriority(50))->toBe(1);
});

it('returns null when not enough bins for dispatch', function () {
    // Zone requires min 2, but only 1 bin available
    $outlet = Outlet::factory()->active()->create([
        'latitude' => 5.4370,
        'longitude' => 100.3100,
    ]);

    $bin = Bin::factory()->active()->withFillLevel(90)->create();
    BinAssignment::create([
        'bin_id' => $bin->id,
        'outlet_id' => $outlet->id,
        'assigned_at' => now(),
    ]);

    $result = $this->service->generateRoute($this->zone);

    expect($result)->toBeNull();
});

it('generates a fallback route when VROOM is unavailable', function () {
    Http::fake([
        '*' => Http::response(null, 500),
    ]);

    // Create 2 bins in the zone boundary
    $outlet1 = Outlet::factory()->active()->create([
        'latitude' => 5.4370,
        'longitude' => 100.3100,
    ]);
    $outlet2 = Outlet::factory()->active()->create([
        'latitude' => 5.4380,
        'longitude' => 100.3110,
    ]);

    $bin1 = Bin::factory()->active()->withFillLevel(90)->create();
    $bin2 = Bin::factory()->active()->withFillLevel(85)->create();

    BinAssignment::create(['bin_id' => $bin1->id, 'outlet_id' => $outlet1->id, 'assigned_at' => now()]);
    BinAssignment::create(['bin_id' => $bin2->id, 'outlet_id' => $outlet2->id, 'assigned_at' => now()]);

    $route = $this->service->generateRoute($this->zone);

    expect($route)->not->toBeNull()
        ->and($route->stops)->toHaveCount(2)
        ->and($route->vroom_response['fallback'])->toBeTrue()
        ->and($route->route_geometry)->toBeNull()
        ->and($route->status->value)->toBe('pending');
});

it('creates pickup requests for each stop when generating a route', function () {
    Http::fake([
        '*' => Http::response(null, 500),
    ]);

    $outlet1 = Outlet::factory()->active()->create(['latitude' => 5.4370, 'longitude' => 100.3100]);
    $outlet2 = Outlet::factory()->active()->create(['latitude' => 5.4380, 'longitude' => 100.3110]);

    $bin1 = Bin::factory()->active()->withFillLevel(90)->create();
    $bin2 = Bin::factory()->active()->withFillLevel(85)->create();

    BinAssignment::create(['bin_id' => $bin1->id, 'outlet_id' => $outlet1->id, 'assigned_at' => now()]);
    BinAssignment::create(['bin_id' => $bin2->id, 'outlet_id' => $outlet2->id, 'assigned_at' => now()]);

    $route = $this->service->generateRoute($this->zone);

    expect($route->stops)->toHaveCount(2);

    foreach ($route->stops as $stop) {
        expect($stop['pickup_request_id'])->not->toBeNull();
    }
});

it('generates route from a mocked VROOM response', function () {
    $outlet1 = Outlet::factory()->active()->create(['latitude' => 5.4370, 'longitude' => 100.3100]);
    $outlet2 = Outlet::factory()->active()->create(['latitude' => 5.4380, 'longitude' => 100.3110]);

    $bin1 = Bin::factory()->active()->withFillLevel(92)->create();
    $bin2 = Bin::factory()->active()->withFillLevel(85)->create();

    BinAssignment::create(['bin_id' => $bin1->id, 'outlet_id' => $outlet1->id, 'assigned_at' => now()]);
    BinAssignment::create(['bin_id' => $bin2->id, 'outlet_id' => $outlet2->id, 'assigned_at' => now()]);

    Http::fake([
        '*' => Http::response([
            'code' => 0,
            'routes' => [
                [
                    'vehicle' => $this->collector->id,
                    'geometry' => 'e~lgFczpkVsHzB',
                    'steps' => [
                        ['type' => 'start', 'location' => [100.3354, 5.3784], 'arrival' => 0, 'duration' => 0, 'distance' => 0],
                        ['type' => 'job', 'id' => $bin1->id, 'location' => [100.3100, 5.4370], 'arrival' => 600, 'duration' => 600, 'distance' => 4200],
                        ['type' => 'job', 'id' => $bin2->id, 'location' => [100.3110, 5.4380], 'arrival' => 1200, 'duration' => 1200, 'distance' => 4500],
                        ['type' => 'end', 'location' => [100.3354, 5.3784], 'arrival' => 1800, 'duration' => 1800, 'distance' => 9200],
                    ],
                    'cost' => 5400,
                    'duration' => 1800,
                    'distance' => 9200,
                ],
            ],
            'unassigned' => [],
        ]),
    ]);

    $route = $this->service->generateRoute($this->zone);

    expect($route)->not->toBeNull()
        ->and($route->stops)->toHaveCount(2)
        ->and($route->stops[0]['bin_id'])->toBe($bin1->id)
        ->and($route->stops[1]['bin_id'])->toBe($bin2->id)
        ->and($route->total_distance_km)->toBe('9.20')
        ->and($route->total_duration_min)->toBe(30)
        ->and($route->route_geometry)->toBe('e~lgFczpkVsHzB')
        ->and($route->status->value)->toBe('pending');
});

it('does not assign collector who already has an active route', function () {
    Http::fake(['*' => Http::response(null, 500)]);

    // Give collector an active route
    \App\Models\CollectionRoute::factory()->active()->create([
        'collector_id' => $this->collector->id,
        'zone_id' => $this->zone->id,
    ]);

    // Create bins
    $outlet1 = Outlet::factory()->active()->create(['latitude' => 5.4370, 'longitude' => 100.3100]);
    $outlet2 = Outlet::factory()->active()->create(['latitude' => 5.4380, 'longitude' => 100.3110]);
    $bin1 = Bin::factory()->active()->withFillLevel(90)->create();
    $bin2 = Bin::factory()->active()->withFillLevel(85)->create();
    BinAssignment::create(['bin_id' => $bin1->id, 'outlet_id' => $outlet1->id, 'assigned_at' => now()]);
    BinAssignment::create(['bin_id' => $bin2->id, 'outlet_id' => $outlet2->id, 'assigned_at' => now()]);

    $result = $this->service->generateRoute($this->zone);

    // No available collector → null
    expect($result)->toBeNull();
});
