<?php

use App\Models\Bin;
use App\Models\PickupRequest;
use App\Models\User;
use App\Notifications\PickupCompleted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('admins are notified when collector completes a pickup', function () {
    Notification::fake();

    $admin1 = User::factory()->admin()->create();
    $admin2 = User::factory()->admin()->create();
    $collector = User::factory()->collector()->create();

    $bin = Bin::factory()->create(['fill_level' => 85]);
    $pickup = PickupRequest::factory()->claimed($collector)->create(['bin_id' => $bin->id]);

    $this->actingAs($collector)
        ->post(route('collector.pickups.complete', $pickup))
        ->assertRedirect(route('collector.dashboard'));

    Notification::assertSentTo([$admin1, $admin2], PickupCompleted::class);
});

test('collectors are not notified on pickup completion', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $collector = User::factory()->collector()->create();
    $otherCollector = User::factory()->collector()->create();

    $bin = Bin::factory()->create(['fill_level' => 85]);
    $pickup = PickupRequest::factory()->claimed($collector)->create(['bin_id' => $bin->id]);

    $this->actingAs($collector)
        ->post(route('collector.pickups.complete', $pickup));

    Notification::assertSentTo($admin, PickupCompleted::class);
    Notification::assertNotSentTo([$collector, $otherCollector], PickupCompleted::class);
});

test('no notification sent when completion fails', function () {
    Notification::fake();

    User::factory()->admin()->create();
    $collector = User::factory()->collector()->create();
    $otherCollector = User::factory()->collector()->create();

    $bin = Bin::factory()->create(['fill_level' => 85]);
    $pickup = PickupRequest::factory()->claimed($collector)->create(['bin_id' => $bin->id]);

    // Other collector tries to complete — should be forbidden
    $this->actingAs($otherCollector)
        ->post(route('collector.pickups.complete', $pickup))
        ->assertRedirect(route('collector.dashboard'));

    Notification::assertNothingSent();
});

test('pickup completed notification contains correct data', function () {
    Notification::fake();

    $admin = User::factory()->admin()->create();
    $collector = User::factory()->collector()->create();

    $bin = Bin::factory()->create(['fill_level' => 90]);
    $pickup = PickupRequest::factory()->claimed($collector)->create(['bin_id' => $bin->id]);

    $this->actingAs($collector)
        ->post(route('collector.pickups.complete', $pickup));

    Notification::assertSentTo($admin, PickupCompleted::class, function ($notification) use ($pickup) {
        return $notification->pickupRequest->id === $pickup->id;
    });
});
