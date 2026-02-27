<?php

use App\Enums\PickupStatus;
use App\Models\Bin;
use App\Models\PickupRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('collector can fetch pickup board buckets', function () {
    $collector = User::factory()->collector()->create();

    $availablePickup = PickupRequest::factory()->create([
        'bin_id' => Bin::factory()->create(['fill_level' => 90])->id,
    ]);

    $activePickup = PickupRequest::factory()->claimed($collector)->create([
        'bin_id' => Bin::factory()->create(['fill_level' => 88])->id,
    ]);

    $historyPickup = PickupRequest::factory()->completed($collector)->create([
        'bin_id' => Bin::factory()->create(['fill_level' => 0])->id,
    ]);

    Sanctum::actingAs($collector);

    $response = $this->getJson(route('api.collector.pickups.index'));

    $response->assertOk()
        ->assertJsonPath('message', 'Collector pickups retrieved successfully.')
        ->assertJsonStructure([
            'data' => [
                'available',
                'active',
                'history',
            ],
        ]);

    expect(collect($response->json('data.available'))->pluck('id'))->toContain($availablePickup->id)
        ->and(collect($response->json('data.active'))->pluck('id'))->toContain($activePickup->id)
        ->and(collect($response->json('data.history'))->pluck('id'))->toContain($historyPickup->id);
});

test('collector can claim a pending pickup', function () {
    $collector = User::factory()->collector()->create();
    $bin = Bin::factory()->create(['fill_level' => 92]);
    $pickup = PickupRequest::factory()->create(['bin_id' => $bin->id]);

    Sanctum::actingAs($collector);

    $response = $this->postJson(route('api.collector.pickups.claim', $pickup));

    $response->assertOk()
        ->assertJsonPath('data.id', $pickup->id)
        ->assertJsonPath('data.status', PickupStatus::Claimed->value)
        ->assertJsonPath('message', 'Pickup claimed successfully.');

    $pickup->refresh();

    expect($pickup->status)->toBe(PickupStatus::Claimed)
        ->and($pickup->claimed_by)->toBe($collector->id)
        ->and($pickup->claimed_at)->not->toBeNull();
});

test('collector cannot claim an already claimed pickup', function () {
    $collectorA = User::factory()->collector()->create();
    $collectorB = User::factory()->collector()->create();
    $pickup = PickupRequest::factory()->claimed($collectorA)->create();

    Sanctum::actingAs($collectorB);

    $response = $this->postJson(route('api.collector.pickups.claim', $pickup));

    $response->assertStatus(409)
        ->assertJsonPath('message', 'Pickup is no longer available for claim.');

    expect($pickup->fresh()->claimed_by)->toBe($collectorA->id);
});

test('collector can complete own claimed pickup and reset bin fill level', function () {
    $collector = User::factory()->collector()->create();
    $bin = Bin::factory()->create(['fill_level' => 95]);
    $pickup = PickupRequest::factory()->claimed($collector)->create(['bin_id' => $bin->id]);

    Sanctum::actingAs($collector);

    $response = $this->postJson(route('api.collector.pickups.complete', $pickup));

    $response->assertOk()
        ->assertJsonPath('data.id', $pickup->id)
        ->assertJsonPath('data.status', PickupStatus::Completed->value)
        ->assertJsonPath('message', 'Pickup completed successfully.');

    $pickup->refresh();

    expect($pickup->status)->toBe(PickupStatus::Completed)
        ->and($pickup->completed_at)->not->toBeNull()
        ->and($bin->fresh()->fill_level)->toBe(0);
});

test('collector cannot complete a pickup claimed by another collector', function () {
    $collectorA = User::factory()->collector()->create();
    $collectorB = User::factory()->collector()->create();
    $bin = Bin::factory()->create(['fill_level' => 93]);
    $pickup = PickupRequest::factory()->claimed($collectorA)->create(['bin_id' => $bin->id]);

    Sanctum::actingAs($collectorB);

    $response = $this->postJson(route('api.collector.pickups.complete', $pickup));

    $response->assertForbidden()
        ->assertJsonPath('message', 'You can only complete pickups you have claimed.');

    expect($pickup->fresh()->status)->toBe(PickupStatus::Claimed)
        ->and($bin->fresh()->fill_level)->toBe(93);
});

test('collector cannot complete a pending pickup', function () {
    $collector = User::factory()->collector()->create();
    $pickup = PickupRequest::factory()->create();

    Sanctum::actingAs($collector);

    $response = $this->postJson(route('api.collector.pickups.complete', $pickup));

    $response->assertStatus(409)
        ->assertJsonPath('message', 'Pickup cannot be completed in its current state.');
});

test('admin and public users cannot access collector pickup API', function () {
    $pickup = PickupRequest::factory()->create();

    Sanctum::actingAs(User::factory()->admin()->create());
    $this->getJson(route('api.collector.pickups.index'))->assertForbidden();

    Sanctum::actingAs(User::factory()->create());
    $this->postJson(route('api.collector.pickups.claim', $pickup))->assertForbidden();
});

test('missing pickup returns 404', function () {
    $collector = User::factory()->collector()->create();

    Sanctum::actingAs($collector);

    $this->postJson('/api/v1/collector/pickups/999999/claim')->assertNotFound();
    $this->postJson('/api/v1/collector/pickups/999999/complete')->assertNotFound();
});
