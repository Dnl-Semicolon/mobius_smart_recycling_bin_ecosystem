<?php

use App\Models\Bin;
use App\Models\BinAssignment;
use App\Models\Brand;
use App\Models\DetectionEvent;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('HQ user sees aggregated stats across all outlets', function () {
    $user = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $user->id]);
    $outlet1 = Outlet::factory()->create(['brand_id' => $brand->id]);
    $outlet2 = Outlet::factory()->create(['brand_id' => $brand->id]);
    $bin1 = Bin::factory()->create();
    $bin2 = Bin::factory()->create();
    BinAssignment::create(['bin_id' => $bin1->id, 'outlet_id' => $outlet1->id, 'assigned_at' => now()]);
    BinAssignment::create(['bin_id' => $bin2->id, 'outlet_id' => $outlet2->id, 'assigned_at' => now()]);

    DetectionEvent::factory()->create(['bin_id' => $bin1->id, 'detected_at' => now()]);
    DetectionEvent::factory()->create(['bin_id' => $bin2->id, 'detected_at' => now()]);

    $response = $this->actingAs($user)->get(route('store.dashboard'));

    $response->assertOk();
    $response->assertViewHas('todayDetections', 2);
});

it('branch manager sees only assigned outlet stats', function () {
    $hqUser = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $hqUser->id]);
    $outlet1 = Outlet::factory()->create(['brand_id' => $brand->id]);
    $outlet2 = Outlet::factory()->create(['brand_id' => $brand->id]);
    $bin1 = Bin::factory()->create();
    $bin2 = Bin::factory()->create();
    BinAssignment::create(['bin_id' => $bin1->id, 'outlet_id' => $outlet1->id, 'assigned_at' => now()]);
    BinAssignment::create(['bin_id' => $bin2->id, 'outlet_id' => $outlet2->id, 'assigned_at' => now()]);

    $branchUser = User::factory()->storeOwner()->create();
    $outlet1->managers()->attach($branchUser->id, ['role' => 'manager']);

    DetectionEvent::factory()->create(['bin_id' => $bin1->id, 'detected_at' => now()]);
    DetectionEvent::factory()->create(['bin_id' => $bin2->id, 'detected_at' => now()]);

    $response = $this->actingAs($branchUser)->get(route('store.dashboard'));

    $response->assertOk();
    $response->assertViewHas('todayDetections', 1);
});

it('outlet filter narrows dashboard stats', function () {
    $user = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $user->id]);
    $outlet1 = Outlet::factory()->create(['brand_id' => $brand->id]);
    $outlet2 = Outlet::factory()->create(['brand_id' => $brand->id]);
    $bin1 = Bin::factory()->create();
    $bin2 = Bin::factory()->create();
    BinAssignment::create(['bin_id' => $bin1->id, 'outlet_id' => $outlet1->id, 'assigned_at' => now()]);
    BinAssignment::create(['bin_id' => $bin2->id, 'outlet_id' => $outlet2->id, 'assigned_at' => now()]);

    DetectionEvent::factory()->create(['bin_id' => $bin1->id, 'detected_at' => now()]);
    DetectionEvent::factory()->create(['bin_id' => $bin2->id, 'detected_at' => now()]);

    $response = $this->actingAs($user)->get(route('store.dashboard', ['outlet' => $outlet1->id]));

    $response->assertOk();
    $response->assertViewHas('todayDetections', 1);
});

it('branch manager cannot create rewards', function () {
    $hqUser = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $hqUser->id]);
    $outlet = Outlet::factory()->create(['brand_id' => $brand->id]);

    $branchUser = User::factory()->storeOwner()->create();
    $outlet->managers()->attach($branchUser->id, ['role' => 'manager']);

    $response = $this->actingAs($branchUser)->post(route('store.rewards.store'), [
        'name' => 'Test Reward',
        'points_cost' => 100,
    ]);

    $response->assertForbidden();
});

it('branch manager can view rewards index', function () {
    $hqUser = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $hqUser->id]);
    $outlet = Outlet::factory()->create(['brand_id' => $brand->id]);

    $branchUser = User::factory()->storeOwner()->create();
    $outlet->managers()->attach($branchUser->id, ['role' => 'manager']);

    $response = $this->actingAs($branchUser)->get(route('store.rewards.index'));

    $response->assertOk();
});
