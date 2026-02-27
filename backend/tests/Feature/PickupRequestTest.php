<?php

use App\Enums\PickupStatus;
use App\Models\Bin;
use App\Models\PickupRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('collector can view the dashboard', function () {
    $collector = User::factory()->collector()->create();

    $this->actingAs($collector)
        ->get(route('collector.dashboard'))
        ->assertSuccessful()
        ->assertSee('Available Pickups');
});

test('collector can claim a pending pickup request', function () {
    $collector = User::factory()->collector()->create();
    $bin = Bin::factory()->create(['fill_level' => 90]);
    $pickup = PickupRequest::factory()->create(['bin_id' => $bin->id]);

    $response = $this->actingAs($collector)
        ->post(route('collector.pickups.claim', $pickup));

    $response->assertRedirect(route('collector.dashboard'));
    $response->assertSessionHas('success');

    $pickup->refresh();
    expect($pickup->status)->toBe(PickupStatus::Claimed)
        ->and($pickup->claimed_by)->toBe($collector->id)
        ->and($pickup->claimed_at)->not->toBeNull();
});

test('collector cannot claim an already claimed pickup', function () {
    $collector1 = User::factory()->collector()->create();
    $collector2 = User::factory()->collector()->create();
    $pickup = PickupRequest::factory()->claimed($collector1)->create();

    $response = $this->actingAs($collector2)
        ->post(route('collector.pickups.claim', $pickup));

    $response->assertRedirect(route('collector.dashboard'));
    $response->assertSessionHas('error');

    expect($pickup->fresh()->claimed_by)->toBe($collector1->id);
});

test('collector can complete a claimed pickup and bin fill resets', function () {
    $collector = User::factory()->collector()->create();
    $bin = Bin::factory()->create(['fill_level' => 90]);
    $pickup = PickupRequest::factory()->claimed($collector)->create(['bin_id' => $bin->id]);

    $response = $this->actingAs($collector)
        ->post(route('collector.pickups.complete', $pickup));

    $response->assertRedirect(route('collector.dashboard'));
    $response->assertSessionHas('success');

    $pickup->refresh();
    expect($pickup->status)->toBe(PickupStatus::Completed)
        ->and($pickup->completed_at)->not->toBeNull()
        ->and($bin->fresh()->fill_level)->toBe(0);
});

test('collector cannot complete a pickup claimed by someone else', function () {
    $collector1 = User::factory()->collector()->create();
    $collector2 = User::factory()->collector()->create();
    $bin = Bin::factory()->create(['fill_level' => 90]);
    $pickup = PickupRequest::factory()->claimed($collector1)->create(['bin_id' => $bin->id]);

    $response = $this->actingAs($collector2)
        ->post(route('collector.pickups.complete', $pickup));

    $response->assertRedirect(route('collector.dashboard'));
    $response->assertSessionHas('error');

    expect($bin->fresh()->fill_level)->toBe(90);
});

test('collector cannot complete a pending pickup', function () {
    $collector = User::factory()->collector()->create();
    $pickup = PickupRequest::factory()->create();

    $response = $this->actingAs($collector)
        ->post(route('collector.pickups.complete', $pickup));

    $response->assertRedirect(route('collector.dashboard'));
    $response->assertSessionHas('error');
});

test('admin cannot access collector routes', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('collector.dashboard'))
        ->assertForbidden();
});

test('guest cannot access collector routes', function () {
    $this->get(route('collector.dashboard'))
        ->assertRedirect(route('login'));
});

test('collector dashboard shows stats bar', function () {
    $collector = User::factory()->collector()->create();
    $bin = Bin::factory()->create(['fill_level' => 90]);

    // Create 2 completed pickups for this collector
    PickupRequest::factory()->completed($collector)->count(2)->create(['bin_id' => $bin->id]);

    // Create 1 claimed pickup (active)
    PickupRequest::factory()->claimed($collector)->create(['bin_id' => $bin->id]);

    $this->actingAs($collector)
        ->get(route('collector.dashboard'))
        ->assertSuccessful()
        ->assertSee('Total Completed')
        ->assertSee('This Week')
        ->assertSee('Avg Time')
        ->assertSee('Active Now');
});

test('collector stats show zero for new collector', function () {
    $collector = User::factory()->collector()->create();

    $response = $this->actingAs($collector)
        ->get(route('collector.dashboard'));

    $response->assertSuccessful()
        ->assertSee('Total Completed');
});

test('dashboard shows available pickups and active pickups separately', function () {
    $collector = User::factory()->collector()->create();
    $bin1 = Bin::factory()->create(['fill_level' => 90, 'serial_number' => 'MBR-PENDING-001']);
    $bin2 = Bin::factory()->create(['fill_level' => 85, 'serial_number' => 'MBR-CLAIMED-001']);

    PickupRequest::factory()->create(['bin_id' => $bin1->id]);
    PickupRequest::factory()->claimed($collector)->create(['bin_id' => $bin2->id]);

    $response = $this->actingAs($collector)
        ->get(route('collector.dashboard'));

    $response->assertSuccessful()
        ->assertSee('MBR-PENDING-001')
        ->assertSee('MBR-CLAIMED-001')
        ->assertSee('Claim This Pickup')
        ->assertSee('Mark as Completed');
});
