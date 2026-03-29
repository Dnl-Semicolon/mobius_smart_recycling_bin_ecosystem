<?php

use App\Enums\RouteStatus;
use App\Models\Bin;
use App\Models\BinAssignment;
use App\Models\CollectionRoute;
use App\Models\Outlet;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->collector = User::factory()->collector()->create();
    $this->zone = Zone::factory()->create();
    $this->zone->collectors()->attach($this->collector->id, ['is_primary' => true]);
});

it('lists routes for authenticated collector', function () {
    Sanctum::actingAs($this->collector);

    CollectionRoute::factory()->count(2)->create([
        'collector_id' => $this->collector->id,
        'zone_id' => $this->zone->id,
    ]);

    // Route for another collector — should not appear
    CollectionRoute::factory()->create([
        'zone_id' => $this->zone->id,
    ]);

    $response = $this->getJson('/api/v1/collector/routes');

    $response->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

it('shows a specific route with stops', function () {
    Sanctum::actingAs($this->collector);

    $route = CollectionRoute::factory()->create([
        'collector_id' => $this->collector->id,
        'zone_id' => $this->zone->id,
    ]);

    $response = $this->getJson("/api/v1/collector/routes/{$route->id}");

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $route->id)
        ->assertJsonPath('data.status', 'pending');
});

it('cannot view another collectors route', function () {
    Sanctum::actingAs($this->collector);

    $otherRoute = CollectionRoute::factory()->create([
        'zone_id' => $this->zone->id,
    ]);

    $this->getJson("/api/v1/collector/routes/{$otherRoute->id}")
        ->assertForbidden();
});

it('can accept a pending route', function () {
    Sanctum::actingAs($this->collector);

    $route = CollectionRoute::factory()->pending()->create([
        'collector_id' => $this->collector->id,
        'zone_id' => $this->zone->id,
    ]);

    $response = $this->postJson("/api/v1/collector/routes/{$route->id}/accept");

    $response->assertSuccessful()
        ->assertJsonPath('data.status', 'accepted');

    expect($route->fresh()->status)->toBe(RouteStatus::Accepted);
});

it('cannot accept an already accepted route', function () {
    Sanctum::actingAs($this->collector);

    $route = CollectionRoute::factory()->accepted()->create([
        'collector_id' => $this->collector->id,
        'zone_id' => $this->zone->id,
    ]);

    $this->postJson("/api/v1/collector/routes/{$route->id}/accept")
        ->assertStatus(409);
});

it('can start an accepted route', function () {
    Sanctum::actingAs($this->collector);

    $route = CollectionRoute::factory()->accepted()->create([
        'collector_id' => $this->collector->id,
        'zone_id' => $this->zone->id,
    ]);

    $response = $this->postJson("/api/v1/collector/routes/{$route->id}/start");

    $response->assertSuccessful()
        ->assertJsonPath('data.status', 'active');

    $route->refresh();
    expect($route->status)->toBe(RouteStatus::Active)
        ->and($route->started_at)->not->toBeNull();
});

it('cannot start a pending route', function () {
    Sanctum::actingAs($this->collector);

    $route = CollectionRoute::factory()->pending()->create([
        'collector_id' => $this->collector->id,
        'zone_id' => $this->zone->id,
    ]);

    $this->postJson("/api/v1/collector/routes/{$route->id}/start")
        ->assertStatus(409);
});

it('can complete a stop when within proximity threshold', function () {
    Sanctum::actingAs($this->collector);

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

    $route = CollectionRoute::factory()->active()->withStops([
        [
            'order' => 1,
            'bin_id' => $bin->id,
            'pickup_request_id' => null,
            'outlet_name' => $outlet->name,
            'address' => $outlet->address,
            'latitude' => 5.4370,
            'longitude' => 100.3100,
            'fill_level' => 92,
            'service_time_sec' => 300,
            'eta_minutes' => 12,
            'arrival_distance_km' => 2.4,
            'status' => 'pending',
            'completed_at' => null,
        ],
    ])->create([
        'collector_id' => $this->collector->id,
        'zone_id' => $this->zone->id,
    ]);

    // Collector is 50m away (within 200m threshold)
    $response = $this->postJson("/api/v1/collector/routes/{$route->id}/stops/1/complete", [
        'latitude' => 5.4374,
        'longitude' => 100.3103,
    ]);

    $response->assertSuccessful();

    $route->refresh();
    $stops = $route->stops;
    expect($stops[0]['status'])->toBe('completed')
        ->and($stops[0]['completed_at'])->not->toBeNull()
        ->and($stops[0]['completed_latitude'])->toBe(5.4374)
        ->and($stops[0]['completed_longitude'])->toBe(100.3103)
        ->and($stops[0]['distance_from_stop_m'])->toBeFloat();

    // Bin fill level should be reset
    expect($bin->fresh()->fill_level)->toBe(0);
});

it('rejects stop completion when too far away', function () {
    Sanctum::actingAs($this->collector);

    $route = CollectionRoute::factory()->active()->withStops([
        [
            'order' => 1,
            'bin_id' => null,
            'pickup_request_id' => null,
            'outlet_name' => 'Starbucks Gurney',
            'address' => 'Gurney Plaza',
            'latitude' => 5.4370,
            'longitude' => 100.3100,
            'fill_level' => 92,
            'service_time_sec' => 300,
            'eta_minutes' => 12,
            'arrival_distance_km' => 2.4,
            'status' => 'pending',
            'completed_at' => null,
        ],
    ])->create([
        'collector_id' => $this->collector->id,
        'zone_id' => $this->zone->id,
    ]);

    // Collector is at Tanjung Bungah — ~5km from Gurney
    $response = $this->postJson("/api/v1/collector/routes/{$route->id}/stops/1/complete", [
        'latitude' => 5.4640,
        'longitude' => 100.2840,
    ]);

    $response->assertUnprocessable()
        ->assertJsonStructure(['message', 'distance_meters']);

    // Stop should NOT be completed
    expect($route->fresh()->stops[0]['status'])->toBe('pending');
});

it('requires latitude and longitude to complete a stop', function () {
    Sanctum::actingAs($this->collector);

    $route = CollectionRoute::factory()->active()->create([
        'collector_id' => $this->collector->id,
        'zone_id' => $this->zone->id,
    ]);

    $this->postJson("/api/v1/collector/routes/{$route->id}/stops/1/complete", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['latitude', 'longitude']);
});

it('auto-completes route when all stops are done', function () {
    Sanctum::actingAs($this->collector);

    $bin = Bin::factory()->active()->full()->create();

    $route = CollectionRoute::factory()->active()->withStops([
        [
            'order' => 1,
            'bin_id' => $bin->id,
            'pickup_request_id' => null,
            'outlet_name' => 'Test',
            'address' => 'Test',
            'latitude' => 5.4370,
            'longitude' => 100.3100,
            'fill_level' => 92,
            'service_time_sec' => 300,
            'eta_minutes' => 12,
            'arrival_distance_km' => 2.4,
            'status' => 'pending',
            'completed_at' => null,
        ],
    ])->create([
        'collector_id' => $this->collector->id,
        'zone_id' => $this->zone->id,
    ]);

    // Complete from nearby (within 200m)
    $this->postJson("/api/v1/collector/routes/{$route->id}/stops/1/complete", [
        'latitude' => 5.4370,
        'longitude' => 100.3100,
    ])->assertSuccessful();

    expect($route->fresh()->status)->toBe(RouteStatus::Completed);
});

it('can skip a stop with a reason', function () {
    Sanctum::actingAs($this->collector);

    $route = CollectionRoute::factory()->active()->withStops([
        [
            'order' => 1,
            'bin_id' => null,
            'pickup_request_id' => null,
            'outlet_name' => 'Test',
            'address' => 'Test',
            'latitude' => 5.4370,
            'longitude' => 100.3100,
            'fill_level' => 85,
            'service_time_sec' => 300,
            'eta_minutes' => 12,
            'arrival_distance_km' => 2.4,
            'status' => 'pending',
            'completed_at' => null,
        ],
        [
            'order' => 2,
            'bin_id' => null,
            'pickup_request_id' => null,
            'outlet_name' => 'Test 2',
            'address' => 'Test 2',
            'latitude' => 5.4380,
            'longitude' => 100.3110,
            'fill_level' => 90,
            'service_time_sec' => 300,
            'eta_minutes' => 20,
            'arrival_distance_km' => 4.0,
            'status' => 'pending',
            'completed_at' => null,
        ],
    ])->create([
        'collector_id' => $this->collector->id,
        'zone_id' => $this->zone->id,
    ]);

    $response = $this->postJson("/api/v1/collector/routes/{$route->id}/stops/1/skip", [
        'reason' => 'Outlet closed for renovation',
    ]);

    $response->assertSuccessful();

    $route->refresh();
    expect($route->stops[0]['status'])->toBe('skipped')
        ->and($route->stops[0]['skip_reason'])->toBe('Outlet closed for renovation');
});

it('requires a reason to skip a stop', function () {
    Sanctum::actingAs($this->collector);

    $route = CollectionRoute::factory()->active()->create([
        'collector_id' => $this->collector->id,
        'zone_id' => $this->zone->id,
    ]);

    $this->postJson("/api/v1/collector/routes/{$route->id}/stops/1/skip", [])
        ->assertUnprocessable();
});

it('can reject a pending route', function () {
    Sanctum::actingAs($this->collector);

    $route = CollectionRoute::factory()->pending()->create([
        'collector_id' => $this->collector->id,
        'zone_id' => $this->zone->id,
    ]);

    $response = $this->postJson("/api/v1/collector/routes/{$route->id}/reject");

    $response->assertSuccessful();
    expect($route->fresh()->status)->toBe(RouteStatus::Cancelled);
});

it('can complete an active route', function () {
    Sanctum::actingAs($this->collector);

    $route = CollectionRoute::factory()->active()->create([
        'collector_id' => $this->collector->id,
        'zone_id' => $this->zone->id,
    ]);

    $response = $this->postJson("/api/v1/collector/routes/{$route->id}/complete");

    $response->assertSuccessful();

    $route->refresh();
    expect($route->status)->toBe(RouteStatus::Completed)
        ->and($route->completed_at)->not->toBeNull();
});

it('filters routes by status', function () {
    Sanctum::actingAs($this->collector);

    CollectionRoute::factory()->pending()->create([
        'collector_id' => $this->collector->id,
        'zone_id' => $this->zone->id,
    ]);
    CollectionRoute::factory()->completed()->create([
        'collector_id' => $this->collector->id,
        'zone_id' => $this->zone->id,
    ]);

    $response = $this->getJson('/api/v1/collector/routes?status=pending');

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

it('denies non-collector users access to route endpoints', function () {
    $publicUser = User::factory()->publicUser()->create();
    Sanctum::actingAs($publicUser);

    $this->getJson('/api/v1/collector/routes')->assertForbidden();
});
