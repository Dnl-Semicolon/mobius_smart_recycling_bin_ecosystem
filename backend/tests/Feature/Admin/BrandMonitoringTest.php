<?php

use App\Models\Bin;
use App\Models\BinAssignment;
use App\Models\Brand;
use App\Models\DetectionEvent;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('admin can view brands list', function () {
    $admin = User::factory()->admin()->create();
    $brand = Brand::factory()->approved()->create();
    Outlet::factory()->count(2)->create(['brand_id' => $brand->id]);

    $response = $this->actingAs($admin)->get(route('admin.brands.index'));

    $response->assertOk()
        ->assertSee($brand->name);
});

it('admin can view brand detail with stats', function () {
    $admin = User::factory()->admin()->create();
    $hqUser = User::factory()->storeOwner()->create();
    $brand = Brand::factory()->approved()->create(['user_id' => $hqUser->id]);
    $outlet = Outlet::factory()->create(['brand_id' => $brand->id]);
    $bin = Bin::factory()->create();
    BinAssignment::create(['bin_id' => $bin->id, 'outlet_id' => $outlet->id, 'assigned_at' => now()]);
    DetectionEvent::factory()->create(['bin_id' => $bin->id, 'detected_at' => now()]);

    $response = $this->actingAs($admin)->get(route('admin.brands.show', $brand));

    $response->assertOk()
        ->assertSee($brand->name)
        ->assertSee($outlet->name)
        ->assertSee($hqUser->name);
});

it('admin can see brand staff', function () {
    $admin = User::factory()->admin()->create();
    $brand = Brand::factory()->approved()->create();
    $outlet = Outlet::factory()->create(['brand_id' => $brand->id]);
    $manager = User::factory()->storeOwner()->create();
    $outlet->managers()->attach($manager->id, ['role' => 'manager']);

    $response = $this->actingAs($admin)->get(route('admin.brands.show', $brand));

    $response->assertOk()
        ->assertSee($manager->name);
});

it('non-admin cannot access brand monitoring', function () {
    $user = User::factory()->storeOwner()->create();

    $this->actingAs($user)->get(route('admin.brands.index'))->assertForbidden();
});
