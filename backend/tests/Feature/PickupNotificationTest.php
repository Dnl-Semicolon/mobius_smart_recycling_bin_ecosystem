<?php

use App\Enums\WasteType;
use App\Models\Bin;
use App\Models\DetectionEvent;
use App\Models\PickupRequest;
use App\Models\User;
use App\Notifications\BinReadyForPickup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('pickup request is created when bin crosses 80% fill', function () {
    $bin = Bin::factory()->create(['fill_level' => 78]);
    User::factory()->collector()->create();

    Notification::fake();

    // This PaperCup (+5) takes 78 → 83, crossing the 80% threshold
    DetectionEvent::factory()->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::PaperCup,
    ]);

    expect($bin->fresh()->fill_level)->toBe(83);
    expect(PickupRequest::where('bin_id', $bin->id)->count())->toBe(1);

    $pickup = PickupRequest::where('bin_id', $bin->id)->first();
    expect($pickup->status->value)->toBe('pending');
});

test('collectors are notified when pickup request is created', function () {
    Notification::fake();

    $collector1 = User::factory()->collector()->create();
    $collector2 = User::factory()->collector()->create();
    User::factory()->admin()->create(); // admin should NOT be notified

    $bin = Bin::factory()->create(['fill_level' => 76]);

    DetectionEvent::factory()->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::PaperCup, // +5 → 81
    ]);

    Notification::assertSentTo([$collector1, $collector2], BinReadyForPickup::class);
    Notification::assertCount(2);
});

test('no duplicate pickup request when bin is already above 80%', function () {
    Notification::fake();

    $bin = Bin::factory()->create(['fill_level' => 78]);
    User::factory()->collector()->create();

    // First detection crosses threshold → pickup created
    DetectionEvent::factory()->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::PaperCup, // +5 → 83
    ]);

    expect(PickupRequest::where('bin_id', $bin->id)->count())->toBe(1);

    // Second detection — already above 80%, should NOT create another
    DetectionEvent::factory()->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::PaperCup, // +5 → 88
    ]);

    expect(PickupRequest::where('bin_id', $bin->id)->count())->toBe(1);
});

test('no pickup request when bin does not cross 80%', function () {
    Notification::fake();

    $bin = Bin::factory()->create(['fill_level' => 50]);

    DetectionEvent::factory()->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::Straw, // +1 → 51
    ]);

    expect(PickupRequest::where('bin_id', $bin->id)->count())->toBe(0);
    Notification::assertNothingSent();
});

test('new pickup request can be created after previous one is completed', function () {
    Notification::fake();

    $collector = User::factory()->collector()->create();
    $bin = Bin::factory()->create(['fill_level' => 78]);

    // First crossing → pickup created
    DetectionEvent::factory()->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::PaperCup, // +5 → 83
    ]);

    $pickup = PickupRequest::where('bin_id', $bin->id)->first();
    $pickup->update([
        'status' => \App\Enums\PickupStatus::Completed,
        'claimed_by' => $collector->id,
        'claimed_at' => now()->subHour(),
        'completed_at' => now(),
    ]);
    $bin->update(['fill_level' => 0]);

    // Fill back up to cross threshold again
    $bin->update(['fill_level' => 77]);
    DetectionEvent::factory()->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::PaperCup, // +5 → 82
    ]);

    expect(PickupRequest::where('bin_id', $bin->id)->count())->toBe(2);
});
