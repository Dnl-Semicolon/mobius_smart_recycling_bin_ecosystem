<?php

use App\Enums\WasteType;
use App\Models\Bin;
use App\Models\DetectionEvent;
use App\Models\Outlet;
use App\Models\PickupRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('public user can view the community dashboard', function () {
    $user = User::factory()->publicUser()->create();

    $this->actingAs($user)
        ->get(route('public.dashboard'))
        ->assertSuccessful()
        ->assertSee('Community Impact');
});

test('public dashboard shows impact stats with correct counts', function () {
    $user = User::factory()->publicUser()->create();
    $bin = Bin::factory()->create();

    DetectionEvent::factory()->count(3)->create(['bin_id' => $bin->id]);
    PickupRequest::factory()->completed()->count(2)->create();
    Outlet::factory()->count(2)->create();

    $this->actingAs($user)
        ->get(route('public.dashboard'))
        ->assertSuccessful()
        ->assertSee('Items Detected')
        ->assertSee('Pickups Done')
        ->assertSee('Active Bins')
        ->assertSee('Outlets');
});

test('public dashboard shows recent activity', function () {
    $user = User::factory()->publicUser()->create();
    $bin = Bin::factory()->create();

    DetectionEvent::factory()->create([
        'bin_id' => $bin->id,
        'waste_type' => WasteType::PaperCup,
    ]);

    $this->actingAs($user)
        ->get(route('public.dashboard'))
        ->assertSuccessful()
        ->assertSee('paper cup');
});

test('admin cannot access public dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('public.dashboard'))
        ->assertForbidden();
});

test('collector cannot access public dashboard', function () {
    $collector = User::factory()->collector()->create();

    $this->actingAs($collector)
        ->get(route('public.dashboard'))
        ->assertForbidden();
});

test('public dashboard renders with empty data', function () {
    $user = User::factory()->publicUser()->create();

    $this->actingAs($user)
        ->get(route('public.dashboard'))
        ->assertSuccessful()
        ->assertSee('No detections recorded yet')
        ->assertSee('No data yet');
});
