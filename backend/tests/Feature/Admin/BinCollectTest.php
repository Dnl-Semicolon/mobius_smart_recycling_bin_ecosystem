<?php

use App\Enums\PickupStatus;
use App\Models\Bin;
use App\Models\PickupRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin collect route no longer exists', function () {
    $admin = User::factory()->admin()->create();
    $bin = Bin::factory()->create(['fill_level' => 85]);

    $this->actingAs($admin)
        ->post("/admin/bins/{$bin->id}/collect")
        ->assertNotFound();
});

test('collector collect route no longer exists', function () {
    $collector = User::factory()->collector()->create();
    $bin = Bin::factory()->create(['fill_level' => 90]);

    $this->actingAs($collector)
        ->post("/collector/bins/{$bin->id}/collect")
        ->assertNotFound();
});

test('pickup request workflow resets bin fill level', function () {
    $collector = User::factory()->collector()->create();
    $bin = Bin::factory()->create(['fill_level' => 90]);
    $pickup = PickupRequest::factory()->claimed($collector)->create(['bin_id' => $bin->id]);

    $this->actingAs($collector)
        ->post(route('collector.pickups.complete', $pickup));

    expect($bin->fresh()->fill_level)->toBe(0)
        ->and($pickup->fresh()->status)->toBe(PickupStatus::Completed);
});
