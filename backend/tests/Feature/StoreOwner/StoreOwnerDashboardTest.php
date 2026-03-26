<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

use App\Models\Brand;
use App\Models\Outlet;
use App\Models\Reward;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Store owner dashboard
|--------------------------------------------------------------------------
*/

test('store owner can view dashboard', function () {
    $brand = Brand::factory()->sponsored()->create();
    $outlet = Outlet::factory()->create(['brand_id' => $brand->id]);
    $user = User::factory()->create(['roles' => ['store_owner']]);
    $outlet->managers()->attach($user->id, ['role' => 'manager']);

    $this->actingAs($user)
        ->get('/store')
        ->assertOk()
        ->assertSeeText($outlet->name)
        ->assertSeeText($brand->name);
});

test('store owner without outlet gets 403', function () {
    $user = User::factory()->create(['roles' => ['store_owner']]);

    $this->actingAs($user)
        ->get('/store')
        ->assertForbidden();
});

test('non-store-owner cannot access store dashboard', function () {
    $user = User::factory()->publicUser()->create();

    $this->actingAs($user)
        ->get('/store')
        ->assertForbidden();
});

/*
|--------------------------------------------------------------------------
| Store owner rewards CRUD
|--------------------------------------------------------------------------
*/

test('store owner can view rewards index', function () {
    $brand = Brand::factory()->sponsored()->create();
    $outlet = Outlet::factory()->create(['brand_id' => $brand->id]);
    $user = User::factory()->create(['roles' => ['store_owner']]);
    $outlet->managers()->attach($user->id, ['role' => 'manager']);

    Reward::factory()->forBrand($brand)->create(['name' => 'Test Reward']);

    $this->actingAs($user)
        ->get('/store/rewards')
        ->assertOk()
        ->assertSeeText('Test Reward');
});

test('store owner HQ can create a reward', function () {
    $user = User::factory()->create(['roles' => ['store_owner']]);
    $brand = Brand::factory()->sponsored()->create(['user_id' => $user->id]);
    Outlet::factory()->create(['brand_id' => $brand->id]);

    $this->actingAs($user)
        ->post('/store/rewards', [
            'name' => 'Free Drink',
            'description' => 'A free drink on us',
            'points_cost' => 150,
            'stock' => 20,
            'active' => 1,
        ])
        ->assertRedirect(route('store.rewards.index'));

    $this->assertDatabaseHas('rewards', [
        'brand_id' => $brand->id,
        'name' => 'Free Drink',
        'points_cost' => 150,
        'stock' => 20,
    ]);
});

test('store owner HQ can update their reward', function () {
    $user = User::factory()->create(['roles' => ['store_owner']]);
    $brand = Brand::factory()->sponsored()->create(['user_id' => $user->id]);
    Outlet::factory()->create(['brand_id' => $brand->id]);

    $reward = Reward::factory()->forBrand($brand)->create(['name' => 'Old Name']);

    $this->actingAs($user)
        ->put("/store/rewards/{$reward->id}", [
            'name' => 'New Name',
            'description' => 'Updated',
            'points_cost' => 200,
        ])
        ->assertRedirect(route('store.rewards.index'));

    expect($reward->fresh()->name)->toBe('New Name');
});

test('store owner cannot edit another brands reward', function () {
    $user = User::factory()->create(['roles' => ['store_owner']]);
    $brand = Brand::factory()->sponsored()->create(['user_id' => $user->id]);
    $otherBrand = Brand::factory()->sponsored()->create();
    Outlet::factory()->create(['brand_id' => $brand->id]);

    $reward = Reward::factory()->forBrand($otherBrand)->create();

    $this->actingAs($user)
        ->put("/store/rewards/{$reward->id}", [
            'name' => 'Hacked',
            'points_cost' => 1,
        ])
        ->assertForbidden();
});

test('store owner HQ can delete their reward', function () {
    $user = User::factory()->create(['roles' => ['store_owner']]);
    $brand = Brand::factory()->sponsored()->create(['user_id' => $user->id]);
    Outlet::factory()->create(['brand_id' => $brand->id]);

    $reward = Reward::factory()->forBrand($brand)->create();

    $this->actingAs($user)
        ->delete("/store/rewards/{$reward->id}")
        ->assertRedirect(route('store.rewards.index'));

    $this->assertDatabaseMissing('rewards', ['id' => $reward->id]);
});

/*
|--------------------------------------------------------------------------
| Analytics
|--------------------------------------------------------------------------
*/

test('store owner can view analytics page', function () {
    $brand = Brand::factory()->sponsored()->create();
    $outlet = Outlet::factory()->create(['brand_id' => $brand->id]);
    $user = User::factory()->create(['roles' => ['store_owner']]);
    $outlet->managers()->attach($user->id, ['role' => 'manager']);

    $this->actingAs($user)
        ->get('/store/analytics')
        ->assertOk();
});
